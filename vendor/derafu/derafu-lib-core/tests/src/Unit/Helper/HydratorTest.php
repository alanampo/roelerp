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

use Derafu\Lib\Core\Helper\Hydrator;
use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Hydrator::class)]
#[CoversClass(Str::class)]
class HydratorTest extends TestCase
{
    public function testHydrateWithProperties(): void
    {
        $data = ['name' => 'Alice', 'age' => 28];
        $instance = new HydratableClass();

        Hydrator::hydrate($instance, $data);

        $this->assertSame('Alice', $instance->name);
        $this->assertSame(28, $instance->age);
    }

    public function testHydrateWithSetAttributeMethod(): void
    {
        $data = ['name' => 'Bob', 'age' => 35];
        $instance = new HydratorHelperFallbackClass();

        Hydrator::hydrate($instance, $data);

        $this->assertSame('Bob', $instance->getAttribute('name'));
        $this->assertSame(35, $instance->getAttribute('age'));
    }

    public function testHydrateWithSetterMethods(): void
    {
        $data = ['name' => 'Charlie', 'age' => 40];
        $instance = new HydratorHelperSetterClass();

        Hydrator::hydrate($instance, $data);

        $this->assertSame('Charlie', $instance->getName());
        $this->assertSame(40, $instance->getAge());
    }
}

class HydratableClass
{
    public string $name;

    public int $age;
}

class HydratorHelperFallbackClass
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

class HydratorHelperSetterClass
{
    private string $name;

    private int $age;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}
