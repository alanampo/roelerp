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

namespace Derafu\Lib\Core\Package\Prime\Component\Log;

use Derafu\Lib\Core\Foundation\Abstract\AbstractComponent;
use Derafu\Lib\Core\Package\Prime\Component\Log\Contract\LogComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Log\Contract\LoggerWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Log\Entity\Level;

/**
 * Servicio de registro de bitácora (logging).
 */
class LogComponent extends AbstractComponent implements LogComponentInterface
{
    /**
     * Worker para escribir en la bitácora de registros.
     *
     * @var LoggerWorkerInterface
     */
    private LoggerWorkerInterface $logger;

    /**
     * Constructor del componente.
     *
     * @param LoggerWorkerInterface $logger
     */
    public function __construct(LoggerWorkerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkers(): array
    {
        return [
            'logger' => $this->logger,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getLoggerWorker(): LoggerWorkerInterface
    {
        return $this->logger;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogs(
        int|string|null $level = null,
        bool $newFirst = true
    ): array {
        $journal = $this->getLoggerWorker()->getJournal();

        // Obtener todos los logs.
        $records = $journal->all();
        if ($level === null) {
            return $newFirst ? array_reverse($records) : $records;
        }

        // Obtener logs de cierto nivel.
        $level = (new Level($level))->getCode();
        $filtered = [];
        foreach ($records as $record) {
            if ($record->level->value === $level) {
                $filtered[] = $record;
            }
        }

        return $newFirst ? array_reverse($filtered) : $filtered;
    }

    /**
     * {@inheritDoc}
     */
    public function clearLogs(int|string|null $level = null): void
    {
        $journal = $this->getLoggerWorker()->getJournal();

        // Borrar todos los logs.
        if ($level === null) {
            $journal->clear();
            return;
        }

        // Dejar todos los logs excepto los de cierto nivel (esos se borran).
        $level = (new Level($level))->getCode();
        $logs = array_filter(
            $journal->all(),
            fn ($log) => $log->level->value !== $level
        );
        $journal->clear();
        foreach ($logs as $logRecord) {
            $journal->add($logRecord);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function flushLogs(
        int|string|null $level = null,
        bool $newFirst = true
    ): array {
        $logs = $this->getLogs($level, $newFirst);
        $this->clearLogs($level);

        return $logs;
    }
}
