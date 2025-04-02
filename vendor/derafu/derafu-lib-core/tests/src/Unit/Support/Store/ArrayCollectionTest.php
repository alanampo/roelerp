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

namespace Derafu\Lib\Tests\Unit\Support\Store;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Derafu\Lib\Tests\TestCase;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use Traversable;

#[CoversClass(DataContainer::class)]
class ArrayCollectionTest extends TestCase
{
    // Prueba básica para asegurar que un array simple se convierta
    // correctamente.
    public function testArrayCollectionInDataContainerWithArray(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $container = new DataContainer($data);

        $this->assertSame(
            $data,
            $container->all(),
            'Error al convertir desde un array.'
        );
    }

    // Prueba con un ArrayObject estándar.
    public function testArrayCollectionInDataContainerWithArrayObject(): void
    {
        $data = new ArrayObject(['key1' => 'value1', 'key2' => 'value2']);
        $container = new DataContainer($data);

        $this->assertSame(
            ['key1' => 'value1', 'key2' => 'value2'],
            $container->all(),
            'Error al convertir desde ArrayObject.'
        );
    }

    // Prueba con una clase personalizada que implemente ambas interfaces.
    public function testArrayCollectionInDataContainerWithArrayAccess(): void
    {
        $data = new MyArrayAccess();
        $container = new DataContainer($data);

        $this->assertSame(
            ['key1' => 'value1', 'key2' => 'value2'],
            $container->all(),
            'Error al convertir desde un ArrayAccess/Traversable.'
        );
    }
}

class MyArrayAccess implements ArrayAccess, IteratorAggregate
{
    private array $container = ['key1' => 'value1', 'key2' => 'value2'];

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->container[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->container);
    }
}
