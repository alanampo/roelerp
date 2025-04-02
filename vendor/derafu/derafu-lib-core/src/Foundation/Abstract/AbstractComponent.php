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
use Derafu\Lib\Core\Foundation\Contract\ComponentInterface;
use Derafu\Lib\Core\Foundation\Contract\WorkerInterface;
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;
use LogicException;

/**
 * Clase base para los componentes de la aplicación.
 */
abstract class AbstractComponent extends AbstractService implements ComponentInterface
{
    use ConfigurableTrait;

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        $regex = "/\\\\Package\\\\([A-Za-z0-9_]+)\\\\Component\\\\([A-Za-z0-9_]+)\\\\/";

        $class = (string) $this;
        if (preg_match($regex, $class, $matches)) {
            return $matches[1] . ' ' . $matches[2];
        }

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getWorker(string $worker): WorkerInterface
    {
        // Obtener todos los workers del paquete.
        $workers = $this->getWorkers();

        // No se encontró el worker.
        if (!isset($workers[$worker])) {
            throw new LogicException(sprintf(
                'El worker %s no existe en el componente.',
                $worker
            ));
        }

        // Entregar el worker encontrado.
        return $workers[$worker];
    }

    /**
     * Entrega la configuración de un worker del componente.
     *
     * @param string $worker
     * @return array|DataContainerInterface
     */
    protected function getWorkerConfiguration(
        string $worker
    ): array|DataContainerInterface {
        $config = $this->getConfiguration()->get('workers.' . $worker);

        return $config ?? [];
    }
}
