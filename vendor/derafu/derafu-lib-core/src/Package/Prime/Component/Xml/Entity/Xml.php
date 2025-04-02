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

namespace Derafu\Lib\Core\Package\Prime\Component\Xml\Entity;

use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Core\Helper\Xml as XmlUtil;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Exception\XmlException;
use Derafu\Lib\Core\Support\Xml\XPathQuery;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;

/**
 * Clase que representa un documento XML.
 */
class Xml extends DOMDocument implements XmlInterface
{
    /**
     * Instancia para facilitar el manejo de XML usando XPath.
     *
     * @var XPathQuery
     */
    private XPathQuery $xPathQuery;

    /**
     * Representación del XML como arreglo.
     *
     * @var array
     */
    private array $array;

    /**
     * Constructor del documento XML.
     *
     * @param string $version Versión del documento XML.
     * @param string $encoding Codificación del documento XML.
     */
    public function __construct(
        string $version = '1.0',
        string $encoding = 'ISO-8859-1'
    ) {
        parent::__construct($version, $encoding);

        $this->formatOutput = true;
        $this->preserveWhiteSpace = true;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->documentElement->tagName;
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespace(): ?string
    {
        $namespace = $this->documentElement->getAttribute('xmlns');

        return $namespace !== '' ? $namespace : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema(): ?string
    {
        $schemaLocation = $this->documentElement->getAttribute(
            'xsi:schemaLocation'
        );

        if (!$schemaLocation || !str_contains($schemaLocation, ' ')) {
            return null;
        }

        return explode(' ', $schemaLocation)[1];
    }

    /**
     * {@inheritDoc}
     */
    public function loadXml(string $source, int $options = 0): bool
    {
        // Si no hay un string XML en el origen entonces se lanza excepción.
        if (empty($source)) {
            throw new XmlException(
                'El contenido del XML que se desea cargar está vacío.'
            );
        }

        // Convertir el XML si es necesario.
        preg_match(
            '/<\?xml\s+version="([^"]+)"\s+encoding="([^"]+)"\?>/',
            $source,
            $matches
        );
        //$version = $matches[1] ?? $this->xmlVersion;
        $encoding = strtoupper($matches[2] ?? $this->encoding);
        if ($encoding === 'UTF-8') {
            $source = Str::utf8decode($source);
            $source = str_replace(
                ' encoding="UTF-8"?>',
                ' encoding="ISO-8859-1"?>',
                $source
            );
        }

        // Si el XML que se cargará no inicia con el TAG que abre XML se agrega.
        // Esto es 100% necesario pues si viene en codificación diferente a
        // UTF-8 (lo más normal) y no viene este tag abriendo el XML al cargar
        // reclamará que falta la codificación.
        if (!str_starts_with($source, '<?xml')) {
            $source = '<?xml version="1.0" encoding="' . $encoding . '"?>'
                . "\n" . $source
            ;
        }

        // Obtener estado actual de libxml y cambiarlo antes de cargar el XML
        // para obtener los errores en una variable si falla algo.
        $useInternalErrors = libxml_use_internal_errors(true);

        // Cargar el XML.
        $status = parent::loadXml($source, $options);

        // Obtener errores, limpiarlos y restaurar estado de errores de libxml.
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);

        if (!$status) {
            throw new XmlException('Error al cargar el XML.', $errors);
        }

        // Retornar estado de la carga del XML.
        // Sólo retornará `true`, pues si falla lanza excepción.
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function saveXml(?DOMNode $node = null, int $options = 0): string
    {
        $xml = parent::saveXml($node, $options);

        return XmlUtil::fixEntities($xml);
    }

    /**
     * {@inheritDoc}
     */
    public function getXml(): string
    {
        $xml = $this->saveXml();
        $xml = preg_replace(
            '/<\?xml\s+version="1\.0"\s+encoding="[^"]+"\s*\?>/i',
            '',
            $xml
        );

        return trim($xml);
    }

    /**
     * {@inheritDoc}
     */
    public function C14NWithIsoEncoding(?string $xpath = null): string
    {
        // Si se proporciona XPath, filtrar los nodos.
        if ($xpath) {
            $node = $this->getNodes($xpath)->item(0);
            if (!$node) {
                throw new XmlException(sprintf(
                    'No fue posible obtener el nodo con el XPath %s.',
                    $xpath
                ));
            }
            $xml = $node->C14N();
        }
        // Usar C14N() para todo el documento si no se especifica XPath.
        else {
            $xml = $this->C14N();
        }

        // Corregir XML entities.
        $xml = XmlUtil::fixEntities($xml);

        // Convertir el XML aplanado de UTF-8 a ISO-8859-1.
        // Requerido porque C14N() siempre entrega los datos en UTF-8.
        $xml = Str::utf8decode($xml);

        // Entregar el XML canonicalizado.
        return $xml;
    }

    /**
     * {@inheritDoc}
     */
    public function C14NWithIsoEncodingFlattened(?string $xpath = null): string
    {
        // Obtener XML canonicalizado y codificado en ISO8859-1.
        $xml = $this->C14NWithIsoEncoding($xpath);

        // Eliminar los espacios entre tags.
        $xml = preg_replace("/>\s+</", '><', $xml);

        // Entregar el XML canonicalizado y aplanado.
        return $xml;
    }

    /**
     * {@inheritDoc}
     */
    public function getSignatureNodeXml(): ?string
    {
        $tag = $this->documentElement->tagName;
        $xpath = '/' . $tag . '/Signature';
        $signatureElement = $this->getNodes($xpath)->item(0);

        return $signatureElement?->C14N();
    }

    /**
     * {@inheritDoc}
     */
    public function query(string $query, array $params = []): string|array|null
    {
        if (!isset($this->xPathQuery)) {
            $this->xPathQuery = new XPathQuery($this);
        }

        return $this->xPathQuery->get($query, $params);
    }

    /**
     * {@inheritDoc}
     */
    public function getNodes(string $query, array $params = []): DOMNodeList
    {
        if (!isset($this->xPathQuery)) {
            $this->xPathQuery = new XPathQuery($this);
        }

        return $this->xPathQuery->getNodes($query, $params);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $selector): mixed
    {
        return Selector::get($this->toArray(), $selector);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        if (!isset($this->array)) {
            $this->array = $this->query('/');
        }

        return $this->array;
    }

    /**
     * {@inheritDoc}
     */
    public function getDocumentElement(): ?DOMElement
    {
        return $this->documentElement;
    }
}
