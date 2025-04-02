<?php

declare(strict_types=1);

/**
 * Derafu: Biblioteca PHP (Núcleo).
 * Copyright (C) Derafu <https://www.derafu.org>
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
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 *
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Service;

use Derafu\Lib\Core\Enum\Currency;
use Derafu\Lib\Core\Helper\Date;
use Derafu\Lib\Core\Helper\Rut;
use Derafu\Lib\Core\Package\Prime\Component\Template\Abstract\AbstractTemplateDataHandler;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\DataHandlerInterface;

/**
 * Servicio para traducir datos genéricos de una plantilla a su representación
 * para ser utilizada en su renderización.
 */
class TemplateDataHandler extends AbstractTemplateDataHandler implements DataHandlerInterface
{
    /**
     * Mapa de handlers genéricos.
     *
     * @return array
     */
    protected function createHandlers(): array
    {
        return [
            // RUT.
            'rut' => fn (string $rut) => Rut::formatFull($rut),
            // Números.
            'number' => function (int|float|string $num) {
                $num = round((float) $num);
                return number_format($num, 0, ',', '.');
            },
            // Montos usando una moneda.
            'currencyAmount' => function (string $value) {
                [$codigo, $num] = explode(':', $value);
                $currency = Currency::tryFrom($codigo) ?? Currency::XXX;
                return $currency->format((float) $num);
            },
            // Fechas.
            'date' => function (string $fecha) {
                $timestamp = strtotime($fecha);
                return date('d/m/Y', $timestamp);
            },
            'dateLong' => fn (string $fecha) => Date::formatSpanish($fecha),
            'period' => fn (int $period) => Date::formatPeriodSpanish($period),
            'year' => fn (string $fecha) => explode('-', $fecha, 2)[0],
        ];
    }
}
