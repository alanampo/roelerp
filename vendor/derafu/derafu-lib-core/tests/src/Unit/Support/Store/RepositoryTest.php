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
use Derafu\Lib\Core\Helper\Factory;
use Derafu\Lib\Core\Support\Store\Repository;
use Derafu\Lib\Tests\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Repository::class)]
#[CoversClass(Arr::class)]
#[CoversClass(Factory::class)]
class RepositoryTest extends TestCase
{
    public static function provideTestCases(): array
    {
        $tests = require self::getFixturesPath(
            'support/store/repository.php'
        );

        $testCases = [];

        foreach ($tests as $name => $test) {
            foreach ($test['cases'] as $caseName => $case) {
                $testCases[$name . ':' . $caseName] = [
                    $test['data'],
                    $case,
                ];
            }
        }

        return $testCases;
    }

    #[DataProvider('provideTestCases')]
    public function testRepositoryCase(array $data, array $case): void
    {
        $repository = new Repository($data, idAttribute: 'id');

        $method = $case['method'];
        $args = $case['args'];
        $expected = $case['expected'];

        $result = match($method) {
            'find' => $repository->find(...$args),
            'findAll' => $repository->findAll(),
            'findBy' => $repository->findBy(...$args),
            'findOneBy' => $repository->findOneBy(...$args),
            'count' => $repository->count(...$args),
            'findByCriteria' => $repository->findByCriteria(...$args),
            default => throw new InvalidArgumentException(sprintf(
                'Método %s no soportado.',
                $method
            ))
        };

        // Asegurar que el resultado sea arreglo, y si tiene objetos dentro que
        // también sean arreglos.
        $result = json_decode(json_encode($result), true);

        $this->assertSame($expected, $result);
    }
}
