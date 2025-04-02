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

namespace Derafu\Lib\Core\Foundation\Contract;

use Derafu\Lib\Core\Common\Contract\ConfigurableInterface;

/**
 * Interfaz para la clase de componentes de la aplicación.
 */
interface ComponentInterface extends ServiceInterface, ConfigurableInterface
{
    /**
     * Obtiene un worker del componente.
     *
     * Un worker es un servicio que implementa WorkerInterface.
     *
     * @param string $worker
     * @return WorkerInterface
     */
    public function getWorker(string $worker): WorkerInterface;

    /**
     * Obtiene la lista de workers del paquete.
     *
     * Un worker es un servicio que implementa WorkerInterface.
     *
     * @return WorkerInterface[]
     */
    public function getWorkers(): array;
}
