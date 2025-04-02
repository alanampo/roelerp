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

/**
 * Interfaz para los handler de los workers de la aplicación.
 *
 * Orquesta y agrupa lógica compleja que puede incluir varios jobs o
 * estrategias.
 */
interface HandlerInterface extends ServiceInterface
{
    // Esta interfaz deberá ser extendida definiendo específicamente cada
    // método handle() con sus tipos de argumentos y tipo de retorno.
    // public function handle();

    /**
     * Entrega una estrategia específica que el handler maneja.
     *
     * @param string $strategy
     * @return StrategyInterface
     */
    public function getStrategy(string $strategy): StrategyInterface;

    /**
     * Entrega el listado de estrategias que el handler puede manejar.
     *
     * @return StrategyInterface[]
     */
    public function getStrategies(): array;
}
