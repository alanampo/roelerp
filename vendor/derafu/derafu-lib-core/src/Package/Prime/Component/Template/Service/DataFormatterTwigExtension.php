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

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Service;

use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\DataFormatterInterface;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;

class DataFormatterTwigExtension extends AbstractExtension
{
    /**
     * Codificación de caracteres para los renderizados devueltos por las
     * funciones de la extensión.
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * Constructor de la extensión.
     *
     * Se inyecta la dependencia para formatear los valores.
     *
     * @param DataFormatterInterface $formatter
     */
    public function __construct(
        private DataFormatterInterface $formatter
    ) {
    }

    /**
     * Entrega los filtros disponibles en esta extensión.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_as', [$this, 'format_as']),
            new TwigFilter('to_string', [$this, 'to_string']),
        ];
    }

    /**
     * Formatea un valor de acuerdo a un ID que dice cómo formatearlo.
     *
     * @param mixed $value
     * @param string $id
     * @return Markup
     */
    public function format_as(mixed $value, string $id): Markup
    {
        $html = $this->formatter->format($id, $value);

        return new Markup($html, $this->charset);
    }

    /**
     * Convierte un valor a string.
     *
     * @param mixed $value
     * @return Markup
     */
    public function to_string(mixed $value): Markup
    {
        $html = $this->formatter->format('string', $value);

        return new Markup($html, $this->charset);
    }
}
