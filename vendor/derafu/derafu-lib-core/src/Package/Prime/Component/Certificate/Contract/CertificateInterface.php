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

use Derafu\Lib\Core\Helper\Str;

/**
 * Interfaz para la entidad del certificado digital.
 */
interface CertificateInterface
{
    /**
     * Entrega la llave pública y privada.
     *
     * @param boolean $clean Si se limpia el contenido del certificado.
     * @return array Arreglo con los índices: cert y pkey.
     */
    public function getKeys(bool $clean = false): array;

    /**
     * Entrega la clave pública (certificado) de la firma.
     *
     * @param bool $clean Si se limpia el contenido del certificado.
     * @return string Contenido del certificado, clave pública del certificado
     * digital, en base64.
     */
    public function getPublicKey(bool $clean = false): string;

    /**
     * Entrega la clave pública (certificado) de la firma.
     *
     * @param bool $clean Si se limpia el contenido del certificado.
     * @return string Contenido del certificado, clave pública del certificado
     * digital, en base64.
     */
    public function getCertificate(bool $clean = false): string;

    /**
     * Entrega la clave privada de la firma.
     *
     * @param bool $clean Si se limpia el contenido de la clave privada.
     * @return string Contenido de la clave privada del certificado digital
     * en base64.
     */
    public function getPrivateKey(bool $clean = false): string;

    /**
     * Entrega los detalles de la llave privada.
     *
     * @return array
     */
    public function getPrivateKeyDetails(): array;

    /**
     * Entrega los datos del certificado como arreglo.
     *
     * Alias de getCertX509().
     *
     * @return array Arreglo con todos los datos del certificado.
     */
    public function getData(): array;

    /**
     * Entrega los datos del certificado como string en formato PKCS #12.
     *
     * @param string $password
     * @return string
     */
    public function getPkcs12(string $password): string;

    /**
     * Entrega el ID asociado al certificado.
     *
     * El ID es el RUN que debe estar en una extensión, esto es lo estándar.
     * También podría estar en el campo `serialNumber`, algunos proveedores lo
     * colocan en este campo, también es más fácil para pruebas
     *
     * @param bool $forceUpper Si se fuerza a mayúsculas.
     * @return string ID asociado al certificado en formato: 11222333-4.
     */
    public function getId(bool $forceUpper = true): string;

    /**
     * Entrega el CN del subject.
     *
     * @return string CN del subject.
     */
    public function getName(): string;

    /**
     * Entrega el emailAddress del subject.
     *
     * @return string EmailAddress del subject.
     */
    public function getEmail(): string;

    /**
     * Entrega desde cuando es válida la firma.
     *
     * @return string Fecha y hora desde cuando es válida la firma.
     */
    public function getFrom(): string;

    /**
     * Entrega hasta cuando es válida la firma.
     *
     * @return string Fecha y hora hasta cuando es válida la firma.
     */
    public function getTo(): string;

    /**
     * Entrega los días totales que la firma es válida.
     *
     * @return int Días totales en que la firma es válida.
     */
    public function getTotalDays(): int;

    /**
     * Entrega los días que faltan para que la firma expire.
     *
     * @param string|null $from Fecha desde la que se calcula.
     * @return int Días que faltan para que la firma expire.
     */
    public function getExpirationDays(?string $from = null): int;

    /**
     * Indica si la firma está vigente o vencida.
     *
     * NOTE: Este método también validará que la firma no esté vigente en el
     * futuro. O sea, que la fecha desde cuándo está vigente debe estar en el
     * pasado.
     *
     * @param string|null $when Fecha de referencia para validar la vigencia.
     * @return bool `true` si la firma está vigente, `false` si está vencida.
     */
    public function isActive(?string $when = null): bool;

    /**
     * Entrega el nombre del emisor de la firma.
     *
     * @return string CN del issuer.
     */
    public function getIssuer(): string;

    /**
     * Obtiene el módulo de la clave privada.
     *
     * @return string Módulo en base64.
     */
    public function getModulus(int $wordwrap = Str::WORDWRAP): string;

    /**
     * Obtiene el exponente público de la clave privada.
     *
     * @return string Exponente público en base64.
     */
    public function getExponent(int $wordwrap = Str::WORDWRAP): string;
}
