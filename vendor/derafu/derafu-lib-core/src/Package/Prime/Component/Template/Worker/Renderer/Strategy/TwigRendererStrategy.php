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

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Worker\Renderer\Strategy;

use Derafu\Lib\Core\Foundation\Abstract\AbstractStrategy;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\Renderer\Strategy\HtmlRendererStrategyInterface;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;

/**
 * Estrategia de renderizado de archivos HTML mediante Twig (.html.twig).
 */
class TwigRendererStrategy extends AbstractStrategy implements HtmlRendererStrategyInterface
{
    /**
     * Renderizador de plantillas HTML con Twig.
     *
     * @var Environment
     */
    private Environment $twig;

    /**
     * Cargador de plantillas mediante el sistema de archivos para Twig.
     */
    private FilesystemLoader $filesystemLoader;

    /**
     * Rutas donde están las plantillas.
     *
     * @var array
     */
    private array $paths;

    /**
     * Extensiones de Twig que se utilizarán al renderizar.
     *
     * @var ExtensionInterface[]
     */
    private array $extensions;

    /**
     * Constructor de la estrategia.
     *
     * @param string|array $paths Rutas dónde se buscarán las plantillas.
     * @param ExtensionInterface[] $extensions Extensiones que se cargarán.
     */
    public function __construct(
        string|array $paths = [],
        array $extensions = []
    ) {
        $this->paths = is_string($paths) ? [$paths] : $paths;
        $this->extensions = $extensions;
    }

    /**
     * Renderiza una plantilla Twig en HTML.
     *
     * @param string $template Plantilla Twig a renderizar.
     * @param array $data Datos que se pasarán a la plantilla Twig.
     * @return string Código HTML con el renderizado de la plantilla Twig.
     */
    public function render(string $template, array &$data = []): string
    {
        //$config = $data['options']['config']['html'] ?? [];

        // Resolver plantilla.
        $template = $this->resolveTemplate($template);

        // Renderizar la plantilla.
        return $this->getTwig()->render($template, $data);
    }

    /**
     * Entrega la instancia de Twig.
     *
     * Este método evita crearla en el constructor y se crea solo cuando
     * realmente se utiliza. Útil porque los workers son lazy services.
     *
     * @return Environment
     */
    private function getTwig(): Environment
    {
        if (!isset($this->twig)) {
            $this->twig = new Environment($this->getFilesystemLoader());
            foreach ($this->extensions as $extension) {
                assert($extension instanceof ExtensionInterface);
                $this->twig->addExtension($extension);
            }
        }

        return $this->twig;
    }

    /**
     * Entrega la instancia del cargador de plantillas desde el sistema de
     * archivos.
     *
     * @return FilesystemLoader
     */
    private function getFilesystemLoader(): FilesystemLoader
    {
        if (!isset($this->filesystemLoader)) {
            $this->filesystemLoader = new FilesystemLoader($this->paths);
        }

        return $this->filesystemLoader;
    }

    /**
     * Resuelve la plantilla que se está solicitando.
     *
     * Se encarga de:
     *
     *   - Agregar la extensión a la plantilla.
     *   - Agregar el directorio si se pasó una ruta absoluta de la plantilla.
     *
     * @param string $template
     * @return string
     */
    private function resolveTemplate(string $template): string
    {
        // Agregar extensión.
        if (!str_ends_with($template, '.html.twig')) {
            $template .= '.html.twig';
        }

        // Agregar el directorio si se pasó una ruta absoluta.
        if ($template[0] === '/') {
            $dir = dirname($template);
            $this->getFilesystemLoader()->addPath($dir);
            $template = basename($template);
        }

        // Entregar nombre de la plantilla.
        return $template;
    }
}
