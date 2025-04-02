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

namespace Derafu\Lib\Core\Common\Trait;

use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;
use Derafu\Lib\Core\Support\Store\DataContainer;

/**
 * Trait para clases que deben implementar un sistema simple de configuración.
 *
 * @see Derafu\Lib\Core\Common\Contract\ConfigurableInterface
 */
trait ConfigurableTrait
{
    /**
     * Contenedor para las configuraciones.
     *
     * @var DataContainerInterface
     */
    protected DataContainerInterface $configuration;

    /**
     * {@inheritDoc}
     */
    public function setConfiguration(
        array|DataContainerInterface $configuration
    ): static {
        if (is_array($configuration)) {
            $configuration = new DataContainer(
                $configuration,
                $this->getConfigurationSchema()
            );
        }

        $this->configuration = $configuration;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(): DataContainerInterface
    {
        if (!isset($this->configuration)) {
            $this->setConfiguration([]);
        }

        $this->configuration->validate();

        return $this->configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveConfiguration(
        array $configuration = []
    ): DataContainerInterface {
        if (isset($this->configuration)) {
            $configuration = array_merge(
                $this->configuration->all(),
                $configuration
            );
        }

        return $this->setConfiguration($configuration)->configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationSchema(): array
    {
        return $this->configurationSchema ?? [];
    }
}
