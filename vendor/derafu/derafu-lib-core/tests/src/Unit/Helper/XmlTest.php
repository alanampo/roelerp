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

use Derafu\Lib\Core\Helper\Xml;
use Derafu\Lib\Tests\TestCase;
use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Xml::class)]
class XmlTest extends TestCase
{
    public function testXmlXpath(): void
    {
        $xmlContent = <<<XML
        <root>
            <element1>Value 1</element1>
            <element2>Value 2</element2>
        </root>
        XML;

        $doc = new DOMDocument();
        $doc->loadXml($xmlContent);

        $result = Xml::xpath($doc, '//element1');

        $this->assertInstanceOf(\DOMNodeList::class, $result);
        $this->assertSame(1, $result->length);
        $this->assertSame('Value 1', $result->item(0)->textContent);
    }

    public function testXmlFixEntities(): void
    {
        $xml = '<root>He said "Hello" & ' . "'Goodbye'</root>";
        //$expectedXml = '<root>He said &quot;Hello&quot; &amp; &apos;Goodbye&apos;</root>';
        $expectedXml = '<root>He said &quot;Hello&quot; & &apos;Goodbye&apos;</root>';

        $result = Xml::fixEntities($xml);

        $this->assertSame($expectedXml, $result);
    }

    public function testXmlXpathInvalidExpression(): void
    {
        $xmlContent = <<<XML
        <root>
            <element1>Value 1</element1>
            <element2>Value 2</element2>
        </root>
        XML;

        $doc = new DOMDocument();
        $doc->loadXml($xmlContent);

        $this->expectException(InvalidArgumentException::class);
        $result = Xml::xpath($doc, '//*invalid_xpath');
    }

    public function testXmlFixEntitiesMalformedXml(): void
    {
        // XML sin el cierre el tag `root`.
        $malformedXml = '<root>He said "Hello" & <child>Goodbye</child>';

        $result = Xml::fixEntities($malformedXml);

        // Se espera que el XML malformado se mantenga igual. Y que la
        // corrección de entidades sea aplicada al resto del string del XML.
        //$expectedXml = '<root>He said &quot;Hello&quot; &amp; <child>Goodbye</child>';
        $expectedXml = '<root>He said &quot;Hello&quot; & <child>Goodbye</child>';
        $this->assertSame($expectedXml, $result);
    }

    public function testXmlFixEntitiesEmptyString(): void
    {
        $emptyXml = '';

        $result = Xml::fixEntities($emptyXml);

        // Un string XML vacio debe entregar un resultado vacio.
        $this->assertSame('', $result);
    }

    public function testXmlSanitizeNoSpecialCharacters(): void
    {
        $input = 'Hello World';
        $expected = 'Hello World';

        $result = Xml::sanitize($input);

        $this->assertSame($expected, $result);
    }

    public function testXmlSanitizeWithAmpersand(): void
    {
        $input = 'Tom & Jerry';
        $expected = 'Tom &amp; Jerry';

        $result = Xml::sanitize($input);

        $this->assertSame($expected, $result);
    }

    public function testXmlSanitizeWithQuotes(): void
    {
        $input = 'She said "Hello"';
        //$expected = 'She said &quot;Hello&quot;';
        $expected = 'She said "Hello"';

        $result = Xml::sanitize($input);

        $this->assertSame($expected, $result);
    }

    public function testXmlSanitizeWithApostrophe(): void
    {
        $input = "It's a beautiful day";
        //$expected = 'It&apos;s a beautiful day';
        $expected = 'It\'s a beautiful day';

        $result = Xml::sanitize($input);

        $this->assertSame($expected, $result);
    }

    public function testXmlSanitizeWithLessThanAndGreaterThan(): void
    {
        $input = '5 < 10 > 2';
        //$expected = '5 &lt; 10 &gt; 2';
        $expected = '5 < 10 > 2';

        $result = Xml::sanitize($input);

        $this->assertSame($expected, $result);
    }

    public function testXmlSanitizeWithNumericValue(): void
    {
        $input = '12345';
        $expected = '12345';

        $result = Xml::sanitize($input);

        $this->assertSame($expected, $result);
    }

    public function testXmlSanitizeEmptyString(): void
    {
        $input = '';
        $expected = '';

        $result = Xml::sanitize($input);

        $this->assertSame($expected, $result);
    }
}
