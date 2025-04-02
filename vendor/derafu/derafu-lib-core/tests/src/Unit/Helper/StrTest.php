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

use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Str::class)]
class StrTest extends TestCase
{
    public function testStrUtf2Iso(): void
    {
        $utf8String = 'áéíóúñ';
        $expectedIsoString = mb_convert_encoding(
            $utf8String,
            'ISO-8859-1',
            'UTF-8'
        );

        $result = Str::utf8decode($utf8String);

        $this->assertSame($expectedIsoString, $result);
    }

    public function testStrIso2Utf(): void
    {
        $expectedUtf8String = 'áéíóúñ';
        $isoString = mb_convert_encoding(
            $expectedUtf8String,
            'ISO-8859-1',
            'UTF-8'
        );

        $result = Str::utf8encode($isoString);

        $this->assertSame($expectedUtf8String, $result);
    }

    public function testStrUtf2IsoInvalidEncoding(): void
    {
        // Secuencia UTF-8 inválida.
        $invalidUtf8String = "\x80\x81\x82";

        $result = Str::utf8decode($invalidUtf8String);

        // El resultado debe ser el string origial pues no puede ser convertido.
        $this->assertSame($invalidUtf8String, $result);
    }

    public function testStrIso2UtfInvalidEncoding(): void
    {
        // Secuencia ISO-8859-1 inválida.
        $invalidIsoString = "\xFF\xFE\xFD";

        // A pesar de ser un string con secuencia inválida, la función
        // mb_convert_encoding() (usada en Str::utf8decode()) hará un "mejor"
        // esfuerzo y entregará el siguiente string.
        $expectedString = 'ÿþý';

        $result = Str::utf8encode($invalidIsoString);

        $this->assertSame($expectedString, $result);
    }
}
