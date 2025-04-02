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

use Derafu\Lib\Core\Foundation\Abstract\AbstractWorker;
use Derafu\Lib\Core\Package\Prime\Component\Log\Contract\LoggerWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Log\Entity\Caller;
use Derafu\Lib\Core\Package\Prime\Component\Log\Entity\Level;
use Derafu\Lib\Core\Support\Store\Contract\JournalInterface;
use Derafu\Lib\Core\Support\Store\Journal;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;

/**
 * Clase de LoggerServiceInterface con la implementación del sistema de log.
 */
class LoggerWorker extends AbstractWorker implements LoggerWorkerInterface
{
    /**
     * Instancia del logger de Monolog.
     *
     * @var Logger
     */
    private Logger $logger;

    /**
     * Instancia del almacenamiento de los logs.
     *
     * @var JournalInterface
     */
    private JournalInterface $journal;

    /**
     * Esquema de la configuración del worker.
     *
     * @var array
     */
    protected array $configurationSchema = [
        'channel' => [
            'types' => 'string',
            'default' => 'derafu_lib',
        ],
    ];

    /**
     * Arreglo con los handlers del logger.
     *
     * @var array
     */
    private array $loggerHandlers;

    /**
     * Formateador para los mensajes del log.
     *
     * @var FormatterInterface|null
     */
    private ?FormatterInterface $formatter;

    /**
     * Constructor del worker.
     *
     * @param array $handlers Lista de handlers adicionales.
     * @param FormatterInterface|null $formatter Formatter para los logs.
     */
    public function __construct(
        array $handlers = [],
        ?FormatterInterface $formatter = null,
    ) {
        $this->handlers = $handlers;
        $this->formatter = $formatter;
    }

    /**
     * {@inheritDoc}
     */
    public function getJournal(): JournalInterface
    {
        return $this->journal;
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $level = (new Level($level))->getMonologLevel();
        $context = $this->normalizeContext($context);
        $this->getLogger()->log($level, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function debug($message, array $context = []): void
    {
        $context = $this->normalizeContext($context);
        $this->getLogger()->debug($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function info($message, array $context = []): void
    {
        $context = $this->normalizeContext($context);
        $this->getLogger()->info($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function notice($message, array $context = []): void
    {
        $context = $this->normalizeContext($context);
        $this->getLogger()->notice($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function warning($message, array $context = []): void
    {
        $context = $this->normalizeContext($context);
        $this->getLogger()->warning($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function error($message, array $context = []): void
    {
        $context = $this->normalizeContext($context);
        $this->getLogger()->error($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function critical($message, array $context = []): void
    {
        $context = $this->normalizeContext($context);
        $this->getLogger()->critical($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function alert($message, array $context = []): void
    {
        $context = $this->normalizeContext($context);
        $this->getLogger()->alert($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function emergency($message, array $context = []): void
    {
        $context = $this->normalizeContext($context);
        $this->getLogger()->emergency($message, $context);
    }

    /**
     * Normaliza el contexto del registro de la bitácora.
     *
     * @param array $context
     * @return array
     */
    private function normalizeContext(array $context): array
    {
        // Se agrega el caller.
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $context['__caller'] = new Caller(
            file: $trace[1]['file'] ?? null,
            line: $trace[1]['line'] ?? null,
            function: $trace[2]['function'] ?? null,
            class: $trace[2]['class'] ?? null,
            type: $trace[2]['type'] ?? null
        );

        // Entregar el contexto normalizado.
        return $context;
    }

    /**
     * Entrega la instancia del logger asegurándo que esté inicializada.
     *
     * @return Logger
     */
    private function getLogger(): Logger
    {
        if (!isset($this->logger)) {
            $this->initialize();
        }

        return $this->logger;
    }

    /**
     * Inicializa el logger.
     *
     * @return void
     */
    private function initialize(): void
    {
        // Crear el logger.
        $channel = $this->getConfiguration()->get('channel');
        $this->logger = new Logger($channel);

        // Crear el procesador de los registros de la bitácora.
        $processor = new Processor();
        $this->logger->pushProcessor($processor);

        // Crear el almacenamiento de los registros de la bitácora.
        $this->journal = new Journal();

        // Crear el handler de almacenamiento en memoria de los logs.
        $journalHandler = new JournalHandler($this->journal);
        if ($this->formatter !== null) {
            $journalHandler->setFormatter($this->formatter);
        }
        $this->loggerHandlers[] = $journalHandler;

        // Agregar los handlers.
        foreach ($this->loggerHandlers as $handler) {
            $this->logger->pushHandler($handler);
        }
    }
}
