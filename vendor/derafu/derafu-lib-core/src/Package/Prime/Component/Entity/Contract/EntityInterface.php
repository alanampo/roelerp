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

namespace Derafu\Lib\Core\Package\Prime\Component\Entity\Contract;

use Derafu\Lib\Core\Common\Contract\StringableInterface;

/**
 * Interfaz para las entidades de repositorios.
 */
interface EntityInterface extends StringableInterface
{
    /**
     * Entrega las propiedades de la entidad como un arreglo.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Asignar un atributo a la entidad.
     *
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function setAttribute(string $name, mixed $value): static;

    /**
     * Obtener un atributo de la entidad.
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name): mixed;

    /**
     * Permite saber si existe o no un atributo definido para la entidad.
     *
     * @return bool
     */
    public function hasAttribute(string $name): bool;

    /**
     * Permite desasignar el valor de un atributo de la entidad.
     *
     * @param string $name
     * @return void
     */
    public function unsetAttribute(string $name): void;
}
