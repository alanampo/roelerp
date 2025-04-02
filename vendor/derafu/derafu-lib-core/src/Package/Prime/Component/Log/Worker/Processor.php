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
use Monolog\LogRecord as MonologLogRecord;

/**
 * Procesador del registro de la bitácora.
 */
class Processor
{
    /**
     * Procesador del registro de la bitácora.
     *
     * @param MonologLogRecord $logRecord Registro original de monolog.
     * @return Log Registro convertido de la biblioteca.
     */
    public function __invoke(MonologLogRecord $logRecord): Log
    {
        // Extraer caller si existe.
        $context = $logRecord->context;
        $caller = $context['__caller'] ?? null;
        unset($context['__caller']);

        // Crear instancia de LogRecord.
        $log = new Log(
            datetime: $logRecord->datetime,
            channel: $logRecord->channel,
            level: $logRecord->level,
            message: $logRecord->message,
            context: $context,
            extra: $logRecord->extra,
            formatted: $logRecord->formatted
        );

        // Asignar datos adicionales del registro de la bitácora.
        $log->code = $context['code'] ?? $logRecord->level->value;
        $log->caller = $caller;

        // Entregar el registro de la bitácora personalizado.
        return $log;
    }
}
