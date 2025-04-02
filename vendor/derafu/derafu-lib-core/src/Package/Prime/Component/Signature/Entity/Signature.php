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

namespace Derafu\Lib\Core\Package\Prime\Component\Signature\Entity;

use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\SignatureInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlInterface;
use LogicException;

/**
 * Clase que representa el nodo `Signature` en un XML firmado electrónicamente
 * utilizando el estándar de firma digital de XML (XML DSIG).
 */
class Signature implements SignatureInterface
{
    /**
     * Documento XML que representa el nodo de la firma electrónica.
     *
     * @var XmlInterface
     */
    private XmlInterface $xml;

    /**
     * Datos del nodo Signature.
     *
     * Por defecto se dejan vacíos los datos que se completarán posteriormente.
     * Ya sea mediante una asignación de los datos o bien mediante la carga de
     * un nuevo XML con los datos.
     *
     * @var array
     */
    private array $data = [
        // Nodo raíz es Signature.
        // Este es el nodo que se incluirá en los XML firmados.
        'Signature' => [
            '@attributes' => [
                'xmlns' => 'http://www.w3.org/2000/09/xmldsig#',
            ],
            // Datos que se firmarán. Acá el más importante es el tag
            // "DigestValue" que contiene un "resumen" (digestión) del C14N
            // del nodo de la referencia.
            'SignedInfo' => [
                '@attributes' => [
                    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                ],
                'CanonicalizationMethod' => [
                    '@attributes' => [
                        'Algorithm' => 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
                    ],
                ],
                'SignatureMethod' => [
                    '@attributes' => [
                        'Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
                    ],
                ],
                'Reference' => [
                    '@attributes' => [
                        // Indica cuál es el nodo de la referencia, debe tener
                        // como prefijo un "#". Si está vacío se entiende que
                        // se desea firmar todo el XML.
                        'URI' => '', // Opcional.
                    ],
                    'Transforms' => [
                        'Transform' => [
                            '@attributes' => [
                                'Algorithm' => 'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                            ],
                        ],
                    ],
                    'DigestMethod' => [
                        '@attributes' => [
                            'Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
                        ],
                    ],
                    'DigestValue' => '', // Obligatorio.
                ],
            ],
            // Firma del C14N del nodo `SignedInfo`.
            // Se agrega después de construir el C14N del SignedInfo y firmar.
            'SignatureValue' => '', // Obligatorio.
            // Información de la clave pública para la validación posterior
            // de la firma electrónica.
            'KeyInfo' => [
                'KeyValue' => [
                    'RSAKeyValue' => [
                        'Modulus' => '', // Obligatorio.
                        'Exponent' => '', // Obligatorio.
                    ],
                ],
                'X509Data' => [
                    'X509Certificate' => '', // Obligatorio.
                ],
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function setData(array $data): static
    {
        // Asignar los datos.
        $this->data = $data;

        // Invalidar el documento XML del nodo Signature.
        $this->invalidateXml();

        // Retornar instancia para encadenamiento.
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function configureSignatureData(
        string $digestValue,
        CertificateInterface $certificate,
        ?string $reference = null
    ): static {
        return $this
            ->setReference($reference)
            ->setDigestValue($digestValue)
            ->setCertificate($certificate)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setXml(XmlInterface $xml): static
    {
        $this->xml = $xml;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getXml(): XmlInterface
    {
        // Si la instancia no ha sido asignada previamente se lanza una
        // excepción.
        if (!isset($this->xml)) {
            throw new LogicException(
                'La instancia de Xml no está disponible en Signature.'
            );
        }

        return $this->xml;
    }

    /**
     * Establece la referencia URI para la firma electrónica.
     *
     * @param string|null $reference La referencia URI, la cual debe incluir el
     * prefijo "#".
     * @return static La instancia actual para encadenamiento de métodos.
     */
    private function setReference(?string $reference = null): static
    {
        // Asignar URI de la referencia (o vacia si se firma todo el XML).
        $uri = $reference ? ('#' . ltrim($reference, '#')) : '';
        $this->data['Signature']['SignedInfo']['Reference']['@attributes']['URI'] = $uri;

        // Asignar algoritmo de transformación al momento de obtener el C14N.
        $this->data['Signature']['SignedInfo']['Reference']['Transforms']['Transform']['@attributes']['Algorithm'] = $reference
            ? 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315'
            : 'http://www.w3.org/2000/09/xmldsig#enveloped-signature'
        ;

        // Invalidar el documento XML del nodo Signature.
        $this->invalidateXml();

        // Retornar instancia para encadenamiento.
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReference(): ?string
    {
        $uri = $this->data['Signature']['SignedInfo']['Reference']['@attributes']['URI'];

        return $uri ? ltrim($uri, '#') : null;
    }

    /**
     * Establece el valor del DigestValue del nodo `Reference`.
     *
     * @param string $digestValue El DigestValue calculado.
     * @return static La instancia actual para encadenamiento de métodos.
     */
    private function setDigestValue(string $digestValue): static
    {
        // Asignar el digest value.
        $this->data['Signature']['SignedInfo']['Reference']['DigestValue'] = $digestValue;

        // Invalidar el documento XML del nodo Signature.
        $this->invalidateXml();

        // Retornar instancia para encadenamiento.
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDigestValue(): ?string
    {
        $digestValue = $this->data['Signature']['SignedInfo']['Reference']['DigestValue'];

        return $digestValue ?: null;
    }

    /**
     * Asigna un certificado digital a la instancia actual y actualiza los
     * valores correspondientes en el nodo `KeyInfo` (módulo, exponente y
     * certificado en formato X509).
     *
     * @param CertificateInterface $certificate El certificado digital a asignar.
     * @return static La instancia actual para encadenamiento de métodos.
     */
    private function setCertificate(CertificateInterface $certificate): static
    {
        // Agregar módulo, exponente y certificado. Este último contiene la
        // clave pública que permitirá a otros validar la firma del XML.
        $this->data['Signature']['KeyInfo']['KeyValue']['RSAKeyValue']['Modulus'] =
            $certificate->getModulus()
        ;
        $this->data['Signature']['KeyInfo']['KeyValue']['RSAKeyValue']['Exponent'] =
            $certificate->getExponent()
        ;
        $this->data['Signature']['KeyInfo']['X509Data']['X509Certificate'] =
            $certificate->getCertificate(true)
        ;

        // Invalidar el documento XML del nodo Signature.
        $this->invalidateXml();

        // Retornar instancia para encadenamiento.
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getX509Certificate(): ?string
    {
        $x509 = $this->data['Signature']['KeyInfo']['X509Data']['X509Certificate'];

        return $x509 ?: null;
    }

    /**
     * {@inheritDoc}
     */
    public function setSignatureValue(string $signatureValue): static
    {
        // Asignar firma electrónica del nodo `SignedInfo`.
        $this->data['Signature']['SignatureValue'] =
            Str::wordWrap($signatureValue)
        ;

        // Invalidar el documento XML del nodo Signature.
        $this->invalidateXml();

        // Retornar instancia para encadenamiento.
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSignatureValue(): ?string
    {
        $signatureValue = $this->data['Signature']['SignatureValue'];

        return $signatureValue ?: null;
    }

    /**
     * Invalida el Xml asociado al nodo de la firma.
     *
     * Este método se utiliza al asignar datos al nodo, pues el Xml
     * deberá ser regenerado (esto se hace fuera y se debe volver a asignar).
     *
     * La invalidación se realiza aplicando `unset()` al Xml.
     *
     * @return void
     */
    private function invalidateXml(): void
    {
        unset($this->xml);
    }
}
