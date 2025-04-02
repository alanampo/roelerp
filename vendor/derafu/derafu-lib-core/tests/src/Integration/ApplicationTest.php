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

namespace Derafu\Lib\Tests\Integration;

use Derafu\Lib\Core\Foundation\Abstract\AbstractPackage;
use Derafu\Lib\Core\Foundation\Abstract\AbstractServiceRegistry;
use Derafu\Lib\Core\Foundation\Application;
use Derafu\Lib\Core\Foundation\Configuration;
use Derafu\Lib\Core\Foundation\Kernel;
use Derafu\Lib\Core\Foundation\ServiceConfigurationCompilerPass;
use Derafu\Lib\Core\Foundation\ServiceProcessingCompilerPass;
use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Core\Helper\Xml as XmlHelper;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Log\Contract\LogComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\SignatureComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Entity\Xml;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Worker\EncoderWorker as XmlEncoderWorker;
use Derafu\Lib\Core\Package\Prime\Component\Xml\XmlComponent;
use Derafu\Lib\Core\Package\Prime\Contract\PrimePackageInterface;
use Derafu\Lib\Core\Package\Prime\PrimePackage;
use Derafu\Lib\Core\Support\Store\Abstract\AbstractStore;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

#[CoversClass(Application::class)]
#[CoversClass(AbstractPackage::class)]
#[CoversClass(AbstractServiceRegistry::class)]
#[CoversClass(XmlComponent::class)]
#[CoversClass(ServiceConfigurationCompilerPass::class)]
#[CoversClass(ServiceProcessingCompilerPass::class)]
#[CoversClass(XmlHelper::class)]
#[CoversClass(Xml::class)]
#[CoversClass(XmlEncoderWorker::class)]
#[CoversClass(PrimePackage::class)]
#[CoversClass(Configuration::class)]
#[CoversClass(Kernel::class)]
#[CoversClass(Selector::class)]
#[CoversClass(AbstractStore::class)]
#[CoversClass(DataContainer::class)]
class ApplicationTest extends TestCase
{
    private array $testCases = [
        'packages' => [
            'prime' => [
                'class' => PrimePackageInterface::class,
                'components' => [
                    'certificate' => CertificateComponentInterface::class,
                    'log' => LogComponentInterface::class,
                    'signature' => SignatureComponentInterface::class,
                    'xml' => XmlComponentInterface::class,
                ],
            ],
        ] ,
    ];

    public function testApplicationGlobalFunction(): void
    {
        $app = Application::getInstance();

        $this->assertInstanceOf(Application::class, $app);
    }

    public function testApplicationGetPackages(): void
    {
        $app = Application::getInstance();

        foreach ($this->testCases['packages'] as $name => $package) {
            $this->assertInstanceOf(
                $package['class'],
                $app->getPackage($name)
            );
        }
    }

    public function testApplicationServiceNotFoundWrongServiceName(): void
    {
        $app = Application::getInstance();

        $this->expectException(ServiceNotFoundException::class);

        $app->getService('foundation');
    }

    public function testApplicationServiceNotFoundInterfaceServiceName(): void
    {
        $app = Application::getInstance();

        $this->expectException(ServiceNotFoundException::class);

        $app->getService(PrimePackageInterface::class);
    }

    public function testApplicationServicePackagesCount(): void
    {
        $app = Application::getInstance();

        $this->assertSame(1, count($app->getPackages()));
    }

    public function testApplicationServiceMagicHierarchyPrimeXmlEncoder(): void
    {
        $app = Application::getInstance();
        $package = $app->getPackage('prime');
        $component = $package->getComponent('xml');
        $worker = $component->getWorker('encoder');

        $data = ['root' => ['element' => 'Árbol']];
        $expected = '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n<root>\n  <element>" .
            mb_convert_encoding('Árbol', 'ISO-8859-1', 'UTF-8') . "</element>\n</root>\n"
        ;

        assert($worker instanceof XmlEncoderWorker);

        $result = $worker->encode($data);
        $this->assertSame($expected, $result->saveXml());
    }

    public function testApplicationServiceMethodsHierarchyPrimeXmlEncoder(): void
    {
        $app = Application::getInstance();
        $package = $app->getPrimePackage();
        $component = $package->getXmlComponent();
        $worker = $component->getEncoderWorker();

        $data = ['root' => ['element' => 'Árbol']];
        $expected = '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n<root>\n  <element>" .
            mb_convert_encoding('Árbol', 'ISO-8859-1', 'UTF-8') . "</element>\n</root>\n"
        ;

        $result = $worker->encode($data);
        $this->assertSame($expected, $result->saveXml());
    }

    public function testApplicationPackageIdentification(): void
    {
        $app = Application::getInstance();
        $package = $app->getPrimePackage();

        $this->assertSame(PrimePackage::class, (string) $package);
        $this->assertSame('prime', $package->getId());
        $this->assertSame('Prime', $package->getName());
    }

    public function testApplicationComponentIdentification(): void
    {
        $app = Application::getInstance();
        $component = $app->getPrimePackage()->getXmlComponent();

        $this->assertSame(XmlComponent::class, (string) $component);
        $this->assertSame('prime.xml', $component->getId());
        $this->assertSame('Prime Xml', $component->getName());
    }

    public function testApplicationWorkerIdentification(): void
    {
        $app = Application::getInstance();
        $worker = $app->getPrimePackage()->getXmlComponent()->getEncoderWorker();

        $this->assertSame(XmlEncoderWorker::class, (string) $worker);
        $this->assertSame('prime.xml.encoder', $worker->getId());
        $this->assertSame('Prime Xml Encoder', $worker->getName());
    }
}
