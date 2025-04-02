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

namespace Derafu\Lib\Core\Package\Prime\Component\Template;

use Derafu\Lib\Core\Foundation\Abstract\AbstractComponent;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\RendererWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\TemplateComponentInterface;

/**
 * Servicio para trabajar con plantillas para renderizado.
 */
class TemplateComponent extends AbstractComponent implements TemplateComponentInterface
{
    public function __construct(
        private RendererWorkerInterface $renderer
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkers(): array
    {
        return [
            'renderer' => $this->renderer,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRendererWorker(): RendererWorkerInterface
    {
        return $this->renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $template, array $data = []): string
    {
        return $this->renderer->render($template, $data);
    }
}
