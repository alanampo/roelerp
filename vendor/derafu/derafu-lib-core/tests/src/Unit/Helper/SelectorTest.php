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

use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Selector::class)]
class SelectorTest extends TestCase
{
    public static function provideGetSelector(): array
    {
        $test = require self::getFixturesPath('helper/selector_get.php');

        $getSelectors = [];

        foreach ($test['cases'] as $selector => $expected) {
            $getSelectors[$selector] = [
                $test['data'],
                $selector,
                $expected,
            ];
        }

        return $getSelectors;
    }

    public static function provideSetSelector(): array
    {
        $tests = require self::getFixturesPath('helper/selector_set.php');

        $setSelectors = [];

        foreach ($tests as $test) {
            $setSelectors[$test['name']] = [
                $test['data'],
                $test['cases'],
            ];
        }

        return $setSelectors;
    }

    #[DataProvider('provideGetSelector')]
    public function testSelectorGet($data, $selector, $expected): void
    {
        $result = Selector::get($data, $selector);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('provideSetSelector')]
    public function testSelectorSet($data, $cases): void
    {
        foreach ($cases as $case) {
            Selector::set($data, $case['selector'], $case['value']);
            $this->assertSame($case['expected'], $data);
        }
    }
}
