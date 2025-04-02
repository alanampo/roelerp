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

use ArrayObject;
use Derafu\Lib\Core\Foundation\Contract\WorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Exception\DatasourceProviderException;

/**
 * Interfaz para el administrador de entidades.
 */
interface DatasourceProviderWorkerInterface extends WorkerInterface
{
    /**
     * Busca y entrega los datos en un origen de datos.
     *
     * Los datos se entregan como un ArrayObject para ser compartidos entre los
     * diferentes consumidores de datos que podrían requerir el mismo origen.
     *
     * @param string $source Identificador del origen solicitado.
     * @return ArrayObject
     * @throws DatasourceProviderException
     */
    public function fetch(string $source): ArrayObject;
}
