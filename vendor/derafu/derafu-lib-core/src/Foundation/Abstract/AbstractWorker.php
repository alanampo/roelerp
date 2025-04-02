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

namespace Derafu\Lib\Core\Foundation\Abstract;

use Derafu\Lib\Core\Common\Trait\ConfigurableTrait;
use Derafu\Lib\Core\Foundation\Contract\HandlerInterface;
use Derafu\Lib\Core\Foundation\Contract\JobInterface;
use Derafu\Lib\Core\Foundation\Contract\StrategyInterface;
use Derafu\Lib\Core\Foundation\Contract\WorkerInterface;
use Derafu\Lib\Core\Foundation\Exception\HandlerException;
use Derafu\Lib\Core\Foundation\Exception\JobException;
use Derafu\Lib\Core\Foundation\Exception\StrategyException;

/**
 * Clase base para los workers de la aplicación.
 */
abstract class AbstractWorker extends AbstractService implements WorkerInterface
{
    use ConfigurableTrait;

    /**
     * Trabajos que el worker implementa.
     *
     * @var JobInterface[]
     */
    protected array $jobs;

    /**
     * Handlers que el worker implementa.
     *
     * @var HandlerInterface[]
     */
    protected array $handlers;

    /**
     * Estrategias que el worker implementa.
     *
     * @var StrategyInterface[]
     */
    protected array $strategies;

    /**
     * Constructor del worker.
     *
     * @param array $jobs Jobs que este worker implementa.
     * @param array $handlers Handlers que este worker implementa.
     * @param array $strategies Estrategias que este worker implementa.
     */
    public function __construct(
        iterable $jobs = [],
        iterable $handlers = [],
        iterable $strategies = []
    ) {
        $this->jobs = is_array($jobs)
            ? $jobs
            : iterator_to_array($jobs)
        ;

        $this->handlers = is_array($handlers)
            ? $handlers
            : iterator_to_array($handlers)
        ;

        $this->strategies = is_array($strategies)
            ? $strategies
            : iterator_to_array($strategies)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        $regex = "/\\\\Package\\\\([A-Za-z0-9_]+)\\\\Component\\\\([A-Za-z0-9_]+)\\\\Worker\\\\([A-Za-z0-9_]+)Worker/";

        $class = (string) $this;
        if (preg_match($regex, $class, $matches)) {
            return $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
        }

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getJob(string $job): JobInterface
    {
        if (!isset($this->jobs[$job])) {
            throw new JobException(sprintf(
                'No se encontró el trabajo %s en el worker %s (%s).',
                $job,
                $this->getName(),
                $this->getId(),
            ));
        }

        return $this->jobs[$job];
    }

    /**
     * {@inheritDoc}
     */
    public function getJobs(): array
    {
        return $this->jobs;
    }

    /**
     * {@inheritDoc}
     */
    public function getHandler(string $handler): HandlerInterface
    {
        if (!isset($this->handlers[$handler])) {
            throw new HandlerException(sprintf(
                'No se encontró el manejador (handler) %s en el worker %s (%s).',
                $handler,
                $this->getName(),
                $this->getId(),
            ));
        }

        return $this->handlers[$handler];
    }

    /**
     * {@inheritDoc}
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * {@inheritDoc}
     */
    public function getStrategy(string $strategy): StrategyInterface
    {
        $strategies = [$strategy];
        if (!str_contains($strategy, '.')) {
            $strategies[] = 'default.' . $strategy;
        }

        foreach ($strategies as $name) {
            if (isset($this->strategies[$name])) {
                return $this->strategies[$name];
            }
        }

        throw new StrategyException(sprintf(
            'No se encontró la estrategia %s en el worker %s (%s).',
            $strategy,
            $this->getName(),
            $this->getId(),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }
}
