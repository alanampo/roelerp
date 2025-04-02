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

namespace Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract;

use Derafu\Lib\Core\Foundation\Contract\WorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Exception\CertificateException;

/**
 * Interfaz para la clase que carga certificados digitales.
 */
interface LoaderWorkerInterface extends WorkerInterface
{
    /**
     * Crea una instancia de Certificate desde un archivo que contiene el
     * certificado digital en formato PKCS#12.
     *
     * @param string $filepath Ruta al archivo que contiene el certificado
     * digital.
     * @param string $password Contraseña para acceder al contenido del
     * certificado.
     * @return CertificateInterface Instancia de la clase Certificate que
     * contiene la clave privada y el certificado público.
     * @throws CertificateException Si no se puede leer el archivo o cargar el
     * certificado.
     */
    public function createFromFile(
        string $filepath,
        string $password
    ): CertificateInterface;

    /**
     * Crea una instancia de Certificate desde un string que contiene los datos
     * del certificado digital en formato PKCS#12.
     *
     * @param string $data String que contiene los datos del certificado
     * digital.
     * @param string $password Contraseña para acceder al contenido del
     * certificado.
     * @return CertificateInterface Instancia de la clase Certificate que
     * contiene la clave privada y el certificado público.
     * @throws CertificateException Si no se puede cargar el certificado desde
     * los datos.
     */
    public function createFromData(
        string $data,
        string $password
    ): CertificateInterface;

    /**
     * Crea una instancia de Certificate desde un arreglo que contiene las
     * claves pública y privada.
     *
     * @param array $data Arreglo que contiene las claves 'publicKey'
     * (o 'cert') y 'privateKey' (o 'pkey').
     * @return CertificateInterface Instancia de la clase Certificate que
     * contiene la clave privada y el certificado público.
     */
    public function createFromArray(array $data): CertificateInterface;

    /**
     * Crea una instancia de Certificate a partir de una clave pública y una
     * clave privada.
     *
     * @param string $publicKey Clave pública del certificado.
     * @param string $privateKey Clave privada asociada al certificado.
     * @return CertificateInterface Instancia de la clase Certificate que
     * contiene la clave privada y el certificado público.
     */
    public function createFromKeys(
        string $publicKey,
        string $privateKey
    ): CertificateInterface;
}
