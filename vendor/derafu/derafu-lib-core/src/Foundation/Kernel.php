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

namespace Derafu\Lib\Core\Foundation;

use Derafu\Lib\Core\Foundation\Contract\ConfigurationInterface;
use Derafu\Lib\Core\Foundation\Contract\KernelInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Núcleo de la aplicación.
 */
class Kernel implements KernelInterface
{
    /**
     * Contenedor de servicios de la aplicación.
     *
     * @var ContainerBuilder
     */
    private ContainerBuilder $container;

    /**
     * {@inheritDoc}
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->initialize($configuration);
    }

    /**
     * Inicializa el núcleo.
     *
     * @param ConfigurationInterface $configuration
     * @return void
     */
    private function initialize(ConfigurationInterface $configuration): void
    {
        // Crear el contenedor de dependencias.
        $this->container = new ContainerBuilder();

        // Registrar configuración como servicio.
        $this->container
            ->register(ConfigurationInterface::class)
            ->setSynthetic(true)
        ;
        $this->container->set(ConfigurationInterface::class, $configuration);

        // Cargar parámetros al contenedor.
        foreach ($configuration->getParameters() as $name => $value) {
            $this->container->setParameter($name, $value);
        }

        // Registrar servicios.
        $serviceRegistryClass = $configuration->getServiceRegistryClass();
        $serviceRegistry = new $serviceRegistryClass();
        $serviceRegistry->register($this->container);

        // Procesar servicios con un Compiler Pass si está definido.
        $compilerPassClass = $configuration->getCompilerPassClass();
        if ($compilerPassClass !== null) {
            $servicesPrefix = $configuration->getServicesPrefix();
            $this->container->addCompilerPass(
                new $compilerPassClass($servicesPrefix)
            );
        }

        // Agregar el compiler pass para inyectar las configuraciones.
        $compilerPassClass = ServiceConfigurationCompilerPass::class;
        $this->container->addCompilerPass(new $compilerPassClass());

        // Compilar el contenedor de servicios.
        $this->container->compile();
    }

    /**
     * {@inheritDoc}
     */
    public function getContainer(): ContainerBuilder
    {
        return $this->container;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(): ConfigurationInterface
    {
        return $this->container->get(ConfigurationInterface::class);
    }
}
