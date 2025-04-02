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

namespace Derafu\Lib\Core\Package\Prime\Component\Signature\Contract;

use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlInterface;
use LogicException;

/**
 * Interfaz para la clase que representa la firma electrónica de un XML.
 */
interface SignatureInterface
{
    /**
     * Asigna los datos del nodo de la firma.
     *
     * @param array $data
     * @return static
     */
    public function setData(array $data): static;

    /**
     * Entrega los datos del nodo Signature.
     *
     * Esta es la estructura de datos que permite crear el nodo como XML.
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Agrega los datos necesarios al nodo de la firma para poder calcular la
     * firma sobre estos datos.
     *
     * @param string $digestValue El DigestValue calculado.
     * @param CertificateInterface $certificate El certificado digital a asignar.
     * @param string|null $reference La referencia URI, la cual debe incluir el
     * prefijo "#"
     * @return static La instancia actual para encadenamiento de métodos.
     */
    public function configureSignatureData(
        string $digestValue,
        CertificateInterface $certificate,
        ?string $reference = null
    ): static;

    /**
     * Asigna la instancia de Xml construida con los datos del nodo de
     * la firma electrónica.
     *
     * @param XmlInterface $xml
     * @return static
     */
    public function setXml(XmlInterface $xml): static;

    /**
     * Obtiene el objeto `Xml` que representa el nodo `Signature`.
     *
     * @return XmlInterface El objeto `Xml` con los datos del nodo
     * `Signature`.
     * @throws LogicException Cuando no está disponible el Xml del nodo.
     */
    public function getXml(): XmlInterface;

    /**
     * Obtiene la referencia asociada a la firma electrónica, si existe.
     *
     * @return string|null La referencia asociada al nodo `Signature`, o `null`.
     * si no tiene.
     */
    public function getReference(): ?string;

    /**
     * Obtiene el valor del DigestValue del nodo `Reference`.
     *
     * @return string|null El valor de DigestValue o `null` si no está definido.
     */
    public function getDigestValue(): ?string;

    /**
     * Obtiene el certificado X509 asociado al nodo `KeyInfo`.
     *
     * @return string|null El certificado X509 en base64 o `null` si no está
     * definido.
     */
    public function getX509Certificate(): ?string;

    /**
     * Establece el valor de la firma calculada para el nodo `SignedInfo`.
     *
     * @param string $signatureValue El valor de la firma en base64.
     * @return static La instancia actual para encadenamiento de métodos.
     */
    public function setSignatureValue(string $signatureValue): static;

    /**
     * Obtiene la firma calculada para el nodo `SignedInfo`.
     *
     * @return string|null El valor de la firma calculada en base64 o `null` si
     * no está definido.
     */
    public function getSignatureValue(): ?string;
}
