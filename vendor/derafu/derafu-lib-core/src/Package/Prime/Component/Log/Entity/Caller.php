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

namespace Derafu\Lib\Core\Package\Prime\Component\Log\Entity;

/**
 * Clase que representa quién llamó al log.
 */
class Caller
{
    /**
     * Archivo donde se llamó al log.
     *
     * @var string
     */
    public string $file;

    /**
     * Línea del archivo donde se llamó al log.
     *
     * @var int
     */
    public int $line;

    /**
     * Método que llamó al log.
     *
     * @var string
     */
    public string $function;

    /**
     * Clase del método que llamó al log.
     *
     * @var string
     */
    public string $class;

    /**
     * Tipo de llamada (estática o de objeto instanciado).
     *
     * @var string
     */
    public string $type;

    /**
     * Constructor de la clase.
     *
     * @param string $file
     * @param integer $line
     * @param string $function
     * @param string $class
     * @param string $type
     */
    public function __construct(
        string $file,
        int $line,
        string $function,
        string $class,
        string $type
    ) {
        $this->file = $file;
        $this->line = $line;
        $this->function = $function;
        $this->class = $class;
        $this->type = $type;
    }

    /**
     * Método mágico para obtener quién llamó al log como string a partir de los
     * atributos de la instancia de esta clase.
     *
     * @return string Quién llamó al log formateado como string.
     */
    public function __toString(): string
    {
        return sprintf(
            'in %s on line %d, called by %s%s%s()',
            $this->file,
            $this->line,
            $this->class,
            $this->type,
            $this->function
        );
    }
}
