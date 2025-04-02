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

namespace Derafu\Lib\Core\Package\Prime\Component\Log\Contract;

use Derafu\Lib\Core\Foundation\Contract\ComponentInterface;

/**
 * Interfaz para la clase de registros en bitácora (logging).
 */
interface LogComponentInterface extends ComponentInterface
{
    /**
     * Entrega la instancia del worker que escribe en la bitácora.
     *
     * @return LoggerWorkerInterface
     */
    public function getLoggerWorker(): LoggerWorkerInterface;

    /**
     * Recupera los logs de la bitácora.
     *
     * Puede recuperar todos los logs o los de un nivel específico. Además
     * permite entregar los logs en el orden que fueron ingresados o de más
     * nuevo a más antiguo.
     *
     * @param int|string|null $level Nivel de los logs que se desean recuperar.
     * @param bool $newFirst Indica si se deben entregar logs nuevos primero.
     * @return array Arreglo con los logs solicitados.
     */
    public function getLogs(
        int|string|null $level = null,
        bool $newFirst = true
    ): array;

    /**
     * Elimina los logs de un nivel específico de la bitácora.
     *
     * @param int|string|null $level Nivel de los logs que se desean limpiar.
     * @return void
     */
    public function clearLogs(int|string|null $level = null): void;

    /**
     * Recupera los logs y los elimina de la bitácora.
     *
     * Puede recuperar todos los logs o los de un nivel específico. Además
     * permite entregar los logs en el orden que fueron ingresados o de más
     * nuevo a más antiguo.
     *
     * @param int|string|null $level Nivel de los logs que se desean recuperar.
     * @param bool $newFirst Indica si se deben entregar logs nuevos primero.
     * @return array Arreglo con los logs solicitados.
     */
    public function flushLogs(
        int|string|null $level = null,
        bool $newFirst = true
    ): array;
}
