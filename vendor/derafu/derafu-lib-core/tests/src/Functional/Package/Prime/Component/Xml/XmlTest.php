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

namespace Derafu\Lib\Tests\Functional\Package\Prime\Component\Xml;

use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Core\Helper\Xml as XmlUtil;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Entity\Xml;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Exception\XmlException;
use Derafu\Lib\Core\Support\Xml\XPathQuery;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Xml::class)]
#[CoversClass(XmlException::class)]
#[CoversClass(Str::class)]
#[CoversClass(XmlUtil::class)]
#[CoversClass(XPathQuery::class)]
class XmlTest extends TestCase
{
    /**
     * Verifica que el documento XML se carga correctamente.
     */
    public function testXmlLoadXml(): void
    {
        $xmlContent = <<<XML
        <root>
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $result = $doc->loadXml($xmlContent);

        $this->assertTrue($result);
        $this->assertSame('root', $doc->documentElement->tagName);
    }

    /**
     * Verifica que se obtenga correctamente el nombre del tag raíz del
     * documento XML.
     */
    public function testXmlGetName(): void
    {
        $xmlContent = <<<XML
        <root>
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $this->assertSame('root', $doc->getName());
    }

    /**
     * Verifica la obtención del espacio de nombres del documento XML cuando
     * existe.
     */
    public function testXmlGetNamespace(): void
    {
        $xmlContent = <<<XML
        <root xmlns="http://example.com">
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $this->assertSame('http://example.com', $doc->getNamespace());
    }

    /**
     * Verifica la obtención del espacio de nombres del documento XML cuando
     * no existe.
     */
    public function testXmlGetNamespaceNull(): void
    {
        $xmlContent = <<<XML
        <root>
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $this->assertNull($doc->getNamespace());
    }

    /**
     * Verifica la obtención del schema asociado al documento XML cuando
     * existe.
     */
    public function testXmlGetSchema(): void
    {
        $xmlContent = <<<XML
        <root xsi:schemaLocation="http://example.com schema.xsd"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $this->assertSame('schema.xsd', $doc->getSchema());
    }

    /**
     * Verifica la obtención del schema asociado al documento XML cuando no
     * existe.
     */
    public function testXmlGetSchemaNull(): void
    {
        $xmlContent = <<<XML
        <root>
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $this->assertNull($doc->getSchema());
    }

    /**
     * Verifica que el método saveXml() genera correctamente el XML.
     */
    public function testXmlSaveXml(): void
    {
        $xmlContent = <<<XML
        <root>
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $expectedXml = <<<XML
        <?xml version="1.0" encoding="ISO-8859-1"?>
        <root>
            <element>Value</element>
        </root>

        XML;

        $this->assertXmlStringEqualsXmlString($expectedXml, $doc->saveXml());
    }

    /**
     * Verifica que el método C14N() funcione correctamente, generando la
     * versión canónica del XML.
     */
    public function testXmlC14N(): void
    {
        $xmlContent = <<<XML
        <root>
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $canonicalXml = $doc->C14N();

        $this->assertNotEmpty($canonicalXml);
        $expectedXml = "<root>\n    <element>Value</element>\n</root>";
        $this->assertStringContainsString($expectedXml, $canonicalXml);
    }

    /**
     * Verifica que el método C14NWithIsoEncoding() aplane correctamente el
     * documento XML.
     */
    public function testXmlC14NWithIsoEncoding(): void
    {
        $xmlContent = <<<XML
        <root>
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $flattenedXml = $doc->C14NWithIsoEncodingFlattened();

        $expectedXml = '<root><element>Value</element></root>';
        $this->assertSame($expectedXml, $flattenedXml);
    }

    /**
     * Verifica que C14NWithIsoEncoding() funcione correctamente cuando se
     * proporciona una expresión XPath.
     */
    public function testXmlC14NWithIsoEncodingWithXPath(): void
    {
        $xmlContent = <<<XML
        <root>
            <element>Value</element>
            <element2>Other Value</element2>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $flattenedXml = $doc->C14NWithIsoEncoding('//element2');

        $expectedXml = '<element2>Other Value</element2>';
        $this->assertSame($expectedXml, $flattenedXml);
    }

    /**
     * Verifica que C14NWithIsoEncoding() retorne false cuando la expresión
     * XPath no coincide con ningún nodo.
     */
    public function testXmlC14NWithIsoEncodingXPathNotFound(): void
    {
        $this->expectException(XmlException::class);

        $xmlContent = <<<XML
        <root>
            <element>Value</element>
        </root>
        XML;

        $doc = new Xml();
        $doc->loadXml($xmlContent);

        $xml = $doc->C14NWithIsoEncoding('//nonexistent');
    }
}
