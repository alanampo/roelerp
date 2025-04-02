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

namespace Derafu\Lib\Core\Package\Prime\Component\Log\Worker;

use Derafu\Lib\Core\Package\Prime\Component\Log\Entity\Log;
use Monolog\Formatter\LineFormatter as MonologLineFormatter;
use Monolog\LogRecord as MonologLogRecord;

/**
 * Clase que genera una representación personalizada del mensaje del log.
 *
 * Se utiliza el LineFormatter oficial de Monolog y se añade el caller al final
 * de la línea.
 */
class LineFormatter extends MonologLineFormatter
{
    /**
     * Formatea el registro de la bitácora.
     *
     * @param MonologLogRecord $logRecord Registro de la bitácora.
     * @return string Registro formateado.
     */
    public function format(MonologLogRecord $logRecord): string
    {
        $message = parent::format($logRecord);

        if ($logRecord instanceof Log && isset($logRecord->caller)) {
            return sprintf(
                '%s %s.',
                $message,
                (string) $logRecord->caller,
            );
        }

        return $message;
    }
}
