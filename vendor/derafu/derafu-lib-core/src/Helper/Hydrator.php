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

use LogicException;
use ReflectionClass;

/**
 * Hidratador básico para actualización de atributos en instancias de clases.
 */
class Hydrator
{
    /**
     * Hidrata un objeto con los datos proporcionados.
     *
     * @param object $instance Instancia de la clase que se hidratará.
     * @param array $data Datos con los atributos que se deben asignar.
     * @return object Instancia hidratada.
     */
    public static function hydrate(object $instance, array $data): object
    {
        $reflectionClass = new ReflectionClass($instance);

        foreach ($data as $attribute => $value) {
            $assigned = false;

            // Si la propiedad existe, se configura directamente.
            if ($reflectionClass->hasProperty($attribute)) {
                $property = $reflectionClass->getProperty($attribute);
                $property->setAccessible(true);
                $property->setValue($instance, $value);
                $assigned = true;
            }
            // Si no existe, intentar usar algún método genérico.
            else {
                // Probar primero con setAttribute().
                if (method_exists($instance, 'setAttribute')) {
                    $instance->setAttribute($attribute, $value);
                    $assigned = true;
                }
                // Probar con setXyz() y setXyzAttribute().
                else {
                    // Tratar de asignar
                    $methods = ['set' . Str::studly($attribute)];
                    $methods[] = $methods[0] . 'Attribute';
                    foreach ($methods as $method) {
                        if (method_exists($instance, $method)) {
                            $instance->$method($value);
                            $assigned = true;
                        }
                    }
                }
            }

            // Si no fue posible asignar el atributo error.
            if (!$assigned) {
                throw new LogicException(sprintf(
                    'No se puede asignar el atributo %s porque la clase %s no define la propiedad ni un método setAttribute().',
                    $attribute,
                    get_class($instance)
                ));
            }
        }

        return $instance;
    }
}
