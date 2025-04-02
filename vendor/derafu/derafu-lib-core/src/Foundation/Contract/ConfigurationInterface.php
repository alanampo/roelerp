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

use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;

/**
 * Interfaz para la configuración de la aplicación.
 */
interface ConfigurationInterface
{
    /**
     * Obtiene una configuración mediante su clave.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Entrega los parámetros de la aplicación que se agregarán al contenedor de
     * servicios como parámetros (índice principal `parameters`).
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Entrega el prefijo que se debe asignar a los servicios que administra
     * esta aplicación.
     *
     * @return string
     */
    public function getServicesPrefix(): string;

    /**
     * Entrega la clase que se debe usar como kernel de la aplicación.
     *
     * @return string
     */
    public function getKernelClass(): string;

    /**
     * Entrega la clase que se debe usar para registrar los servicios de la
     * aplicación.
     *
     * @return string
     */
    public function getServiceRegistryClass(): string;

    /**
     * Entrega la clase, si se desea, para procesar los servicios después de
     * haber sido registrados y previo a ser compilados en el contenedor.
     *
     * @return string|null
     */
    public function getCompilerPassClass(): ?string;

    /**
     * Entrega una ruta dentro de la aplicación.
     *
     * Si no se especifica el $path se entregará el directorio raíz de la
     * aplicación.
     *
     * @param $path Ruta que se desea resolver.
     * @return string
     */
    public function resolvePath(?string $path = null): string;

    /**
     * Obtiene la configuración de un paquete de la aplicación.
     *
     * @param string $package
     * @return array|DataContainerInterface
     */
    public function getPackageConfiguration(
        string $package
    ): array|DataContainerInterface;
}
