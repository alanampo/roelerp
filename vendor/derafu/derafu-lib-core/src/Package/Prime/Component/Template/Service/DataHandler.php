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

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Service;

use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\DataHandlerInterface;
use Derafu\Lib\Core\Support\Store\Contract\RepositoryInterface;

/**
 * Servicio de manejo de datos.
 *
 * Permite manejar un dato (valor de una variable) y transformarlo (formatear)
 * según las reglas definidas para el handler.
 */
class DataHandler implements DataHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function handle(
        string $id,
        mixed $data,
        string|array|callable|DataHandlerInterface|RepositoryInterface|null $handler = null
    ): string {
        // Si es un string es una máscara de sprintf().
        if (is_string($handler)) {
            return sprintf($handler, $data);
        }

        // Si es un arreglo es el arreglo deberá contener el valor a traducir.
        // Si no existe, se entregará el mismo valor como string.
        elseif (is_array($handler)) {
            return (string) ($handler[$data] ?? $data);
        }

        // Si es una función se llama directamente y se retorna su resultado.
        elseif (is_callable($handler)) {
            return (string) $handler($data, $id);
        }

        // Si es un repositorio se busca la entidad y se retorna el string que
        // representa la interfaz. Cada Entidad deberá implementar __toString().
        elseif ($handler instanceof RepositoryInterface) {
            $entity = $handler->find($data);
            return (string) $entity;
        }

        // Entregar los datos formateados.
        return (string) $data;
    }
}
