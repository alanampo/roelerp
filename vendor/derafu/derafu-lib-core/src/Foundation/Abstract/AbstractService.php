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

namespace Derafu\Lib\Core\Foundation\Abstract;

use Derafu\Lib\Core\Common\Contract\ConfigurableInterface;
use Derafu\Lib\Core\Common\Trait\IdentifiableTrait;
use Derafu\Lib\Core\Foundation\Contract\ServiceInterface;
use Derafu\Lib\Core\Helper\Arr;
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * Clase base para los servicios de la aplicación.
 */
abstract class AbstractService implements ServiceInterface
{
    use IdentifiableTrait;

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        if ($this instanceof LazyObjectInterface) {
            return get_parent_class($this);
        }

        return static::class;
    }

    /**
     * Resuelve las opciones del servicio a partir del esquema de opciones.
     *
     * @param array $options
     * @return DataContainerInterface
     */
    protected function resolveOptions(
        array|DataContainerInterface $options = []
    ): DataContainerInterface {
        // Si las opciones que se pasaron son un contenedor de datos se extraen
        // como arreglo.
        if ($options instanceof DataContainerInterface) {
            $options = $options->all();
        }

        // Si la clase implementa ConfigurableInterface se busca si tiene
        // opciones que estén definidas en la configuración para incluir al
        // resolver las opciones haciendo merge entre las de la configuración y
        // las que se puedan haber pasado.
        if ($this instanceof ConfigurableInterface) {
            $optionsFromConfiguration = $this->getConfiguration()->get('options');
            if ($optionsFromConfiguration) {
                $options = Arr::mergeRecursiveDistinct(
                    $optionsFromConfiguration,
                    $options
                );
            }
        }

        // Crear un nuevo contenedor con las opciones resuelvas y validar contra
        // el esquema de las opciones (si está definido).
        return new DataContainer($options, $this->getOptionsSchema());
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsSchema(): array
    {
        return $this->optionsSchema ?? [];
    }
}
