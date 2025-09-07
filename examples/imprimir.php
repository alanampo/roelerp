
<?php
/**
  * api_ia/orden_envio_print.php
  * Render de etiqueta para Brother QL-1110NWB tamaño 103mm x 164mm en UNA sola hoja.
  * Firma HMAC: sig = HMAC_SHA256("{$id}|{$exp}", PRINT_SECRET)
  *
  * GET:
  * id, exp, sig  (obligatorios)
  * sag_note      (opcional)
  * dl=1          (opcional: fuerza descarga para iOS)
  * dbg=1         (depuración firmas)
 */

declare(strict_types=1);
date_default_timezone_set('America/Santiago');

/* ===== Secreto HMAC (debe coincidir con el comando que genera la URL) ===== */
define('PRINT_SECRET', 'DFT/%U&%Y$DXCEyuytrs');

/* ===== Nota SAG por defecto ===== */
if (!defined('DEFAULT_SAG_NOTE')) {
  define('DEFAULT_SAG_NOTE', 'Inscripción SAG N° EDD-05-27');
}

/* ===== Depuración firmas ===== */
if (isset($_GET['dbg'])) {
    $id  = isset($_GET['id'])  ? (int)$_GET['id']  : 0;
    $exp = isset($_GET['exp']) ? (int)$_GET['exp'] : 0;
    $sig = $_GET['sig'] ?? '';
    $data = $id.'|'.$exp;
    $calc = hash_hmac('sha256', $data, PRINT_SECRET);
    header('Content-Type: text/plain; charset=utf-8');
    echo "calc: $calc\nsig : $sig\ndata: $data\nexp: ".($exp?date('Y-m-d H:i:s',$exp):'-')."\n";
    exit;
}

/* ===== Conexión BD ===== */
$DB = null;
if (file_exists(__DIR__.'/../config.php')) {
    require_once __DIR__.'/../config.php'; // si aquí se crea $DB (PDO), úsalo
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

/* ===== Validación parámetros y firma ===== */
$id  = isset($_GET['id'])  ? (int)$_GET['id']  : 0;
$exp = isset($_GET['exp']) ? (int)$_GET['exp'] : 0;
$sig = $_GET['sig'] ?? '';
if ($id<=0 || $exp<=0 || $sig==='') { http_response_code(400); exit('Parámetros incompletos'); }
if ($exp < time()) { http_response_code(410); exit('Enlace expirado'); }
$data = $id.'|'.$exp;
$calc = hash_hmac('sha256', $data, PRINT_SECRET);
if (!hash_equals($calc, $sig)) { http_response_code(401); exit('Firma inválida'); }

/* ===== Obtener HTML/base64 de la etiqueta ===== */
$codigo = '';
try {
    if (isset($DB) && $DB instanceof PDO) {
        $st = $DB->prepare("SELECT codigo FROM ordenes_envio WHERE id = :id LIMIT 1");
        $st->execute([':id'=>$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) $codigo = (string)$row['codigo'];
    } else {
        $rs = mysqli_query($mysqli, "SELECT codigo FROM ordenes_envio WHERE id = {$id} LIMIT 1");
        if ($rs && mysqli_num_rows($rs)>0) {
            $row = mysqli_fetch_assoc($rs);
            $codigo = (string)$row['codigo'];
        }
    }
} catch (Throwable $e) {
    http_response_code(500); exit('Error consultando la orden');
}
if ($codigo==='') { http_response_code(404); exit('Orden sin etiqueta'); }

/* ===== Decodificar si viene en Base64 ===== */
if (preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $codigo) && (strlen($codigo)%4===0)) {
    $dec = base64_decode($codigo, true);
    if ($dec !== false && $dec!=='') $codigo = $dec;
}

/* ===== Nota SAG ===== */
$sag_note = trim((string)($_GET['sag_note'] ?? ''));
if ($sag_note === '') $sag_note = DEFAULT_SAG_NOTE;

/* ===== Descarga forzada para iOS (opcional) ===== */
$forceDownload = isset($_GET['dl']) && $_GET['dl'] == '1';
$qs = $_GET; $qs['dl'] = 1;
$downloadUrl = htmlspecialchars($_SERVER['PHP_SELF'].'?'.http_build_query($qs), ENT_QUOTES, 'UTF-8');

header('Content-Type: text/html; charset=utf-8');
if ($forceDownload) {
  header('Content-Disposition: attachment; filename="etiqueta-'.$id.'.html"');
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Etiqueta #<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{
    --label-w: 103mm; --label-h: 164mm;
    --pad-top: 4mm; --pad-right: 4mm; --pad-bottom: 4mm; --pad-left: 10mm;
    --font: Arial, Helvetica, sans-serif; --fs: 12px; --lh: 1.25;
  }
  @page { size: var(--label-w) var(--label-h); margin: 0; }
  html, body { width: var(--label-w); height: var(--label-h); }
  body { margin:0; padding:0; background:#fff; -webkit-print-color-adjust:exact; print-color-adjust:exact; font-family:var(--font); }
  .sheet {
    width: var(--label-w); height: var(--label-h);
    margin: 0 auto; box-sizing: border-box;
    padding: var(--pad-top) var(--pad-right) var(--pad-bottom) var(--pad-left);
    overflow: hidden; page-break-before: avoid; page-break-after: avoid; break-inside: avoid;
  }
  .toolbar { margin: 8px; display:flex; gap:8px; align-items:center; }
  .toolbar a, .toolbar button { padding:6px 10px; cursor:pointer; border:1px solid #e2e8f0; background:#f8fafc; }
  .hint { font-size:12px; color:#475569; display:none; }
  @media print { .toolbar { display:none; } }
  .label-content { font-size: var(--fs); line-height: var(--lh); color: #000; }
  .label-content * { box-sizing:border-box; page-break-inside:avoid; break-inside:avoid; }
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
  const btn = document.getElementById('btnPrint');
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
  <button id="btnPrint">🖨 Imprimir</button>
  <a href="<?= $downloadUrl ?>" rel="noopener">⬇️ Descargar para iOS</a>
  <span id="iosHint" class="hint">En iPhone dentro de Telegram: toque “Compartir” → “Imprimir”.</span>
</div>

<div class="sheet" role="region" aria-label="Etiqueta de envío">
  <div class="label-content">
    <?= $codigo /* HTML de la etiqueta original desde BD */ ?>

    <?php if ($sag_note !== ''): ?>
      <div class="sag-box"><?= htmlspecialchars($sag_note, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>