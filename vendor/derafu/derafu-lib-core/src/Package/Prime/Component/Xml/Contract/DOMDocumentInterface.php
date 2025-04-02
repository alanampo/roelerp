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

namespace Derafu\Lib\Core\Package\Prime\Component\Xml\Contract;

use DOMElement;
use DOMNodeList;
use DOMParentNode;

/**
 * Interfaz para la clase que representa un documento XML.
 *
 * Esta interfaz exista para que otras interfaces que se usen en clases que
 * extienden DOMDocument, como la entidad Xml, puedan resolver los métodos que
 * usan del DOMDocument oficial de PHP. Estos métodos no se deben implementar si
 * se extiende de DOMDocument. Es solo para que los IDE y analizadores
 * sintácticos no generen alertas en la interfaz.
 *
 * @see DOMDocument
 */
interface DOMDocumentInterface extends DOMParentNode
{
    /**
     * Entrega la instancia del elemento raíz del documento.
     *
     * Esta es una forma segura de acceder a $documentElement cuando se usa la
     * referencia a esta interfaz como tipo de datos de la variable.
     *
     * Si no se usa esto, herramientas como phpstan reclamarán.
     *
     * @return DOMElement|null
     */
    public function getDocumentElement(): ?DOMElement;

    /**
     * Canonicaliza el XML de acuerdo con la especificación C14N.
     *
     * La implementación oficial de esta interfaz hereda de DOMDocument por lo
     * que este método está disponible. Sin embargo se define en la interfaz
     * para que herramientas como phpstan no reclamen cuando se usa la
     * referencia a esta interfaz como tipo de datos de la variable.
     *
     * @param bool $exclusive Indica si se utiliza la canonicalización
     * exclusiva.
     * @param bool $withComments Incluye los comentarios en la salida si es
     * `true`.
     * @param array|null $xpath Lista opcional de nodos para incluir en la
     * canonicalización.
     * @param array|null $nsPrefixes Prefijos de namespaces a considerar en la
     * canonicalización.
     * @return string|false Una cadena con el XML canonicalizado o `false` en
     * caso de error.
     */
    public function C14N(
        bool $exclusive = false,
        bool $withComments = false,
        ?array $xpath = null,
        ?array $nsPrefixes = null
    ): string|false;

    /**
     * Crea un nuevo elemento en el documento XML.
     *
     * La implementación oficial de esta interfaz hereda de DOMDocument por lo
     * que este método está disponible. Sin embargo se define en la interfaz
     * para que herramientas como phpstan no reclamen cuando se usa la
     * referencia a esta interfaz como tipo de datos de la variable.
     *
     * @param string $localName El nombre local del elemento.
     * @param string $value El valor opcional del elemento.
     * @return DOMElement|false El elemento creado o `false` en caso de error.
     */
    public function createElement(
        string $localName,
        string $value = ''
    ); // Agregar tipo de retorno es incompatible con DOMDocument oficial de PHP.

    /**
     * Crea un nuevo elemento en un namespace específico.
     *
     * La implementación oficial de esta interfaz hereda de DOMDocument por lo
     * que este método está disponible. Sin embargo se define en la interfaz
     * para que herramientas como phpstan no reclamen cuando se usa la
     * referencia a esta interfaz como tipo de datos de la variable.
     *
     * @param string|null $namespace URI del namespace (puede ser `null` para
     * ninguno).
     * @param string $qualifiedName Nombre calificado del elemento.
     * @param string $value Valor opcional del elemento.
     * @return DOMElement|false El elemento creado o `false` en caso de error.
     */
    public function createElementNS(
        ?string $namespace,
        string $qualifiedName,
        string $value = ''
    ); // Agregar tipo de retorno es incompatible con DOMDocument oficial de PHP.

    /**
     * Valida el documento XML actual contra un esquema XML.
     *
     * La implementación oficial de esta interfaz hereda de DOMDocument por lo
     * que este método está disponible. Sin embargo se define en la interfaz
     * para que herramientas como phpstan no reclamen cuando se usa la
     * referencia a esta interfaz como tipo de datos de la variable.
     *
     * @param string $filename Ruta al archivo del esquema (.xsd).
     * @param int $flags Opciones de validación (por defecto `0`).
     * @return bool `true` si la validación es exitosa, `false` en caso de error.
     */
    public function schemaValidate(string $filename, int $flags = 0): bool;

    /**
     * Obtiene una lista de nodos (DOMNodeList) usando un nombre de etiqueta.
     *
     * Busca todos los elementos del documento actual que coincidan con el
     * nombre de etiqueta proporcionado (sin importar en qué parte del árbol se
     * encuentren).
     *
     * @param string $qualifiedName Nombre calificado de la etiqueta a buscar.
     * @return DOMNodeList Lista de nodos que coinciden con la etiqueta.
     */
    public function getElementsByTagName(string $qualifiedName): DOMNodeList;
}
