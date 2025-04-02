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
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\Renderer\Strategy\PdfRendererStrategyInterface;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Estrategia de renderizado de archivos PDF (.pdf).
 */
class PdfRendererStrategy extends AbstractStrategy implements PdfRendererStrategyInterface
{
    /**
     * Constructor de la estrategia.
     *
     * @param HtmlRendererStrategyInterface $htmlRenderer
     */
    public function __construct(
        private HtmlRendererStrategyInterface $htmlRenderer
    ) {
    }

    /**
     * Renderiza un HTML en un documento PDF.
     *
     * El renderizado se realiza utilizando el HTML generado mediante una
     * estrategia de renderizado (dependencia de esta estrategia).
     *
     * @param string $template Plantilla HTML a renderizar.
     * @param array $data Datos que se pasarán a la plantilla al renderizarla.
     * @return string Datos del PDF con el renderizado de la plantilla HTML.
     */
    public function render(string $template, array &$data = []): string
    {
        //$config = $data['options']['config']['pdf'] ?? [];

        // Renderizar HTML usando el HtmlRenderer.
        $html = $this->htmlRenderer->render($template, $data);

        // Generar el PDF con mPDF.
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);

        // Obtener el contenido del PDF.
        $pdf = $mpdf->Output('', Destination::STRING_RETURN);

        // Entregar el contenido del PDF.
        return $pdf;
    }
}
