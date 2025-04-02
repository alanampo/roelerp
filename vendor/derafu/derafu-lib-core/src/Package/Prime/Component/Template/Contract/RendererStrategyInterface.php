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

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Contract;

use Derafu\Lib\Core\Foundation\Contract\StrategyInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Exception\TemplateException;

/**
 * Interfaz base de las estrategias de renderizado mediante plantillas.
 */
interface RendererStrategyInterface extends StrategyInterface
{
    /**
     * Realiza el renderizado de una plantilla.
     *
     * @param string $template Plantilla a renderizar.
     * @param array $data Datos que se pararán a la plantilla al renderizarla.
     * @return string Datos del renderizado.
     * @throws TemplateException
     */
    public function render(string $template, array &$data = []): string;
}
