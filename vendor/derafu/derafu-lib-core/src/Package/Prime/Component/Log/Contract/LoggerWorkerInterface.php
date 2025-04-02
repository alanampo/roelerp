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

use Derafu\Lib\Core\Foundation\Contract\WorkerInterface;
use Derafu\Lib\Core\Support\Store\Contract\JournalInterface;
use Psr\Log\LoggerInterface;

/**
 * Interfaz para la clase de registros en bitácora (logging).
 */
interface LoggerWorkerInterface extends WorkerInterface, LoggerInterface
{
    /**
     * Entrega la bitácora de registros en la que el logger está escribiendo.
     *
     * @return JournalInterface
     */
    public function getJournal(): JournalInterface;

    /**
     * Registra un mensaje en el log.
     *
     * @param int|string $level Nivel del mensaje que se desea registrar.
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function log($level, $message, array $context = []): void;

    /**
     * Registra un mensaje de nivel DEBUG en la bitácora.
     *
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function debug($message, array $context = []): void;

    /**
     * Registra un mensaje de nivel INFO en la bitácora.
     *
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function info($message, array $context = []): void;

    /**
     * Registra un mensaje de nivel NOTICE en la bitácora.
     *
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function notice($message, array $context = []): void;

    /**
     * Registra un mensaje de nivel WARNING en la bitácora.
     *
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function warning($message, array $context = []): void;

    /**
     * Registra un mensaje de nivel ERROR en la bitácora.
     *
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function error($message, array $context = []): void;

    /**
     * Registra un mensaje de nivel CRITICAL en la bitácora.
     *
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function critical($message, array $context = []): void;

    /**
     * Registra un mensaje de nivel ALERT en la bitácora.
     *
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function alert($message, array $context = []): void;

    /**
     * Registra un mensaje de nivel EMERGENCY en la bitácora.
     *
     * @param string $message El mensaje que se desea registrar.
     * @param array $context Contexto adicional para el mensaje.
     * @return void
     */
    public function emergency($message, array $context = []): void;
}
