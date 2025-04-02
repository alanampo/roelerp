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

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Worker;

use Derafu\Lib\Core\Foundation\Abstract\AbstractWorker;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\RendererStrategyInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\RendererWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Exception\TemplateException;
use Throwable;

/**
 * Worker de renderización de plantillas.
 *
 * Permite renderizar plantillas utilizando estrategias según el formato.
 * Por ejemplo puede renderizar HTML mediante Twig o un PDF usando HTML.
 */
class RendererWorker extends AbstractWorker implements RendererWorkerInterface
{
    /**
     * Formato por defecto que se debe utilizar.
     *
     * @var string
     */
    private string $defaultFormat = 'html';

    /**
     * {@inheritDoc}
     */
    public function render(string $template, array $data = []): string
    {
        // Formato en el que se renderizará la plantilla.
        $format = $data['options']['format'] ?? $this->defaultFormat;

        // Buscar estrategia según el formato.
        $strategy = $this->getStrategy($format);
        assert($strategy instanceof RendererStrategyInterface);

        // Renderizar utilizando la estrategia.
        try {
            return $strategy->render($template, $data);
        } catch (Throwable $e) {
            throw new TemplateException(sprintf(
                'Ocurrió un problema al renderizar la plantilla %s: %s',
                $template,
                $e->getMessage()
            ));
        }
    }
}
