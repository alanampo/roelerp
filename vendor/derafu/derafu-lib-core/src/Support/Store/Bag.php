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

namespace Derafu\Lib\Core\Support\Store;

use ArrayAccess;
use ArrayObject;
use Derafu\Lib\Core\Helper\Arr;
use Derafu\Lib\Core\Support\Store\Abstract\AbstractStore;
use Derafu\Lib\Core\Support\Store\Contract\BagInterface;

/**
 * Clase para contenedor simple de datos.
 */
class Bag extends AbstractStore implements BagInterface
{
    /**
     * Constructor del contenedor.
     *
     * @param array|ArrayAccess|ArrayObject $data Datos iniciales
     */
    public function __construct(array|ArrayAccess|ArrayObject $data = [])
    {
        $this->data = $this->createFrom($data);
    }

    /**
     * {@inheritDoc}
     */
    public function replace(array|ArrayAccess|ArrayObject $data): static
    {
        $this->data = $this->createFrom($data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function merge(array|ArrayAccess|ArrayObject $data): static
    {
        if (!is_array($data)) {
            $data = $this->createFrom($data)->toArray();
        }

        $this->data = $this->createFrom(
            Arr::mergeRecursiveDistinct($this->toArray(), $data)
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $key): static
    {
        if ($this->has($key)) {
            unset($this->data[$key]);
        }

        return $this;
    }
}
