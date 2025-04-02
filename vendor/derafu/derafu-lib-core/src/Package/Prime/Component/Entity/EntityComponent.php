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

namespace Derafu\Lib\Core\Package\Prime\Component\Entity;

use Derafu\Lib\Core\Foundation\Abstract\AbstractComponent;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\DatasourceProviderWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\EntityComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\ManagerWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\RepositoryInterface;

/**
 * Componente "prime.entity".
 */
class EntityComponent extends AbstractComponent implements EntityComponentInterface
{
    public function __construct(
        private ManagerWorkerInterface $manager,
        private DatasourceProviderWorkerInterface $datasourceProvider
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkers(): array
    {
        return [
            'manager' => $this->manager,
            'datasource_provider' => $this->datasourceProvider,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getManagerWorker(): ManagerWorkerInterface
    {
        return $this->manager;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatasourceProviderWorker(): DatasourceProviderWorkerInterface
    {
        return $this->datasourceProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(string $source): RepositoryInterface
    {
        return $this->manager->getRepository($source);
    }
}
