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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail\Contract;

use Throwable;

/**
 * Interfaz para el mensaje de correo electrónico.
 */
interface MessageInterface
{
    /**
     * Asigna el ID único del mensaje (respecto al contexto del transporte).
     *
     * @param integer $id
     * @return static
     */
    public function id(int $id): static;

    /**
     * Obtiene el ID único del mensaje (respecto al contexto del transporte).
     *
     * @return integer
     */
    public function getId(): int;

    /**
     * Asigna el error que ocurrió con el mensaje durante su transporte.
     *
     * @param Throwable $error
     * @return static
     */
    public function error(Throwable $error): static;

    /**
     * Obtiene el error que ocurrió con el mensaje durante su transporte.
     *
     * @return Throwable|null
     */
    public function getError(): ?Throwable;

    /**
     * Indica si el mensaje tuvo algún error al ser transportado.
     *
     * @return boolean
     */
    public function hasError(): bool;
}
