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

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Contract;

use Derafu\Lib\Core\Support\Store\Contract\RepositoryInterface;

/**
 * Interfaz para la clase que formatea los valores de datos.
 */
interface DataFormatterInterface
{
    /**
     * Asigna el mapa de handlers.
     *
     * Reemplazará un mapa previo si existía.
     *
     * @param array $handlers
     * @return static
     */
    public function setHandlers(array $handlers): static;

    /**
     * Obtiene el mapa de handlers.
     *
     * @return array<string,string|array|callable|DataHandlerInterface|RepositoryInterface>
     */
    public function getHandlers(): array;

    /**
     * Agrega un handler al mapa de handlers.
     *
     * @param string $id
     * @param string|array|callable|DataHandlerInterface|RepositoryInterface $handler
     * @return static
     */
    public function addHandler(
        string $id,
        string|array|callable|DataHandlerInterface|RepositoryInterface $handler
    ): static;

    /**
     * Obtiene un handler a partir de su identificador.
     *
     * @param string $id
     * @return string|array|callable|DataHandlerInterface|RepositoryInterface|null
     */
    public function getHandler(string $id): string|array|callable|DataHandlerInterface|RepositoryInterface|null;

    /**
     * Formatea el valor de un identificador según el mapa de handlers que esté
     * disponible en el servicio.
     *
     * @param string $id
     * @param mixed $data
     * @return string
     */
    public function format(string $id, mixed $data): string;
}
