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

namespace Derafu\Lib\Core\Package\Prime\Component\Xml\Worker;

use Derafu\Lib\Core\Foundation\Abstract\AbstractWorker;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\ValidatorWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Exception\XmlException;
use LogicException;

/**
 * Clase para la validación de XML y manejo de errores.
 */
class ValidatorWorker extends AbstractWorker implements ValidatorWorkerInterface
{
    /**
     * Traducciones, y transformaciones, por defecto de los errores de libxml.
     *
     * El objetivo es simplificar los mensajes "técnicos" de libxml y dejarlos
     * más sencillos para que un humano "no técnico" los pueda entender más
     * fácilmente.
     *
     * @var array
     */
    private array $defaultLibxmlTranslations = [
        '\': '
            => '\' (línea %(line)s): ',
        ': [facet \'pattern\'] The value'
            => ': tiene el valor',
        ': This element is not expected. Expected is one of'
            => ': no era el esperado, el campo esperado era alguno de los siguientes',
        ': This element is not expected. Expected is'
            => ': no era el esperado, el campo esperado era',
        'is not accepted by the pattern'
            => 'el que no es válido según la expresión regular (patrón)',
        'is not a valid value of the local atomic type'
            => 'no es un valor válido para el tipo de dato del campo',
        'is not a valid value of the atomic type'
            => 'no es un valor válido, se requiere un valor de tipo',
        ': [facet \'maxLength\'] The value has a length of '
            => ': el valor del campo tiene un largo de ',
        '; this exceeds the allowed maximum length of '
            => ' caracteres excediendo el largo máximo permitido de ',
        ': [facet \'enumeration\'] The value '
            => ': el valor ',
        'is not an element of the set'
            => 'no es válido, debe ser alguno de los valores siguientes',
        '[facet \'minLength\'] The value has a length of'
            => 'el valor del campo tiene un largo de ',
        '; this underruns the allowed minimum length of'
            => ' y el largo mínimo requerido es',
        'Missing child element(s). Expected is'
            => 'debe tener en su interior, nivel inferior, el campo',
        'Character content other than whitespace is not allowed because the content type is \'element-only\''
            => 'el valor del campo es inválido',
        'Element'
            => 'Campo',
        ' ( '
            => ' \'',
        ' ).'
            => '\'.',
        'No matching global declaration available for the validation root'
            => 'El nodo raíz del XML no coincide con lo esperado en la definición del esquema',
    ];

    /**
     * {@inheritDoc}
     */
    public function validateSchema(
        XmlInterface $xml,
        ?string $schemaPath = null,
        array $translations = []
    ): void {
        // Determinar $schemaPath si no fue pasado.
        if ($schemaPath === null) {
            $schemaPath = $this->getSchemaPath($xml);
        }

        // Obtener estado actual de libxml y cambiarlo antes de validar para
        // poder obtenerlos a una variable si hay errores al validar.
        $useInternalErrors = libxml_use_internal_errors(true);

        // Validar el documento XML.
        $isValid = $xml->schemaValidate($schemaPath);

        // Obtener errores, limpiarlos y restaurar estado de errores de libxml.
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);

        // Si el XML no es válido lanzar excepción con los errores traducidos.
        if (!$isValid) {
            $errors = !empty($errors)
                ? $this->translateLibxmlErrors($errors, array_merge($translations, [
                    '{' . $xml->getNamespace() . '}' => '',
                ]))
                : []
            ;
            throw new XmlException(
                sprintf(
                    'La validación del XML falló usando el esquema %s.',
                    basename($schemaPath)
                ),
                $errors
            );
        }
    }

    /**
     * Traduce los errores de libxml a mensajes más sencillos para humanos.
     *
     * @param array $errors Arreglo con los errores originales de libxml.
     * @param array $translations Traducciones adicionales para aplicar.
     * @return array Arreglo con los errores traducidos.
     */
    private function translateLibxmlErrors(
        array $errors,
        array $translations = []
    ): array {
        // Definir reglas de traducción.
        $replace = array_merge($this->defaultLibxmlTranslations, $translations);

        // Traducir los errores.
        $translatedErrors = [];
        foreach ($errors as $error) {
            $translatedErrors[] = str_replace(
                ['%(line)s'],
                [(string) $error->line],
                str_replace(
                    array_keys($replace),
                    array_values($replace),
                    trim($error->message)
                )
            );
        }

        // Entregar errores traducidos.
        return $translatedErrors;
    }

    /**
     * Busca la ruta del esquema XML para validar el documento XML.
     *
     * @param XmlInterface $xml Documento XML para el cual se busca su
     * esquema XML.
     * @return string Ruta hacia el archivo XSD con el esquema del XML.
     * @throws XmlException Si el esquema del XML no se encuentra.
     */
    private function getSchemaPath(XmlInterface $xml): string
    {
        // Si no hay servicio de almacenamiento se debe dar un error.
        if (!isset($this->storageService)) {
            throw new LogicException(
                'No es posible determinar el esquema del XML para su validación pues no está asociado el servicio de almacenamiento. Se debe asociar el servicio o especificar la ruta absoluta al esquema al validar.'
            );
        }

        // Determinar el nombre del archivo del esquema del XML.
        $schema = $xml->getSchema();
        if ($schema === null) {
            throw new XmlException(
                'El XML no contiene una ubicación de esquema válida en el atributo "xsi:schemaLocation".'
            );
        }

        // Armar la ruta al archivo del esquema y corroborar que exista.
        $schemaPath = '';
        // $schemaPath = $this->storageService->getSchemasPath($schema);
        // if ($schemaPath === null) {
        //     throw new XmlException(sprintf(
        //         'No se encontró el archivo de esquema XML %s.',
        //         $schema
        //     ));
        // }

        // Entregar la ruta al esquema (existe y se puede leer el archivo).
        return $schemaPath;
    }
}
