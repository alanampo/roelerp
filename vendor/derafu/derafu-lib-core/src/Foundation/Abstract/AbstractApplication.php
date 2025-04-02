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

use Derafu\Lib\Core\Foundation\Configuration;
use Derafu\Lib\Core\Foundation\Contract\ApplicationInterface;
use Derafu\Lib\Core\Foundation\Contract\ConfigurationInterface;
use Derafu\Lib\Core\Foundation\Contract\KernelInterface;
use Derafu\Lib\Core\Foundation\Contract\PackageInterface;
use LogicException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Clase base para la clase principal de la aplicación.
 */
abstract class AbstractApplication implements ApplicationInterface
{
    /**
     * Instancia del núcleo de la aplicación.
     *
     * @var KernelInterface
     */
    protected KernelInterface $kernel;

    /**
     * Instancia de la clase para el patrón singleton.
     *
     * @var self
     */
    private static self $instance;

    /**
     * Constructor de la aplicación.
     *
     * Debe ser privado para respetar el patrón singleton.
     *
     * @param string|array|null $config Configuración de la aplicación.
     * Puede ser la clase que implementa la configuración, una ruta al archivo
     * de configuración o un arreglo con la configuración.
     */
    private function __construct(string|array|null $config)
    {
        $this->initialize($config);
    }

    /**
     * Inicializa la aplicación.
     *
     * @param string|array|null $config
     */
    protected function initialize(string|array|null $config)
    {
        // Cargar configuración.
        $configuration = $this->resolveConfiguration($config);

        // Iniciar el kernel.
        $kernelClass = $configuration->getKernelClass();
        $this->kernel = new $kernelClass($configuration);
    }

    /**
     * Resuelve y carga la configuración de la aplicación.
     *
     * @param string|array|null $config
     * @return ConfigurationInterface
     */
    protected function resolveConfiguration(
        string|array|null $config
    ): ConfigurationInterface {
        if ($config === null) {
            return new Configuration();
        }

        if (is_array($config)) {
            return new Configuration($config);
        }

        if (class_exists($config)) {
            return new $config();
        }

        return new Configuration($config);
    }

    /**
     * {@inheritDoc}
     */
    public function path(?string $path = null): string
    {
        return $this->kernel->getConfiguration()->resolvePath($path);
    }

    /**
     * {@inheritDoc}
     */
    public function config(string $name, mixed $default = null): mixed
    {
        return $this->kernel->getConfiguration()->get($name, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function hasPackage(string $package): bool
    {
        $servicesPrefix = $this->kernel->getConfiguration()->getServicesPrefix();
        $service = $servicesPrefix . $package;

        return $this->kernel->getContainer()->has($service);
    }

    /**
     * {@inheritDoc}
     */
    public function getPackage(string $package): PackageInterface
    {
        $servicesPrefix = $this->kernel->getConfiguration()->getServicesPrefix();
        $service = $servicesPrefix . $package;

        try {
            $instance = $this->kernel->getContainer()->get($service);
        } catch (ServiceNotFoundException $e) {
            $instance = null;
        }

        if (!$instance instanceof PackageInterface) {
            throw new LogicException(sprintf(
                'El paquete %s no existe en la aplicación.',
                $package
            ));
        }

        $config = $this->kernel->getConfiguration()->getPackageConfiguration(
            $package
        );
        $instance->setConfiguration($config);

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackages(): array
    {
        $packages = [];
        $ids = $this->kernel->getContainer()->findTaggedServiceIds('package');
        $servicesPrefix = $this->kernel->getConfiguration()->getServicesPrefix();
        foreach ($ids as $id => $tags) {
            $packages[str_replace($servicesPrefix, '', $id)] =
                $this->kernel->getContainer()->get($id)
            ;
        }

        return $packages;
    }

    /**
     * {@inheritDoc}
     */
    public function hasService(string $service): bool
    {
        return $this->kernel->getContainer()->has($service);
    }

    /**
     * {@inheritDoc}
     */
    public function getService(string $service): object
    {
        return $this->kernel->getContainer()->get($service);
    }

    /**
     * {@inheritDoc}
     */
    public static function getInstance(string|array|null $config = null): static
    {
        if (!isset(self::$instance)) {
            $class = static::class;
            self::$instance = new $class($config);
        }

        assert(self::$instance instanceof static);

        return self::$instance;
    }
}
