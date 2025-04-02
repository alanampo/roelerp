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

use Composer\InstalledVersions;
use Derafu\Lib\Core\Foundation\Contract\ConfigurationInterface;
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Symfony\Component\Yaml\Yaml;

/**
 * Configuración de la aplicación.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Directorio raíz de la aplicación.
     */
    private string $projectDir;

    /**
     * Prefijo para las configuraciones de Derafu Lib en el archivo de
     * configuración.
     *
     * Este prefijo no es necesario cambiarlo, pues solo se usa para
     * configuraciones internas de la biblioteca y no para las configuraciones
     * de "lo que usa" la biblioteca.
     *
     * @var string
     */
    protected string $prefix = 'derafu.lib.';

    /**
     * Define un mapa de parámetros (índice) y de qué configuración (valor)
     * obtener el valor que se asignará a dicho parámetro.
     *
     * @var array
     */
    protected array $parameters = [
        'kernel.project_dir' => 'app.project_dir',
    ];

    /**
     * Instancia que administra los datos de la configuración.
     *
     * @var DataContainerInterface
     */
    private DataContainerInterface $data;

    /**
     * Esquema de la estructura de los datos de la configuración.
     *
     * @var array
     */
    protected array $dataSchema = [
        'app' => [
            'types' => 'array',
            'default' => [],
            'schema' => [
                'project_dir' => [
                    'types' => 'string',
                ],
            ],
        ],
        'derafu' => [
            'types' => 'array',
            'default' => [],
            'schema' => [
                'lib' => [
                    'types' => 'array',
                    'default' => [],
                    'schema' => [
                        'services' => [
                            'types' => 'array',
                            'default' => [],
                            'schema' => [
                                'prefix' => [
                                    'types' => 'string',
                                    'default' => 'derafu.lib.',
                                ],
                                'kernelClass' => [
                                    'types' => 'string',
                                    'default' => Kernel::class,
                                ],
                                'serviceRegistryClass' => [
                                    'types' => 'string',
                                    'default' => ServiceRegistry::class,
                                ],
                                'compilerPassClass' => [
                                    'types' => 'string',
                                    'default' => ServiceProcessingCompilerPass::class,
                                ],
                            ],
                        ],
                    ],
                ],
                'packages' => [
                    'types' => 'array',
                    'default' => [],
                ],
            ],
        ],
    ];

    /**
     * Constructor de la configuración.
     *
     * @param string|array|null $config Archivo de configuración que se debe
     * cargar o el arreglo con la configuración.
     */
    public function __construct(string|array|null $config = null)
    {
        $this->load($config, $this->dataSchema);
        $this->configure();
    }

    /**
     * Carga la configuración de la aplicación desde un archivo.
     *
     * @param string $config
     * @return static
     */
    protected function load(string|array|null $config, array $schema): static
    {
        $config = $config ?? $this->resolvePath('config/config.yaml');

        $config = is_array($config)
            ? $config
            : (array) Yaml::parseFile($config)
        ;

        $this->data = new DataContainer($config, $schema);

        return $this;
    }

    /**
     * Realiza las configuraciones de la aplicación según lo que se haya
     * cargado como configuración.
     *
     * @return void
     */
    protected function configure(): void
    {
        if ($this->data->get('app.project_dir') === null) {
            $this->data->set('app.project_dir', $this->getProjectDir());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data->get($key, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        $parameters = [];
        foreach ($this->parameters as $name => $source) {
            $value = $this->get($source);
            if ($value !== null) {
                $parameters[$name] = $value;
            }
        }

        return $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getServicesPrefix(): string
    {
        return $this->get($this->prefix . 'services.prefix');
    }

    /**
     * {@inheritDoc}
     */
    public function getKernelClass(): string
    {
        return $this->get($this->prefix . 'services.kernelClass');
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceRegistryClass(): string
    {
        return $this->get($this->prefix . 'services.serviceRegistryClass');
    }

    /**
     * {@inheritDoc}
     */
    public function getCompilerPassClass(): ?string
    {
        return $this->get($this->prefix . 'services.compilerPassClass');
    }

    /**
     * {@inheritDoc}
     */
    protected function getProjectDir(): string
    {
        if (!isset($this->projectDir)) {
            $vendorDir = InstalledVersions::getRootPackage();
            $this->projectDir = realpath($vendorDir['install_path']);
        }

        return $this->projectDir;
    }

    /**
     * {@inheritDoc}
     */
    public function resolvePath(?string $path = null): string
    {
        if (!$path) {
            return $this->getProjectDir();
        }

        if ($path[0] === '/') {
            return $path;
        }

        return $this->getProjectDir() . '/' . $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackageConfiguration(
        string $package
    ): array|DataContainerInterface {
        $prefix = explode('.', $this->prefix)[0] . '.';
        $config = $this->data->get($prefix . 'packages.' . $package);

        return $config ?? [];
    }
}
