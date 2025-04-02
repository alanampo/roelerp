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
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Clase para asignar cómo configurar los servicios previo a la compilación.
 */
class ServiceConfigurationCompilerPass implements CompilerPassInterface
{
    /**
     * Procesar servicios.
     *
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $configuration = $container->get(ConfigurationInterface::class);

        foreach ($container->getDefinitions() as $id => $definition) {
            if (method_exists($definition->getClass(), 'setConfiguration')) {
                $config = $this->resolveConfiguration($definition, $configuration);
                if ($config) {
                    $definition->addMethodCall('setConfiguration', [$config]);
                }
            }
        }
    }

    /**
     * Entrega la configuración de una definición si existe, sino arreglo vacio.
     *
     * @param Definition $definition
     * @param ConfigurationInterface $configuration
     * @return array|DataContainerInterface
     */
    private function resolveConfiguration(
        Definition $definition,
        ConfigurationInterface $configuration
    ): array|DataContainerInterface {
        $config = [];
        $tags = $definition->getTags();

        if (!empty($tags['service:package'])) {
            $package = $tags['service:package'][0]['name'];
            $config = $configuration->getPackageConfiguration($package);
        } elseif (!empty($tags['service:component'])) {
            $package = $tags['service:component'][0]['package'];
            $component = $tags['service:component'][0]['name'];
            $config = $configuration->getPackageConfiguration($package);
            $config = $config['components'][$component] ?? [];
        } elseif (!empty($tags['service:worker'])) {
            $package = $tags['service:worker'][0]['package'];
            $component = $tags['service:worker'][0]['component'];
            $worker = $tags['service:worker'][0]['name'];
            $config = $configuration->getPackageConfiguration($package);
            $config = $config['components'][$component]['workers'][$worker] ?? [];
        } elseif (!empty($tags['service:command'])) {
            $package = $tags['service:command'][0]['package'];
            $component = $tags['service:command'][0]['component'];
            $command = $tags['service:command'][0]['name'];
            $config = $configuration->getPackageConfiguration($package);
            $config = $config['components'][$component]['commands'][$command] ?? [];
        }

        return $config;
    }
}
