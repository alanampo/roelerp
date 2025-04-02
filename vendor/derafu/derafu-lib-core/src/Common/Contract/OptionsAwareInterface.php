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

namespace Derafu\Lib\Core\Common\Contract;

use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;

/**
 * Interfaz para clases que deben implementar un sistema simple de opciones.
 */
interface OptionsAwareInterface
{
    /**
     * Asigna las opciones que se deben usar en la clase.
     *
     * Este método asigna nuevas opciones a la clase.
     *
     * @param array|DataContainerInterface $options
     * @return static
     */
    public function setOptions(array|DataContainerInterface $options): static;

    /**
     * Obtiene las opciones que tiene asignada la clase.
     *
     * @return DataContainerInterface
     */
    public function getOptions(): DataContainerInterface;

    /**
     * Normaliza, y valida, las opciones de la clase.
     *
     * Este método resolverá las opciones haciendo merge con las que puedan
     * haber estado asignadas previamente y asignándolas a la clase.
     *
     * @param array $options Opciones sin normalizar o validar.
     * @return DataContainerInterface Opciones normalizadas y validadas.
     */
    public function resolveOptions(array $options): DataContainerInterface;

    /**
     * Entrega el esquema con el que se validarán las opciones.
     *
     * @return array
     */
    public function getOptionsSchema(): array;
}
