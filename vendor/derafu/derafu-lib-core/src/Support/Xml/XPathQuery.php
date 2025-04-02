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

namespace Derafu\Lib\Core\Support\Xml;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use InvalidArgumentException;
use LogicException;

/**
 * Clase para facilitar el manejo de XML usando XPath.
 */
class XPathQuery
{
    /**
     * Instancia del documento XML.
     *
     * @var DOMDocument
     */
    private readonly DOMDocument $dom;

    /**
     * Instancia que representa el buscador con XPath.
     *
     * @var DOMXPath
     */
    private readonly DOMXPath $xpath;

    /**
     * Si se deben usar los prefijos de namespaces o si deben ser ignorados.
     *
     * @var boolean
     */
    private readonly bool $registerNodeNS;

    /**
     * Constructor que recibe el documento XML y prepara XPath.
     *
     * @param string|DOMDocument $xml Documento XML.
     * @param array $namespaces Arreglo asociativo con prefijo y URI.
     */
    public function __construct(
        string|DOMDocument $xml,
        array $namespaces = []
    ) {
        // Asignar instancia del documento DOM.
        if ($xml instanceof DOMDocument) {
            $this->dom = $xml;
        } else {
            $this->dom = new DOMDocument();
            $this->loadXml($xml);
        }

        // Crear instancia de consultas XPath sobre el documento DOM.
        $this->xpath = new DOMXPath($this->dom);

        // Asignar o desactivar uso de namespaces.
        if ($namespaces) {
            foreach ($namespaces as $prefix => $namespace) {
                $this->xpath->registerNamespace($prefix, $namespace);
            }
            $this->registerNodeNS = true;
        } else {
            $this->registerNodeNS = false;
        }
    }

    /**
     * Devuelve el DOMDocument usado internamente.
     *
     * @return DOMDocument
     */
    public function getDomDocument(): DOMDocument
    {
        return $this->dom;
    }

    /**
     * Ejecuta una consulta XPath y devuelve el resultado procesado.
     *
     * El resultado dependerá de o que se encuentre:
     *
     *   - `null`: si no hubo coincidencias.
     *   - string: si hubo una coincidencia.
     *   - string[]: si hubo más de una coincidencia.
     *
     * Si el nodo tiene hijos, devuelve un arreglo recursivo representando
     * toda la estructura de los nodos.
     *
     * @param string $query Consulta XPath.
     * @param array $params Arreglo de parámetros.
     * @param DOMNode|null $contextNode Desde qué nodo evaluar la expresión.
     * @return string|string[]|null El valor procesado: string, arreglo o null.
     */
    public function get(
        string $query,
        array $params = [],
        ?DOMNode $contextNode = null
    ): string|array|null {
        $nodes = $this->getNodes($query, $params, $contextNode);

        // Sin coincidencias.
        if ($nodes->length === 0) {
            return null;
        }

        // Un solo nodo.
        if ($nodes->length === 1) {
            return $this->processNode($nodes->item(0));
        }

        // Varios nodos.
        $results = [];
        foreach ($nodes as $node) {
            $results[] = $this->processNode($node);
        }

        return $results;
    }

    /**
     * Ejecuta una consulta XPath y devuelve un arreglo de valores.
     *
     * @param string $query Consulta XPath.
     * @param array $params Arreglo de parámetros.
     * @param DOMNode|null $contextNode Desde qué nodo evaluar la expresión.
     * @return string[] Arreglo de valores encontrados.
     */
    public function getValues(
        string $query,
        array $params = [],
        ?DOMNode $contextNode = null
    ): array {
        $nodes = $this->getNodes($query, $params, $contextNode);

        $results = [];
        foreach ($nodes as $node) {
            $results[] = $node->nodeValue;
        }

        return $results;
    }

    /**
     * Ejecuta una consulta XPath y devuelve el primer resultado como string.
     *
     * @param string $query Consulta XPath.
     * @param array $params Arreglo de parámetros.
     * @param DOMNode|null $contextNode Desde qué nodo evaluar la expresión.
     * @return string|null El valor del nodo, o `null` si no existe.
     */
    public function getValue(
        string $query,
        array $params = [],
        ?DOMNode $contextNode = null
    ): ?string {
        $nodes = $this->getNodes($query, $params, $contextNode);

        return $nodes->length > 0
            ? $nodes->item(0)->nodeValue
            : null
        ;
    }

    /**
     * Ejecuta una consulta XPath y devuelve los nodos resultantes.
     *
     * @param string $query Consulta XPath.
     * @param array $params Arreglo de parámetros.
     * @param DOMNode|null $contextNode Desde qué nodo evaluar la expresión.
     * @return DOMNodeList Nodos resultantes de la consulta XPath.
     */
    public function getNodes(
        string $query,
        array $params = [],
        ?DOMNode $contextNode = null
    ): DOMNodeList {
        try {
            $query = $this->resolveQuery($query, $params);
            $nodes = $this->execute(fn () => $this->xpath->query(
                $query,
                $contextNode,
                $this->registerNodeNS
            ));
        } catch (LogicException $e) {
            throw new InvalidArgumentException(sprintf(
                'Ocurrió un error al ejecutar la expresión XPath: %s. %s',
                $query,
                $e->getMessage()
            ));
        }

        return $nodes;
    }

    /**
     * Carga un string XML en el atributo $dom.
     *
     * @param string $xml
     * @return static
     */
    private function loadXml(string $xml): static
    {
        try {
            $this->execute(fn () => $this->dom->loadXml($xml));
        } catch (LogicException $e) {
            throw new InvalidArgumentException(sprintf(
                'El XML proporcionado no es válido: %s',
                $e->getMessage()
            ));
        }

        return $this;
    }

    /**
     * Procesa un nodo DOM y sus hijos recursivamente.
     *
     * @param DOMNode $node Nodo DOM a procesar.
     * @return string|array Valor del nodo o estructura de hijos como arreglo.
     */
    private function processNode(DOMNode $node): string|array
    {
        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    $children[$child->nodeName] = $this->processNode($child);
                }
            }

            // Si tiene hijos procesados, devolver la estructura.
            return count($children) > 0 ? $children : $node->nodeValue;
        }

        // Si no tiene hijos, devolver el valor.
        return $node->nodeValue;
    }

    /**
     * Resuelve los parámetros de una consulta XPath.
     *
     * Este método reemplaza los marcadores nombrados (como `:param`) en la
     * consulta con las comillas en los valores escapadas.
     *
     * @param string $query Consulta XPath con marcadores nombrados (ej.: ":param").
     * @param array $params Arreglo de parámetros en formato ['param' => 'value'].
     * @return string Consulta XPath con los valores reemplazados.
     */
    private function resolveQuery(string $query, array $params = []): string
    {
        // Si los namespaces están desactivados, se adapta la consulta XPath.
        if (!$this->registerNodeNS) {
            $query = preg_replace_callback(
                '/(?<=\/|^)(\w+)/',
                fn ($matches) => '*[local-name()="' . $matches[1] . '"]',
                $query
            );
        }

        // Reemplazar parámetros.
        foreach ($params as $key => $value) {
            $placeholder = ':' . ltrim($key, ':');
            $quotedValue = $this->quoteValue($value);
            $query = str_replace($placeholder, $quotedValue, $query);
        }

        // Entregar la consulta resuelta.
        return $query;
    }

    /**
     * Escapa un valor para usarlo en una consulta XPath.
     *
     * Si la versión es PHP 8.4 o superior se utiliza DOMXPath::quote().
     * De lo contrario, se usa una implementación manual.
     *
     * @param string $value Valor a escapar.
     * @return string Valor escapado como literal XPath.
     */
    private function quoteValue(string $value): string
    {
        // Disponible solo desde PHP 8.4.
        if (method_exists(DOMXPath::class, 'quote')) {
            return DOMXPath::quote($value);
        }

        // Implementación manual para versiones anteriores a PHP 8.4.
        if (!str_contains($value, "'")) {
            return "'" . $value . "'";
        }
        if (!str_contains($value, '"')) {
            return '"' . $value . '"';
        }

        // Si contiene comillas simples y dobles, combinarlas con concat().
        return "concat('" . str_replace("'", "',\"'\",'", $value) . "')";
    }

    /**
     * Envoltura para ejecutar capturando los errores los métodos asociados a
     * la instnacia de XPath.
     *
     * @param callable $function
     * @return mixed
     */
    private function execute(callable $function): mixed
    {
        $use_errors = libxml_use_internal_errors(true);

        $result = $function();

        $error = $this->getLastError();
        if ($result === false || $error) {
            libxml_clear_errors();
            libxml_use_internal_errors($use_errors);

            $message = $error ?: 'Ocurrió un error en XPathQuery.';
            throw new LogicException($message);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($use_errors);

        return $result;
    }

    /**
     * Entrega, si existe, el último error generado de XML.
     *
     * @return string|null
     */
    private function getLastError(): ?string
    {
        $error = libxml_get_last_error();

        if (!$error) {
            return null;
        }

        return trim($error->message) . '.';
    }
}
