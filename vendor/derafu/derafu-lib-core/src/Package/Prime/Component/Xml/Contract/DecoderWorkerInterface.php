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
 * Interfaz para la clase que decodifica un XML a arreglo PHP.
 */
interface DecoderWorkerInterface extends WorkerInterface
{
    /**
     * Convierte un documento XML a un arreglo PHP.
     *
     * @param XmlInterface|DOMElement $documentElement Documento XML que se
     * desea convertir a un arreglo de PHP o el elemento donde vamos a hacer la
     * conversión si no es el documento XML completo.
     * @param array|null $data Arreglo donde se almacenarán los resultados.
     * @param bool $twinsAsArray Indica si se deben tratar los nodos gemelos
     * como un arreglo.
     * @return array Arreglo con la representación del XML.
     */
    public function decode(
        XmlInterface|DOMElement $documentElement,
        ?array &$data = null,
        bool $twinsAsArray = false
    ): array;
}
