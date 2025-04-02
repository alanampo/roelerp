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

use Derafu\Lib\Core\Foundation\Contract\ComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Exception\ManagerException;

/**
 * Interfaz para la clase de entidades.
 */
interface EntityComponentInterface extends ComponentInterface
{
    /**
     * Entrega la instancia del administrador de entidades.
     *
     * @return ManagerWorkerInterface
     */
    public function getManagerWorker(): ManagerWorkerInterface;

    /**
     * Entrega la instancia del proveedor de datos para las entidades.
     *
     * @return DatasourceProviderWorkerInterface
     */
    public function getDatasourceProviderWorker(): DatasourceProviderWorkerInterface;

    /**
     * Entrega un repositorio de entidades.
     *
     * @param string $repository
     * @return RepositoryInterface Repositorio solicitado.
     * @throws ManagerException
     */
    public function getRepository(string $repository): RepositoryInterface;
}
