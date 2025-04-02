<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

/**
 * @file 010-set_pruebas_basico.php
 *
 * Ejemplo que genera y envía los documentos del set de pruebas básico para
 * certificación ante el SII de los documentos:
 *
 * - Factura electrónica
 * - Nota de crédito electrónica
 * - Nota de débito electrónica
 *
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-16
 */

// respuesta en texto plano
header('Content-type: text/plain; charset=ISO-8859-1');

// incluir archivos php de la biblioteca y configuraciones
include 'inc.php';

// primer folio a usar para envio de set de pruebas
$folios = [
    33 => 1, // factura electrónica
    56 => 5, // nota de débito electrónica
    61 => 13, // nota de crédito electrónicas
];

// caratula para el envío de los dte
$caratula = [
    'RutEnvia' => '11222333-4',
    'RutReceptor' => '60803000-K',
    'FchResol' => '2014-08-22',
    'NroResol' => 80,
];

// datos del emisor
$Emisor = [
    'RUTEmisor' => '76192083-9',
    'RznSoc' => 'SASCO SpA',
    'GiroEmis' => 'Servicios integrales de informática',
    'Acteco' => 726000,
    'DirOrigen' => 'Santiago',
    'CmnaOrigen' => 'Santiago',
];

// datos de los DTE (cada elemento del arreglo $set_pruebas es un DTE)
$set_pruebas = [
    // CASO 3605960-1
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
                'QtyItem' => 134,
                'PrcItem' => 1536,
            ],
            [
                'NmbItem' => 'Relleno AFECTO',
                'QtyItem' => 57,
                'PrcItem' => 2510,
            ],
        ],
        'Referencia' => [
            'TpoDocRef' => 'SET',
            'FolioRef' => $folios[33],
            'RazonRef' => 'CASO 3605960-1',
        ],
    ],
    // CASO 414175-2
    [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 33,
                'Folio' => $folios[33]+1,
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
                'NmbItem' => 'Pañuelo AFECTO',
                'QtyItem' => 360,
                'PrcItem' => 2869,
                'DescuentoPct' => 5,
            ],
            [
                'NmbItem' => 'ITEM 2 AFECTO',
                'QtyItem' => 291,
                'PrcItem' => 1930,
                'DescuentoPct' => 9,
            ],
        ],
        'Referencia' => [
            'TpoDocRef' => 'SET',
            'FolioRef' => $folios[33]+1,
            'RazonRef' => 'CASO 3605960-2',
        ],
    ],
    // CASO 3605960-3
    [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 33,
                'Folio' => $folios[33]+2,
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
                'NmbItem' => 'Pintura B&W AFECTO',
                'QtyItem' => 29,
                'PrcItem' => 3215,
            ],
            [
                'NmbItem' => 'ITEM 2 AFECTO',
                'QtyItem' => 170,
                'PrcItem' => 3153,
            ],
            [
                'IndExe' => 1,
                'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                'QtyItem' => 1,
                'PrcItem' => 34845,
            ],
        ],
        'Referencia' => [
            'TpoDocRef' => 'SET',
            'FolioRef' => $folios[33]+2,
            'RazonRef' => 'CASO 3605960-3',
        ],
    ],
    // CASO 3605960-4
    [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 33,
                'Folio' => $folios[33]+3,
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
                'NmbItem' => 'ITEM 1 AFECTO',
                'QtyItem' => 161,
                'PrcItem' => 2686,
            ],
            [
                'NmbItem' => 'ITEM 2 AFECTO',
                'QtyItem' => 68,
                'PrcItem' => 2790,
            ],
            [
                'IndExe' => 1,
                'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                'QtyItem' => 2,
                'PrcItem' => 6783,
            ],
        ],
        'DscRcgGlobal' => [
            'TpoMov' => 'D',
            'TpoValor' => '%',
            'ValorDR' => 10,
        ],
        'Referencia' => [
            'TpoDocRef' => 'SET',
            'FolioRef' => $folios[33]+3,
            'RazonRef' => 'CASO 3605960-4',
        ],
    ],
    // CASO 3605960-5
    [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 61,
                'Folio' => $folios[61],
            ],
            'Emisor' => $Emisor,
            'Receptor' => [
                'RUTRecep' => '55666777-8',
                'RznSocRecep' => 'Empresa S.A.',
                'GiroRecep' => 'Servicios jurídicos',
                'DirRecep' => 'Santiago',
                'CmnaRecep' => 'Santiago',
            ],
            'Totales' => [
                'MntTotal' => 0,
            ]
        ],
        'Detalle' => [
            [
                'NmbItem' => 'Cajón AFECTO',
            ],
        ],
        'Referencia' => [
            [
                'TpoDocRef' => 'SET',
                'FolioRef' => $folios[61],
                'RazonRef' => 'CASO 3605960-1',
            ],
            [
                'TpoDocRef' => 33,
                'FolioRef' => $folios[33],
                'CodRef' => 2,
                'RazonRef' => 'CORRIGE GIRO DEL RECEPTOR',
            ],
        ]
    ],
    // CASO 3605960-6
    
    [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 61,
                'Folio' => $folios[61]+1,
            ],
            'Emisor' => $Emisor,
            'Receptor' => [
                'RUTRecep' => '55666777-8',
                'RznSocRecep' => 'Empresa S.A.',
                'GiroRecep' => 'Servicios jurídicos',
                'DirRecep' => 'Santiago',
                'CmnaRecep' => 'Santiago',
            ],
            'Totales' => [
                // estos valores serán calculados automáticamente
                'MntNeto' => 0,
                'TasaIVA' => \sasco\LibreDTE\Sii::getIVA(),
                'IVA' => 0,
                'MntTotal' => 0,
            ],
        ],
        'Detalle' => [
            [
                'NmbItem' => 'Pañuelo AFECTO',
                'QtyItem' => 360,
                'PrcItem' => 2869,
                'DescuentoPct' => 5,
            ],
            [
                'NmbItem' => 'ITEM 2 AFECTO',
                'QtyItem' => 291,
                'PrcItem' => 1930,
                'DescuentoPct' => 9,
            ],
        ],
        'Referencia' => [
            [
                'TpoDocRef' => 'SET',
                'FolioRef' => $folios[61]+1,
                'RazonRef' => 'CASO 3605960-2',
            ],
            [
                'TpoDocRef' => 33,
                'FolioRef' => $folios[33]+1,
                'CodRef' => 3,
                'RazonRef' => 'DEVOLUCION DE MERCADERIAS',
            ],
        ]
    ],
    // CASO 3605960-7
    
    [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 61,
                'Folio' => $folios[61]+2,
            ],
            'Emisor' => $Emisor,
            'Receptor' => [
                'RUTRecep' => '55666777-8',
                'RznSocRecep' => 'Empresa S.A.',
                'GiroRecep' => 'Servicios jurídicos',
                'DirRecep' => 'Santiago',
                'CmnaRecep' => 'Santiago',
            ],
            'Totales' => [
                // estos valores serán calculados automáticamente
                'MntNeto' => 0,
                'MntExe' => 0,
                'TasaIVA' => \sasco\LibreDTE\Sii::getIVA(),
                'IVA' => 0,
                'MntTotal' => 0,
            ],
        ],
        'Detalle' => [
            [
                'NmbItem' => 'Pintura B&W AFECTO',
                'QtyItem' => 29,
                'PrcItem' => 3215,
            ],
            [
                'NmbItem' => 'ITEM 2 AFECTO',
                'QtyItem' => 170,
                'PrcItem' => 3153,
            ],
            [
                'IndExe' => 1,
                'NmbItem' => 'ITEM 3 SERVICIO EXENTO',
                'QtyItem' => 1,
                'PrcItem' => 34845,
            ],
        ],
        'Referencia' => [
            [
                'TpoDocRef' => 'SET',
                'FolioRef' => $folios[61]+2,
                'RazonRef' => 'CASO 3605960-3',
            ],
            [
                'TpoDocRef' => 33,
                'FolioRef' => $folios[33]+2,
                'CodRef' => 1,
                'RazonRef' => 'ANULA FACTURA',
            ],
        ]
    ],
    // CASO 3605960-8
    [
        'Encabezado' => [
            'IdDoc' => [
                'TipoDTE' => 56,
                'Folio' => $folios[56],
            ],
            'Emisor' => $Emisor,
            'Receptor' => [
                'RUTRecep' => '55666777-8',
                'RznSocRecep' => 'Empresa S.A.',
                'GiroRecep' => 'Servicios jurídicos',
                'DirRecep' => 'Santiago',
                'CmnaRecep' => 'Santiago',
            ],
            'Totales' => [
                'MntTotal' => 0,
            ],
        ],
        'Detalle' => [
            [
                'NmbItem' => 'Cajón AFECTO',
            ],
        ],
        'Referencia' => [
            [
                'TpoDocRef' => 'SET',
                'FolioRef' => $folios[56],
                'RazonRef' => 'CASO 3605960-5',
            ],
            [
                'TpoDocRef' => 61,
                'FolioRef' => $folios[61],
                'CodRef' => 1,
                'RazonRef' => 'ANULA NOTA DE CREDITO ELECTRONICA',
            ],
        ]
    ],
];

// Objetos de Firma, Folios y EnvioDTE
$Firma = new \sasco\LibreDTE\FirmaElectronica($config['firma']);
$Folios = [];
foreach ($folios as $tipo => $cantidad)
    $Folios[$tipo] = new \sasco\LibreDTE\Sii\Folios(file_get_contents('xml/folios/'.$tipo.'.xml'));
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
//file_put_contents('xml/EnvioDTE.xml', $EnvioDTE->generar()); // guardar XML en sistema de archivos
$track_id = $EnvioDTE->enviar();
var_dump($track_id);

// si hubo errores mostrar
foreach (\sasco\LibreDTE\Log::readAll() as $error)
    echo $error,"\n";
