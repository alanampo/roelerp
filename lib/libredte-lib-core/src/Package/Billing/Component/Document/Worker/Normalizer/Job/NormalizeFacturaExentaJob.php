<?php

declare(strict_types=1);

/**
 * LibreDTE: Biblioteca PHP (Núcleo).
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o modificarlo
 * bajo los términos de la Licencia Pública General Affero de GNU publicada por
 * la Fundación para el Software Libre, ya sea la versión 3 de la Licencia, o
 * (a su elección) cualquier versión posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero SIN
 * GARANTÍA ALGUNA; ni siquiera la garantía implícita MERCANTIL o de APTITUD
 * PARA UN PROPÓSITO DETERMINADO. Consulte los detalles de la Licencia Pública
 * General Affero de GNU para obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de
 * GNU junto a este programa.
 *
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace libredte\lib\Core\Package\Billing\Component\Document\Worker\Normalizer\Job;

use Derafu\Lib\Core\Foundation\Abstract\AbstractJob;
use Derafu\Lib\Core\Foundation\Contract\JobInterface;
use Derafu\Lib\Core\Helper\Arr;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\EntityComponentInterface;
use libredte\lib\Core\Package\Billing\Component\Document\Contract\DocumentBagInterface;
use libredte\lib\Core\Package\Billing\Component\Document\Worker\Normalizer\Trait\NormalizeDescuentosRecargosTrait;
use libredte\lib\Core\Package\Billing\Component\Document\Worker\Normalizer\Trait\NormalizeDetalleTrait;
use libredte\lib\Core\Package\Billing\Component\Document\Worker\Normalizer\Trait\NormalizeIvaMntTotalTrait;

/**
 * Normalizador del documento factura exenta.
 */
class NormalizeFacturaExentaJob extends AbstractJob implements JobInterface
{
    // Traits usados por este normalizador.
    use NormalizeDetalleTrait;
    use NormalizeDescuentosRecargosTrait;
    use NormalizeIvaMntTotalTrait;

    public function __construct(
        protected EntityComponentInterface $entityComponent
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(DocumentBagInterface $bag): void
    {
        $data = $bag->getNormalizedData();

        // Completar con nodos por defecto.
        $data = Arr::mergeRecursiveDistinct([
            'Encabezado' => [
                'IdDoc' => false,
                'Emisor' => false,
                'Receptor' => false,
                'RUTSolicita' => false,
                'Totales' => [
                    'MntExe' => 0,
                    'MntTotal' => 0,
                ],
            ],
        ], $data);

        // Actualizar los datos normalizados.
        $bag->setNormalizedData($data);

        // Normalizar datos.
        $this->normalizeDetalle($bag);
        $this->normalizeDescuentosRecargos($bag);
        $this->normalizeIvaMntTotal($bag);
    }
}
