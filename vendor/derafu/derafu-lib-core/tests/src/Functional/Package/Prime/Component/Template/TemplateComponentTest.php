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

namespace Derafu\Lib\Tests\Functional\Package\Prime\Component\Certificate;

use Derafu\Lib\Core\Foundation\Abstract\AbstractApplication;
use Derafu\Lib\Core\Foundation\Abstract\AbstractServiceRegistry;
use Derafu\Lib\Core\Foundation\Application;
use Derafu\Lib\Core\Foundation\Configuration;
use Derafu\Lib\Core\Foundation\Kernel;
use Derafu\Lib\Core\Foundation\ServiceConfigurationCompilerPass;
use Derafu\Lib\Core\Foundation\ServiceProcessingCompilerPass;
use Derafu\Lib\Core\Helper\Arr;
use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\DataFormatterInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\RendererWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Service\DataFormatter;
use Derafu\Lib\Core\Package\Prime\Component\Template\Service\DataFormatterTwigExtension;
use Derafu\Lib\Core\Package\Prime\Component\Template\Service\DataHandler;
use Derafu\Lib\Core\Package\Prime\Component\Template\TemplateComponent;
use Derafu\Lib\Core\Package\Prime\Component\Template\Worker\Renderer\Strategy\PdfRendererStrategy;
use Derafu\Lib\Core\Package\Prime\Component\Template\Worker\Renderer\Strategy\TwigRendererStrategy;
use Derafu\Lib\Core\Package\Prime\Component\Template\Worker\RendererWorker;
use Derafu\Lib\Core\Package\Prime\PrimePackage;
use Derafu\Lib\Core\Support\Store\Abstract\AbstractStore;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateComponent::class)]
#[CoversClass(RendererWorker::class)]
#[CoversClass(AbstractApplication::class)]
#[CoversClass(AbstractServiceRegistry::class)]
#[CoversClass(Application::class)]
#[CoversClass(Configuration::class)]
#[CoversClass(Kernel::class)]
#[CoversClass(ServiceConfigurationCompilerPass::class)]
#[CoversClass(ServiceProcessingCompilerPass::class)]
#[CoversClass(Arr::class)]
#[CoversClass(Selector::class)]
#[CoversClass(DataFormatter::class)]
#[CoversClass(DataFormatterTwigExtension::class)]
#[CoversClass(DataHandler::class)]
#[CoversClass(PdfRendererStrategy::class)]
#[CoversClass(TwigRendererStrategy::class)]
#[CoversClass(PrimePackage::class)]
#[CoversClass(AbstractStore::class)]
#[CoversClass(DataContainer::class)]
class TemplateComponentTest extends TestCase
{
    private RendererWorkerInterface $renderer;

    public function setUp(): void
    {
        $app = Application::getInstance();

        $this->renderer = $app
            ->getPrimePackage()
            ->getTemplateComponent()
            ->getRendererWorker()
        ;

        $app->getService(DataFormatterInterface::class)
            ->addHandler('date', function ($date) {
                $timestamp = strtotime($date);
                return date('d/m/Y', $timestamp);
            })
        ;
    }

    public function testRenderCustomTemplateHtml()
    {
        $template = self::getFixturesPath() . '/package/prime/template/custom_template';
        $data = [
            'title' => 'Derafu',
            'content' => 'I Love Derafu <3',
            'date' => date('Y-m-d'),
        ];
        $html = $this->renderer->render($template, $data);

        $this->assertIsString($html);
    }

    public function testRenderCustomTemplatePdf()
    {
        $template = self::getFixturesPath() . '/package/prime/template/custom_template';
        $data = [
            'title' => 'Derafu',
            'content' => 'I Love Derafu <3',
            'date' => date('Y-m-d'),
            'options' => [
                'format' => 'pdf',
            ],
        ];
        $pdf = $this->renderer->render($template, $data);

        $this->assertIsString($pdf);
    }
}
