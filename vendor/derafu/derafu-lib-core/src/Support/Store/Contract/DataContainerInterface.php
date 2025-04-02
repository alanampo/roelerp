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

use Exception;

/**
 * Interfaz para contenedor de datos estructurados con schema.
 */
interface DataContainerInterface extends StoreInterface
{
    /**
     * Asigna el schema que se usará para validar los datos.
     *
     * @param array $schema Nuevo schema a utilizar.
     * @return static Permite encadenar métodos.
     */
    public function setSchema(array $schema): static;

    /**
     * Obtiene el schema de datos definido.
     *
     * @return array Schema actual.
     */
    public function getSchema(): array;

    /**
     * Valida que los datos almacenados cumplan con el schema.
     *
     * @param bool $allowUndefinedKeys Permitir o no índices no definidos.
     * @return void
     * @throws Exception Lanzará una excepción si ocurre algún error.
     */
    public function validate(bool $allowUndefinedKeys = false): void;
}
