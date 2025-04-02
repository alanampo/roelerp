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

use Derafu\Lib\Core\Foundation\Contract\HandlerInterface;
use Derafu\Lib\Core\Foundation\Contract\StrategyInterface;
use Derafu\Lib\Core\Foundation\Exception\StrategyException;

/**
 * Clase base para los handlers de los workers de la aplicación.
 */
abstract class AbstractHandler extends AbstractService implements HandlerInterface
{
    /**
     * Estrategias que el handler del worker puede utilizar.
     *
     * @var StrategyInterface[]
     */
    protected array $strategies;

    /**
     * Constructor del handler.
     *
     * @param array $strategies Estrategias que este handler puede manejar.
     */
    public function __construct(iterable $strategies = [])
    {
        $this->strategies = is_array($strategies)
            ? $strategies
            : iterator_to_array($strategies)
        ;
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
            'No se encontró la estrategia %s en el handler %s (%s).',
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
