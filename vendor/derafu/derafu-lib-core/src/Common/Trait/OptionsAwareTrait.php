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

namespace Derafu\Lib\Core\Common\Trait;

use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;
use Derafu\Lib\Core\Support\Store\DataContainer;

/**
 * Trait para clases que deben implementar un sistema simple de opciones.
 *
 * @see Derafu\Lib\Core\Common\Contract\OptionsAwareInterface
 */
trait OptionsAwareTrait
{
    /**
     * Contenedor para las opciones.
     *
     * @var DataContainerInterface
     */
    protected DataContainerInterface $options;

    /**
     * Asigna las opciones de la clase.
     *
     * @param array|DataContainerInterface $options
     * @return static
     */
    public function setOptions(array|DataContainerInterface $options): static
    {
        if (is_array($options)) {
            $options = new DataContainer($options, $this->getOptionsSchema());
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Obtiene las opciones de la clase.
     *
     * @return DataContainerInterface
     */
    public function getOptions(): DataContainerInterface
    {
        if (!isset($this->options)) {
            $this->setOptions([]);
        }

        $this->options->validate();

        return $this->options;
    }

    /**
     * Resuelve las opciones de la clase.
     *
     * @param array $options
     * @return DataContainerInterface
     */
    public function resolveOptions(array $options = []): DataContainerInterface
    {
        if (isset($this->options)) {
            $options = array_merge($this->options->all(), $options);
        }

        return $this->setOptions($options)->options;
    }

    /**
     * Obtiene el esquema de las opciones.
     *
     * @return array
     */
    public function getOptionsSchema(): array
    {
        return $this->optionsSchema ?? [];
    }
}
