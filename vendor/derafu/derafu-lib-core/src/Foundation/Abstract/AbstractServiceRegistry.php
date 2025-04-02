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

use Derafu\Lib\Core\Foundation\Contract\ServiceRegistryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Clase basa para registrar servicios en el contenedor de dependencias.
 */
abstract class AbstractServiceRegistry implements ServiceRegistryInterface
{
    /**
     * Listado con las clases de los ServiceRegistry de los que este depende.
     *
     * @var array<class-string>
     */
    protected $registries = [];

    /**
     * Ruta hacia la configuración de la aplicación.
     *
     * @var string
     */
    protected string $configPath;

    /**
     * Listado de archivos de configuración que se deben cargar.
     *
     * @var array
     */
    protected array $servicesFiles = [
        'services.yaml',
    ];

    /**
     * {@inheritDoc}
     */
    public function register(ContainerBuilder $container): void
    {
        // Primero registrar las dependencias.
        foreach ($this->getRegistries() as $registryClass) {
            (new $registryClass())->register($container);
        }

        // Luego registrar los servicios propios.
        $loader = new YamlFileLoader(
            $container,
            new FileLocator($this->getConfigPath())
        );
        foreach ($this->servicesFiles as $servicesFile) {
            $loader->load($servicesFile);
        }
    }

    /**
     * Obtiene los registros de servicios de las dependencias.
     *
     * @return array<class-string>
     */
    private function getRegistries(): array
    {
        return $this->registries;
    }

    /**
     * Obtiene la ruta base del registry.
     *
     * @return string
     */
    private function getBasePath(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * Obtiene la ruta de configuración.
     *
     * @return string
     */
    private function getConfigPath(): string
    {
        return $this->configPath ?? $this->getBasePath() . '/config';
    }
}
