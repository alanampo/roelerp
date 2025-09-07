<?php
/**
  *
  * Render masivo de etiquetas para Brother QL-1110NWB (103mm x 164mm).
  * Muestra TODAS las etiquetas del rango [ini..fin] (YYYY-MM-DD), UNA por hoja.
  * Firma HMAC: sig = HMAC_SHA256("{$ini}|{$fin}|{$exp}", PRINT_SECRET)
  *
  * GET:
  * ini, fin, exp, sig  (obligatorios)
  * sag_note            (opcional)
  * dl=1                (opcional: fuerza descarga para iOS)
  * dbg=1               (depuración firmas)
 */

declare(strict_types=1);
date_default_timezone_set('America/Santiago');

/* ===== Secreto HMAC ===== */
define('PRINT_SECRET', 'DFT/%U&%Y$DXCEyuytrs');

/* ===== Nota SAG por defecto ===== */
if (!defined('DEFAULT_SAG_NOTE')) define('DEFAULT_SAG_NOTE', 'Inscripción SAG N° EDD-05-27');

/* ===== Depuración firmas ===== */
if (isset($_GET['dbg'])) {
    $ini = $_GET['ini'] ?? '';
    $fin = $_GET['fin'] ?? '';
    $exp = isset($_GET['exp']) ? (int)$_GET['exp'] : 0;
    $sig = $_GET['sig'] ?? '';
    $calc = hash_hmac('sha256', $ini.'|'.$fin.'|'.$exp, PRINT_SECRET);
    header('Content-Type: text/plain; charset=utf-8');
    echo "calc: $calc\nsig : $sig\nini : $ini\nfin : $fin\nexp : ".($exp?date('Y-m-d H:i:s',$exp):'-')."\n";
    exit;
}

/* ===== Validación parámetros y firma ===== */
$ini_s = preg_replace('/[^0-9\-]/', '', (string)($_GET['ini'] ?? ''));
$fin_s = preg_replace('/[^0-9\-]/', '', (string)($_GET['fin'] ?? ''));
$exp   = isset($_GET['exp']) ? (int)$_GET['exp'] : 0;
$sig   = $_GET['sig'] ?? '';
if ($ini_s==='' || $fin_s==='' || $exp<=0 || $sig==='') { http_response_code(400); exit('Parámetros incompletos'); }
if ($exp < time()) { http_response_code(410); exit('Enlace expirado'); }
$calc = hash_hmac('sha256', $ini_s.'|'.$fin_s.'|'.$exp, PRINT_SECRET);
if (!hash_equals($calc, $sig)) { http_response_code(401); exit('Firma inválida'); }

try {
    $ini_dt = new DateTimeImmutable($ini_s.' 00:00:00');
    $fin_dt = new DateTimeImmutable($fin_s.' 23:59:59');
} catch (Throwable $e) {
    http_response_code(400); exit('Fechas inválidas');
}

/* ===== Conexión BD ===== */
$DB = null;
if (file_exists(__DIR__.'/../config.php')) {
    require_once __DIR__.'/../config.php';
}
if (!isset($DB)) {
    if (file_exists(__DIR__.'/../class_lib/class_conecta_mysql.php')) {
        require_once __DIR__.'/../class_lib/class_conecta_mysql.php';
        $mysqli = @mysqli_connect($host, $user, $password, $dbname);
        if (!$mysqli) { http_response_code(500); exit('Error de conexión BD'); }
        mysqli_set_charset($mysqli, 'utf8');
    } else {
        http_response_code(500); exit('No hay conexión a BD');
    }
}

/* ===== Cargar etiquetas del rango ===== */
$rows=[];
try {
    if (isset($DB) && $DB instanceof PDO) {
        $st = $DB->prepare("SELECT id, codigo, fecha FROM ordenes_envio WHERE fecha BETWEEN :ini AND :fin ORDER BY fecha ASC, id ASC");
        $st->execute([':ini'=>$ini_dt->format('Y-m-d H:i:s'), ':fin'=>$fin_dt->format('Y-m-d H:i:s')]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } else {
        $ini_q = mysqli_real_escape_string($mysqli, $ini_dt->format('Y-m-d H:i:s'));
        $fin_q = mysqli_real_escape_string($mysqli, $fin_dt->format('Y-m-d H:i:s'));
        $rs = mysqli_query($mysqli, "SELECT id, codigo, fecha FROM ordenes_envio WHERE fecha BETWEEN '{$ini_q}' AND '{$fin_q}' ORDER BY fecha ASC, id ASC");
        while ($rs && $row = mysqli_fetch_assoc($rs)) $rows[]=$row;
    }
} catch (Throwable $e) {
    http_response_code(500); exit('Error consultando órdenes');
}
if (!$rows) { http_response_code(404); exit('No hay etiquetas en el rango'); }

/* ===== Decodificar base64 si aplica ===== */
function decode_codigo(string $codigo): string {
    $c = trim($codigo);
    if ($c!=='' && strlen($c)%4===0 && preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $c)) {
        $dec = base64_decode($c, true);
        if ($dec !== false && $dec!=='') return $dec;
    }
    return $codigo;
}

/* ===== Nota SAG ===== */
$sag_note = trim((string)($_GET['sag_note'] ?? ''));
if ($sag_note === '') $sag_note = DEFAULT_SAG_NOTE;

/* ===== Descarga forzada para iOS ===== */
$forceDownload = isset($_GET['dl']) && $_GET['dl'] == '1';
$qs = $_GET; $qs['dl'] = 1;
$downloadUrl = htmlspecialchars($_SERVER['PHP_SELF'].'?'.http_build_query($qs), ENT_QUOTES, 'UTF-8');

header('Content-Type: text/html; charset=utf-8');
if ($forceDownload) {
  $fname = 'etiquetas-'.$ini_dt->format('Ymd').'-'.$fin_dt->format('Ymd').'.html';
  header('Content-Disposition: attachment; filename="'.$fname.'"');
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Etiquetas <?= htmlspecialchars($ini_s) ?> a <?= htmlspecialchars($fin_s) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{
    --label-w: 103mm; --label-h: 164mm;
    --pad-top: 4mm; --pad-right: 4mm; --pad-bottom: 4mm; --pad-left: 10mm;
    --font: Arial, Helvetica, sans-serif; --fs: 12px; --lh: 1.25;
  }
  @page { size: var(--label-w) var(--label-h); margin: 0; }
  html, body { margin:0; padding:0; background:#fff; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  body { font-family: var(--font); }
  .toolbar { margin:8px; display:flex; gap:8px; align-items:center; }
  .toolbar a, .toolbar button { padding:6px 10px; cursor:pointer; border:1px solid #e2e8f0; background:#f8fafc; }
  .hint { font-size:12px; color:#475569; display:none; }
  @media print { .toolbar { display:none; } }
  .sheet {
    width: var(--label-w); height: var(--label-h);
    padding: var(--pad-top) var(--pad-right) var(--pad-bottom) var(--pad-left);
    box-sizing: border-box; overflow: hidden;
    page-break-after: always; break-inside: avoid;
  }
  .sheet:last-child { page-break-after: auto; }
  .label-content { font-size: var(--fs); line-height: var(--lh); color:#000; }
  .label-content * { box-sizing:border-box; page-break-inside: avoid; break-inside: avoid; }
  .label-content h1, .label-content h2, .label-content h3 { margin:0 0 6px; line-height:1.15; }
  .label-content p { margin:2px 0; }
  .label-content table { width:100%; border-collapse:collapse; table-layout:fixed; }
  .label-content td, .label-content th { vertical-align:top; padding:2px 0; word-wrap:break-word; }
  .label-content img { max-width:100%; height:auto; }
  .label-content img[alt*="QR"], .label-content img[src*="qr"] { max-width:38mm; float:right; margin-left:6mm; }
  .label-content table tr > td:first-child { width:62%; }
  .label-content table tr > td:last-child  { width:38%; text-align:right; }
  .sag-box { margin-top:6px; padding:6px 8px; border:1px solid #000; display:inline-block; font-weight:700; font-size:var(--fs); }
</style>
<script>
(function(){
  const ua = navigator.userAgent || '';
  const isIOS = /\b(iPhone|iPad|iPod)\b/i.test(ua);
  const isTelegram = /Telegram/i.test(ua);
  const canPrint = typeof window.print === 'function';
  const btn = document.getElementById('btnPrintAll');
  const hint = document.getElementById('iosHint');

  if (!canPrint || (isIOS && isTelegram)) {
    if (btn) btn.style.display = 'none';
    if (hint) hint.style.display = 'inline';
  } else if (btn) {
    btn.addEventListener('click', function(){ window.print(); });
  }
})();
</script>
</head>
<body>
<div class="toolbar">
  <button id="btnPrintAll">🖨 Imprimir todas</button>
  <a href="<?= $downloadUrl ?>" rel="noopener">⬇️ Descargar para iOS</a>
  <span id="iosHint" class="hint">En iPhone dentro de Telegram: toque “Compartir” → “Imprimir”.</span>
</div>

<?php foreach ($rows as $row): ?>
  <div class="sheet" role="region" aria-label="Etiqueta orden #<?= (int)$row['id'] ?>">
    <div class="label-content">
      <?= decode_codigo((string)$row['codigo']) /* HTML original de la etiqueta */ ?>

      <?php if ($sag_note !== ''): ?>
        <div class="sag-box"><?= htmlspecialchars($sag_note, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

</body>
</html>