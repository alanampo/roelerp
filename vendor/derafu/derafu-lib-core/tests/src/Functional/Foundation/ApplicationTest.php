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

namespace Derafu\Lib\Tests\Functional\Foundation;

use Derafu\Lib\Core\Foundation\Abstract\AbstractService;
use Derafu\Lib\Core\Foundation\Abstract\AbstractServiceRegistry;
use Derafu\Lib\Core\Foundation\Application;
use Derafu\Lib\Core\Foundation\Configuration;
use Derafu\Lib\Core\Foundation\Kernel;
use Derafu\Lib\Core\Foundation\ServiceConfigurationCompilerPass;
use Derafu\Lib\Core\Foundation\ServiceProcessingCompilerPass;
use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Core\Support\Store\Abstract\AbstractStore;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

#[CoversClass(AbstractServiceRegistry::class)]
#[CoversClass(Application::class)]
#[CoversClass(ServiceConfigurationCompilerPass::class)]
#[CoversClass(ServiceProcessingCompilerPass::class)]
#[CoversClass(Configuration::class)]
#[CoversClass(Kernel::class)]
#[CoversClass(Selector::class)]
#[CoversClass(AbstractStore::class)]
#[CoversClass(DataContainer::class)]
class ApplicationTest extends TestCase
{
    private Application $app;

    protected $config = __DIR__ . '/../../../fixtures/application/config.yaml';

    // Cargar la configuración específica para pruebas.
    protected function setUp(): void
    {
        $this->app = Application::getInstance($this->config);
    }

    // Obtener un servicio público.
    public function testApplicationGetServicePublic(): void
    {
        $service = $this->app->getService('public_service');
        $this->assertInstanceOf(TestService::class, $service);
    }

    // Intentar obtener un servicio privado.
    public function testApplicationGetServicePrivate(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->app->getService('non_existent_service');
    }

    // Intentar obtener un servicio no definido.
    public function testApplicationGetServiceNotFound(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->app->getService('non_existent_service');
    }

    // Obtener servicio que requiere un adaptador.
    public function testApplicationGetServiceStdClass(): void
    {
        $service = $this->app->getService('stdClass_service');
        $this->assertInstanceOf(stdClass::class, $service);
    }

    // Obtener servicio a través de la función global.
    public function testApplicationGetSingleton(): void
    {
        $app = Application::getInstance($this->config);

        $service = $app->getService('public_service');
        $this->assertInstanceOf(TestService::class, $service);
    }
}

class TestServiceRegistry extends AbstractServiceRegistry
{
    protected string $configPath = __DIR__ . '/../../../fixtures/application';
}

class TestService extends AbstractService
{
}
