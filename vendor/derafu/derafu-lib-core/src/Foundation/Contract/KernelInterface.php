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

namespace Derafu\Lib\Core\Foundation\Contract;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Interfaz del núcleo de la aplicación.
 */
interface KernelInterface
{
    /**
     * Constructor del núcleo.
     *
     * @param ConfigurationInterface $configuration Configuración con la que se
     * ejecutará el núcleo y la aplicación.
     */
    public function __construct(ConfigurationInterface $configuration);

    /**
     * Entrega el contenedor de dependencias.
     *
     * @return ContainerBuilder
     */
    public function getContainer(): ContainerBuilder;

    /**
     * Entrega el servicio de configuración de la aplicación.
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration(): ConfigurationInterface;
}
