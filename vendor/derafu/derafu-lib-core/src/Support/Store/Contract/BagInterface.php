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

namespace Derafu\Lib\Core\Support\Store\Contract;

use ArrayAccess;
use ArrayObject;

/**
 * Interfaz para contenedor simple de datos.
 */
interface BagInterface extends StoreInterface
{
    /**
     * Reemplaza todos los valores almacenados por nuevos valores.
     *
     * @param array|ArrayAccess|ArrayObject $data Nuevos valores a almacenar.
     * @return static Permite encadenar métodos.
     */
    public function replace(array|ArrayAccess|ArrayObject $data): static;

    /**
     * Combina los valores almacenados con nuevos valores.
     *
     * @param array|ArrayAccess|ArrayObject $data Valores a combinar con los
     * existentes.
     * @return static Permite encadenar métodos.
     */
    public function merge(array|ArrayAccess|ArrayObject $data): static;

    /**
     * Elimina un valor almacenado.
     *
     * @param string $key Llave del valor que se desea eliminar.
     * @return static Permite encadenar métodos.
     */
    public function remove(string $key): static;
}
