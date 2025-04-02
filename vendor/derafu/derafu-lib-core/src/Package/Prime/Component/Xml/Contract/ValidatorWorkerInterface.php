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
use Derafu\Lib\Core\Package\Prime\Component\Xml\Exception\XmlException;

/**
 * Interfaz para la clase que valida un documento XML.
 */
interface ValidatorWorkerInterface extends WorkerInterface
{
    /**
     * Realiza la validación de esquema de un documento XML.
     *
     * @param XmlInterface $xml Documento XML que se desea validar.
     * @param string|null $schemaPath Ruta hacia el archivo XSD del esquema
     * XML contra el que se validará. Si no se indica, se obtiene desde el
     * documento XML si está definido en "xsi:schemaLocation".
     * @param array $translations Traducciones adicionales para aplicar.
     * @throws XmlException Si el XML no es válido según su esquema.
     */
    public function validateSchema(
        XmlInterface $xml,
        ?string $schemaPath = null,
        array $translations = []
    ): void;
}
