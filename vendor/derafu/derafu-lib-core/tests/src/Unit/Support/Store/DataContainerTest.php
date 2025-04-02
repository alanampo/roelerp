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

use Closure;
use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\OptionsResolver\Options;

#[CoversClass(DataContainer::class)]
#[CoversClass(Selector::class)]
class DataContainerTest extends TestCase
{
    public static function provideTestCases(): array
    {
        $tests = require self::getFixturesPath(
            'support/store/data_container.php'
        );

        $testCases = [];

        foreach ($tests as $name => $test) {
            $testCases[$name] = [
                $test['data'],
                $test['schema'],
                $test['expected'],
            ];
        }

        return $testCases;
    }

    public function testDataContainerSchemaGetterAndSetter(): void
    {
        $schema = [
            'foo' => [
                'required' => true,
            ],
        ];

        $container = new DataContainer();
        $container->setSchema($schema);
        $this->assertSame($schema, $container->getSchema());
    }

    #[DataProvider('provideTestCases')]
    public function testDataContainerCase(
        array $data,
        array $schema,
        array|string $expected
    ): void {
        if (is_string($expected)) {
            $this->expectException($expected);
        }

        // Se deben resolver los normalizadores porque se pasan como string
        // desde el caso de prueba y deben ser closure para DataContainer.
        $this->resolveNormalizers($schema);

        $container = new DataContainer($data, $schema);

        foreach ($expected as $selector => $expectedValue) {
            $this->assertSame($expectedValue, $container->get($selector));
        }
    }

    private function resolveNormalizers(&$schema): void
    {
        foreach ($schema as $key => &$rules) {
            if (!empty($rules['normalizer'])) {
                $rules['normalizer'] = $this->resolveNormalizer(
                    $rules['normalizer']
                );
            }
            if (!empty($rules['schema'])) {
                $this->resolveNormalizers($rules['schema']);
            }
        }
    }

    private function resolveNormalizer($normalizer): Closure
    {
        switch ($normalizer) {
            case 'float':
                return fn (Options $options, $value) => (float) $value;
            default:
                return fn (Options $options, $value) => $value;
        }
    }
}
