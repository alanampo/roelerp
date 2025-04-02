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

namespace Derafu\Lib\Core\Common\Contract;

use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;

/**
 * Interfaz para clases que deben implementar un sistema simple de
 * configuración.
 */
interface ConfigurableInterface
{
    /**
     * Asigna una configuración a la clase.
     *
     * @param array|DataContainerInterface $configuration
     * @return static
     */
    public function setConfiguration(
        array|DataContainerInterface $configuration
    ): static;

    /**
     * Obtiene la configuración de la clase.
     *
     * @return DataContainerInterface
     */
    public function getConfiguration(): DataContainerInterface;

    /**
     * Normaliza, y valida, la configuración de la clase.
     *
     * @param array $configuration Configuración sin normalizar o validar.
     * @return DataContainerInterface Configuración normalizadas y validadas.
     */
    public function resolveConfiguration(
        array $configuration
    ): DataContainerInterface;

    /**
     * Entrega el esquema con el que se validarán las configuraciones.
     *
     * @return array
     */
    public function getConfigurationSchema(): array;
}
