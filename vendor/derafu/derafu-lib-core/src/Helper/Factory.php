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

namespace Derafu\Lib\Core\Helper;

use ReflectionClass;
use stdClass;

/**
 * Fábrica básica para creación de instancias de clases a partir de los datos y
 * el nombre de la clase.
 */
class Factory
{
    /**
     * Crea una instancia de una clase.
     *
     * @param array $data Datos con los atributos que se deben asignar.
     * @param string|null $class Si la clase no se especifica se usará stdClass.
     * @return object Instancia de la clase solicitada con sus atributos.
     */
    public static function create(array $data, ?string $class = null): object
    {
        $class = $class ?? stdClass::class;

        // Si no se indicó una clase, retorna un stdClass.
        if ($class === stdClass::class) {
            return (object) $data;
        }

        // Crear la instancia sin constructor.
        $reflectionClass = new ReflectionClass($class);
        $instance = $reflectionClass->newInstanceWithoutConstructor();

        // Hidratar la instancia con los datos.
        return Hydrator::hydrate($instance, $data);
    }
}
