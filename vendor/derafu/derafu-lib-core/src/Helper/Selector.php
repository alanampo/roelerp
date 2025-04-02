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
use stdClass;

/**
 * Clase para acceder a los datos de un arreglo de manera simplificada.
 */
class Selector
{
    /**
     * Asigna un valor a un selector.
     *
     * @param array $data Conjunto de datos.
     * @param string $selector Selector donde se almacenará el valor.
     * @param mixed $value Valor que se desea almacenar.
     */
    public static function set(
        array &$data,
        string $selector,
        mixed $value
    ): void {
        self::resolveSelector($data, $selector, $value, true);
    }

    /**
     * Obtiene un valor almacenado a un selector.
     *
     * @param array $data Conjunto de datos.
     * @param string $selector Selector del valor que se desea obtener.
     * @param mixed $default Valor por defecto si la llave no existe.
     * @return mixed Valor almacenado o valor por defecto.
     */
    public static function get(
        array $data,
        string $selector,
        mixed $default = null
    ): mixed {
        $value = self::resolveSelector($data, $selector);

        return $value !== null ? $value : $default;
    }

    /**
     * Verifica si existe un valor para un selector.
     *
     * La validación será positiva siempre que existe el índice del selector y
     * que no sea `null` el valor que tenga dicho índice.
     *
     * @param array $data Conjunto de datos.
     * @param string $selector Selector que se desea verificar.
     * @return bool True si la llave existe, false en caso contrario.
     */
    public static function has(array $data, string $selector): bool
    {
        return self::get($data, $selector) !== null;
    }

    /**
     * Elimina el índice de los datos del selector.
     *
     * Esto elimina el índice del selector, no lo asigna a `null` pues en
     * estricto rigor seguiría existiendo el índice en los datos.
     *
     * @param array $data Conjunto de datos.
     * @param string $selector Selector que se desea eliminar.
     */
    public static function clear(array &$data, string $selector): void
    {
        // TODO: se debe hacer de forma recursiva resolviendo el selector.
        unset($data[$selector]);
    }

    /**
     * Resuelve un selector y obtiene/establece el valor.
     *
     * @param array $data Datos donde buscar/escribir.
     * @param string $selector Selector a resolver.
     * @param mixed $value Valor a escribir (null si es lectura).
     * @param bool $isWrite True si es escritura, false si es lectura.
     * @return mixed Valor leído o null si es escritura.
     */
    private static function resolveSelector(
        array &$data,
        string $selector,
        mixed $value = null,
        bool $isWrite = false
    ): mixed {
        $parsed = self::parseSelector($selector);

        switch ($parsed->type) {
            case 'simple':
                return self::processSimple($data, $parsed, $value, $isWrite);
            case 'array':
                return self::processArray($data, $parsed, $value, $isWrite);
        }

        throw new LogicException(sprintf(
            'No existe tipo de selector %s para acceder a los datos.',
            $parsed->type
        ));
    }

    /**
     * Parser del selector.
     *
     * Convierte el string del selector en una estructura que podemos procesar.
     *
     * @param string $selector Selector a parsear.
     * @return stdClass Estructura del selector.
     */
    private static function parseSelector(string $selector): stdClass
    {
        // Detectar selector de array.
        if (preg_match('/^(\w+)\[(\d+)\]$/', $selector, $matches)) {
            return (object) [
                'type' => 'array',
                'key' => $matches[1],
                'index' => (int) $matches[2],
            ];
        }

        // Si no es uno selector de los previos, tratarlo como selector simple.
        $parts = explode('.', $selector);
        return (object) [
            'type' => 'simple',
            'parts' => $parts,
        ];
    }

    /**
     * Procesar usando el tipo de selector: "simple".
     */
    private static function processSimple(
        array &$data,
        stdClass $selector,
        mixed $value,
        bool $isWrite
    ): mixed {
        $current = &$data;
        $parts = $selector->parts;
        $last = count($parts) - 1;

        foreach ($parts as $i => $part) {
            if ($isWrite && $i === $last) {
                $current[$part] = $value;
                return null;
            }
            if (!isset($current[$part])) {
                if ($isWrite) {
                    $current[$part] = [];
                } else {
                    return null;
                }
            }
            $current = &$current[$part];
        }

        return $current;
    }

    /**
     * Procesar usando el tipo de selector: "array".
     */
    private static function processArray(
        array &$data,
        stdClass $selector,
        mixed $value,
        bool $isWrite
    ): mixed {
        if (!isset($data[$selector->key]) || !is_array($data[$selector->key])) {
            if ($isWrite) {
                $data[$selector->key] = [];
            } else {
                return null;
            }
        }

        if ($isWrite) {
            $data[$selector->key][$selector->index] = $value;
            return null;
        }

        return $data[$selector->key][$selector->index] ?? null;
    }
}
