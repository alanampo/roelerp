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

namespace Derafu\Lib\Core\Helper;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use InvalidArgumentException;

/**
 * Clase con utilidades para trabajar con strings XML.
 */
class Xml
{
    /**
     * Ejecuta una consulta XPath en un documento XML.
     *
     * @param string|DOMDocument $xml Documento XML a consultar.
     * @param string $expression Expresión XPath a ejecutar en el XML.
     * @return DOMNodeList Nodos resultantes de la consulta XPath.
     */
    public static function xpath(
        string|DOMDocument $xml,
        string $expression
    ): DOMNodeList {
        if (is_string($xml)) {
            $document = new DOMDocument();
            $document->loadXml($xml);
        } else {
            $document = $xml;
        }

        $xpath = new DOMXPath($document);
        $result = @$xpath->query($expression);

        if ($result === false) {
            throw new InvalidArgumentException(sprintf(
                'Expresión XPath inválida: %s',
                $expression
            ));
        }

        return $result;
    }

    /**
     * Sanitiza los valores que son asignados a los tags del XML.
     *
     * @param string $xml Texto que se asignará como valor al nodo XML.
     * @return string Texto sanitizado.
     */
    public static function sanitize(string $xml): string
    {
        // Si no se paso un texto o bien es un número no se hace nada.
        if (!$xml || is_numeric($xml)) {
            return $xml;
        }

        // Convertir "predefined entities" de XML.
        $replace = [
            '&amp;' => '&',
            '&#38;' => '&',
            '&lt;' => '<',
            '&#60;' => '<',
            '&gt;' => '>',
            '&#62' => '>',
            '&quot;' => '"',
            '&#34;' => '"',
            '&apos;' => '\'',
            '&#39;' => '\'',
        ];
        $xml = str_replace(array_keys($replace), array_values($replace), $xml);

        // Esto es a propósito, se deben volver a reemplazar.
        $xml = str_replace('&', '&amp;', $xml);

        /*$xml = str_replace(
            ['"', '\''],
            ['&quot;', '&apos;'],
            $xml
        );*/

        // Entregar texto sanitizado.
        return $xml;
    }

    /**
     * Corrige las entities '&apos;' y '&quot;' en el XML.
     *
     * La corrección se realiza solo dentro del contenido de tags del XML, pero
     * no en los atributos de los tags.
     *
     * @param string $xml XML a corregir.
     * @return string XML corregido.
     */
    public static function fixEntities(string $xml): string
    {
        $replace = [
            '\'' => '&apos;',
            '"' => '&quot;',
        ];
        $replaceFrom = array_keys($replace);
        $replaceTo = array_values($replace);

        $newXml = '';
        $n_chars = strlen($xml);
        $convert = false;

        for ($i = 0; $i < $n_chars; ++$i) {
            if ($xml[$i] === '>') {
                $convert = true;
            }
            if ($xml[$i] === '<') {
                $convert = false;
            }
            $newXml .= $convert
                ? str_replace($replaceFrom, $replaceTo, $xml[$i])
                : $xml[$i]
            ;
        }

        return $newXml;
    }
}
