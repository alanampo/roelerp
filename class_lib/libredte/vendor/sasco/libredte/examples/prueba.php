<?php
//GENERAR DTE
// respuesta en texto plano
header('Content-type: text/plain; charset=ISO-8859-1');

// incluir archivos php de la biblioteca y configuraciones
include 'inc.php';

// primer folio a usar para envio de set de pruebas
$folios = [
    33 => 21, // factura electrónica
    56 => 5, // nota de débito electrónica
    61 => 13, // nota de crédito electrónicas
];

// caratula para el envío de los dte
$caratula = [ //ACA HAY QUE PONER LOS DATOS REALES CUANDO PASE A PRODUCCION 
    'RutEnvia' => '77436423-4',
    'RutReceptor' => '60803000-K',
    'FchResol' => '2014-08-22',
    'NroResol' => 80,
];

// datos del emisor
$Emisor = [
    'RUTEmisor' => '77436423-4',
    'RznSoc' => 'Plantinera V.V.',
    'GiroEmis' => 'Cultivo de Plantas Vivas y Viveros',
    'Acteco' => 726000, //CONSULTAR QUE ES ESTO
    'DirOrigen' => 'Ex fundó la glorieta parcela 7 lote 2',
    'CmnaOrigen' => 'Quillota',
    'Telefono' => '+56 972 912 979',
    'CorreoEmisor' => 'plantinera@roelplant.cl',
];

// datos de los DTE (cada elemento del arreglo $set_pruebas es un DTE)
$set_pruebas = [
    // CASO 414175-1
    [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 33,
                'Folio' => $folios[33],
            ],
            'Emisor' => $Emisor,
            'Receptor' => [
                'RUTRecep' => '55666777-8',
                'RznSocRecep' => 'Empresa S.A.',
                'GiroRecep' => 'Servicios jurídicos',
                'DirRecep' => 'Santiago',
                'CmnaRecep' => 'Santiago',
            ],
        ],
        'Detalle' => [
            [
                'NmbItem' => 'Cajón AFECTO',
                'QtyItem' => 123,
                'PrcItem' => 923,
            ],
            [
                'NmbItem' => 'Relleno AFECTO',
                'QtyItem' => 53,
                'PrcItem' => 1473,
            ],
        ],
        'Referencia' => [
            'TpoDocRef' => 'SET',
            'FolioRef' => $folios[33],
            'RazonRef' => 'CASO 414175-1',
        ],
    ]
];

// Objetos de Firma, Folios y EnvioDTE
$Firma = new \sasco\LibreDTE\FirmaElectronica($config['firma']);
$Folios = [];
foreach ($folios as $tipo => $cantidad)
    $Folios[$tipo] = new \sasco\LibreDTE\Sii\Folios(file_get_contents('folios.xml'));
$EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();

// generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
foreach ($set_pruebas as $documento) {
    $DTE = new \sasco\LibreDTE\Sii\Dte($documento);
    if (!$DTE->timbrar($Folios[$DTE->getTipo()]))
        break;
    if (!$DTE->firmar($Firma))
        break;
    $EnvioDTE->agregar($DTE);
    
}

// enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
$EnvioDTE->setCaratula($caratula);
$EnvioDTE->setFirma($Firma);
file_put_contents('xml/EnvioDTE.xml', $EnvioDTE->generar()); // guardar XML en sistema de archivos
$track_id = $EnvioDTE->enviar();

var_dump($track_id);

// si hubo errores mostrar
foreach (\sasco\LibreDTE\Log::readAll() as $error)
    echo $error,"\n";
