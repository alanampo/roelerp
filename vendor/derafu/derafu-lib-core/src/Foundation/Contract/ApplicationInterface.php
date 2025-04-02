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
 * Interfaz para la clase principal de la aplicación.
 */
interface ApplicationInterface
{
    /**
     * Entrega una ruta dentro de la aplicación.
     *
     * Si no se especifica el $path se entregará el directorio raíz de la
     * aplicación.
     *
     * @param $path Ruta que se desea resolver.
     * @return string
     */
    public function path(?string $path = null): string;

    /**
     * Obtiene una configuración mediante su nombre (clave/selector).
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function config(string $name, mixed $default = null): mixed;

    /**
     * Verifica si un paquete está registrado en el contenedor.
     *
     * @param string $package
     * @return boolean
     */
    public function hasPackage(string $package): bool;

    /**
     * Obtiene un paquete registrado en el contenedor.
     *
     * Un paquete es un servicio que implementa PackageInterface.
     *
     * @param string $package
     * @return PackageInterface
     */
    public function getPackage(string $package): PackageInterface;

    /**
     * Obtiene la lista de paquetes registrados en el contenedor.
     *
     * Un paquete es un servicio que implementa PackageInterface.
     *
     * @return PackageInterface[]
     */
    public function getPackages(): array;

    /**
     * Verifica si un servicio está registrado en el contenedor.
     *
     * @param string $service
     * @return boolean
     */
    public function hasService(string $service): bool;

    /**
     * Obtiene un servicio registrado en el contenedor.
     *
     * @param string $service
     * @return object
     */
    public function getService(string $service): object;

    /**
     * Singleton para obtener siempre la misma instancia.
     *
     * @param ?string $servicesConfigFile Archivo de configuración de servicios.
     * @return static
     */
    public static function getInstance(?string $servicesConfigFile = null): static;
}
