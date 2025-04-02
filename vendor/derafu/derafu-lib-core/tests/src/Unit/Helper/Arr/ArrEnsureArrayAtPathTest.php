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

namespace Derafu\Lib\Tests\Unit\Helper\Arr;

use Derafu\Lib\Core\Helper\Arr;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Arr::class)]
class ArrEnsureArrayAtPathTest extends TestCase
{
    public function testBasicConversion(): void
    {
        $data = [
            'Encabezado' => [
                'Detalle' => [
                    'Codigo' => '123',
                ],
            ],
        ];

        Arr::ensureArrayAtPath($data, 'Encabezado.Detalle');

        $this->assertIsArray($data['Encabezado']['Detalle']);
        $this->assertArrayHasKey(0, $data['Encabezado']['Detalle']);
        $this->assertSame(['Codigo' => '123'], $data['Encabezado']['Detalle'][0]);
    }

    public function testAlreadyArray(): void
    {
        $data = [
            'Encabezado' => [
                'Detalle' => [
                    ['Codigo' => '123'],
                ],
            ],
        ];

        Arr::ensureArrayAtPath($data, 'Encabezado.Detalle');

        $this->assertIsArray($data['Encabezado']['Detalle']);
        $this->assertArrayHasKey(0, $data['Encabezado']['Detalle']);
        $this->assertSame(['Codigo' => '123'], $data['Encabezado']['Detalle'][0]);
    }

    public function testNestedPath(): void
    {
        $data = [
            'Root' => [
                'Encabezado' => [
                    'Detalle' => [
                        'Codigo' => '456',
                    ],
                ],
            ],
        ];

        Arr::ensureArrayAtPath($data, 'Root.Encabezado.Detalle');

        $this->assertIsArray($data['Root']['Encabezado']['Detalle']);
        $this->assertArrayHasKey(0, $data['Root']['Encabezado']['Detalle']);
        $this->assertSame(['Codigo' => '456'], $data['Root']['Encabezado']['Detalle'][0]);
    }

    public function testEmptyPath(): void
    {
        $data = [];

        Arr::ensureArrayAtPath($data, 'Root.Encabezado.Detalle');

        $this->assertNull($data['Root']['Encabezado']['Detalle'] ?? null);
    }

    public function testEmptyArrayValue(): void
    {
        $data = [
            'Encabezado' => [
                'Detalle' => [],
            ],
        ];

        Arr::ensureArrayAtPath($data, 'Encabezado.Detalle');

        $this->assertIsArray($data['Encabezado']['Detalle']);
        $this->assertArrayNotHasKey(0, $data['Encabezado']['Detalle']);
    }
}
