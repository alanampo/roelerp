<?php

declare(strict_types=1);

/**
 * Derafu: aplicación PHP (Núcleo).
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

use Derafu\Lib\Core\Helper\Str;

/**
 * Trait para clases que deben identificarse.
 *
 * Cada clase debería escribir al menos los métodos getId() y getName(). Las
 * implementaciones de este trait solo existir para entregar lo mínimo en caso
 * de no ser implementados. Sin embargo, esta implementación mínima puede no ser
 * la óptima en todos los casos donde se necesite usar.
 *
 * @see Derafu\Lib\Core\Common\Contract\IdentifiableInterface
 */
trait IdentifiableTrait
{
    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return static::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): int|string
    {
        $name = $this->getName();

        $id = str_replace(['\\', ' '], [':', '.'], $name);
        $id = Str::snake($id);
        $id = str_replace([':_', '._'], [':', '.'], $id);

        return $id;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->__toString();
    }
}
