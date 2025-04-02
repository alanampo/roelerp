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

use Derafu\Lib\Core\Foundation\Contract\WorkerInterface;
use DOMElement;

/**
 * Interfaz para la clase que codifica un arreglo PHP a XML.
 */
interface EncoderWorkerInterface extends WorkerInterface
{
    /**
     * Convierte un arreglo PHP a un documento XML, generando los nodos y
     * respetando un espacio de nombres si se proporciona.
     *
     * @param array $data Arreglo con los datos que se usarán para generar XML.
     * @param array|null $namespace Espacio de nombres para el XML (URI y
     * prefijo).
     * @param DOMElement|null $parent Elemento padre para los nodos, o null
     * para que sea la raíz.
     * @param XmlInterface $doc El documento raíz del XML que se genera.
     * @return XmlInterface
     */
    public function encode(
        array $data,
        ?array $namespace = null,
        ?DOMElement $parent = null,
        ?XmlInterface $doc = null
    ): XmlInterface;
}
