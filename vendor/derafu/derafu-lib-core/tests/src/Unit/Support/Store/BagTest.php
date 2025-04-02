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

use Derafu\Lib\Core\Helper\Arr;
use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Core\Support\Store\Bag;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Bag::class)]
#[CoversClass(Arr::class)]
#[CoversClass(Selector::class)]
class BagTest extends TestCase
{
    private Bag $bag;

    protected function setUp(): void
    {
        $this->bag = new Bag();
    }

    public function testConstructorWithInitialData(): void
    {
        $data = ['foo' => 'bar'];
        $bag = new Bag($data);

        $this->assertSame($data, $bag->all());
    }

    public function testGetAndSet(): void
    {
        $this->bag->set('foo', 'bar');
        $this->assertSame('bar', $this->bag->get('foo'));
        $this->assertSame('default', $this->bag->get('nonexistent', 'default'));
    }

    public function testGetWithNestedKey(): void
    {
        $this->bag->set('foo.bar', 'baz');
        $this->assertSame('baz', $this->bag->get('foo.bar'));
    }

    public function testHas(): void
    {
        $this->bag->set('foo', 'bar');

        $this->assertTrue($this->bag->has('foo'));
        $this->assertFalse($this->bag->has('nonexistent'));
    }

    public function testRemove(): void
    {
        $this->bag->set('foo', 'bar');
        $this->bag->remove('foo');

        $this->assertFalse($this->bag->has('foo'));
    }

    public function testReplace(): void
    {
        $this->bag->set('foo', 'bar');
        $this->bag->replace(['baz' => 'qux']);

        $this->assertSame(['baz' => 'qux'], $this->bag->all());
        $this->assertFalse($this->bag->has('foo'));
    }

    public function testMerge(): void
    {
        $this->bag->set('foo', 'bar');
        $this->bag->merge(['baz' => 'qux']);

        $expected = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];
        $this->assertSame($expected, $this->bag->all());
    }

    public function testClear(): void
    {
        $this->bag->set('foo', 'bar');
        $this->bag->clear();

        $this->assertSame([], $this->bag->all());
    }

    public function testMethodChaining(): void
    {
        $result = $this->bag
            ->set('foo', 'bar')
            ->set('baz', 'qux')
            ->remove('foo')
            ->merge(['another' => 'value'])
        ;

        $this->assertInstanceOf(Bag::class, $result);
    }
}
