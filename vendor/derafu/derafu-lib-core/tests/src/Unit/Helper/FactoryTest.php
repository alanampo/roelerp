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

namespace Derafu\Lib\Tests\Unit\Helper;

use Derafu\Lib\Core\Helper\Factory;
use Derafu\Lib\Core\Helper\Hydrator;
use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(Factory::class)]
#[CoversClass(Hydrator::class)]
#[CoversClass(Str::class)]
class FactoryTest extends TestCase
{
    public function testCreateStdClass(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $instance = Factory::create($data);

        $this->assertInstanceOf(stdClass::class, $instance);
        $this->assertSame('John', $instance->name);
        $this->assertSame(30, $instance->age);
    }

    public function testCreateFactoryHelperCustomClass(): void
    {
        $data = ['name' => 'Jane', 'age' => 25];

        $instance = Factory::create($data, FactoryHelperCustomClass::class);

        $this->assertInstanceOf(FactoryHelperCustomClass::class, $instance);
        $this->assertSame('Jane', $instance->name);
        $this->assertSame(25, $instance->age);
    }

    public function testSetAttributeFallback(): void
    {
        $data = ['name' => 'John', 'age' => 30];

        $instance = Factory::create($data, FactoryHelperFallbackClass::class);

        $this->assertInstanceOf(FactoryHelperFallbackClass::class, $instance);
        $this->assertSame('John', $instance->getAttribute('name'));
        $this->assertSame(30, $instance->getAttribute('age'));
    }
}

class FactoryHelperCustomClass
{
    public string $name;

    public int $age;
}

class FactoryHelperFallbackClass
{
    private array $attributes = [];

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }
}
