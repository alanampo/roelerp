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

namespace Derafu\Lib\Core\Package\Prime\Component\Signature\Worker;

use Derafu\Lib\Core\Foundation\Abstract\AbstractWorker;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\GeneratorWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\SignatureInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Entity\Signature;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Exception\SignatureException;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Entity\Xml;
use LogicException;

/**
 * Clase que maneja la generación de firmas electrónicas, en particular para
 * documentos XML.
 */
class GeneratorWorker extends AbstractWorker implements GeneratorWorkerInterface
{
    /**
     * Servicio de XMLs.
     *
     * @var XmlComponentInterface
     */
    private XmlComponentInterface $xmlComponent;

    /**
     * Constructor del generador de firmas electrónicas.
     *
     * @param XmlComponentInterface $xmlComponent
     */
    public function __construct(XmlComponentInterface $xmlComponent)
    {
        $this->xmlComponent = $xmlComponent;
    }

    /**
     * {@inheritDoc}
     */
    public function sign(
        string $data,
        string $privateKey,
        string|int $signatureAlgorithm = OPENSSL_ALGO_SHA1
    ): string {
        // Firmar los datos.
        $signature = null;
        $result = openssl_sign(
            $data,
            $signature,
            $privateKey,
            $signatureAlgorithm
        );

        // Si no se logró firmar los datos se lanza una excepción.
        if ($result === false) {
            throw new SignatureException('No fue posible firmar los datos.');
        }

        // Entregar la firma en base64.
        return base64_encode($signature);
    }

    /**
     * {@inheritDoc}
     */
    public function signXml(
        XmlInterface|string $xml,
        CertificateInterface $certificate,
        ?string $reference = null
    ): string {
        // Si se pasó un objeto Xml se convierte a string. Esto es
        // necesario para poder mantener el formato "lindo" si se pasó y poder
        // obtener el C14N de manera correcta.
        if (!is_string($xml)) {
            $xml = $xml->saveXml();
        }

        // Cargar el XML que se desea firmar.
        $doc = new Xml();
        $doc->loadXml($xml);
        if (!$doc->documentElement) {
            throw new SignatureException(
                'No se pudo obtener el documentElement desde el XML a firmar (posible XML mal formado).'
            );
        }

        // Calcular el "DigestValue" de los datos de la referencia.
        $digestValue = $this->digestXmlReference($doc, $reference);

        // Crear la instancia que representa el nodo de la firma con sus datos.
        $signatureNode = (new Signature())->configureSignatureData(
            reference: $reference,
            digestValue: $digestValue,
            certificate: $certificate
        );

        // Firmar el documento calculando el valor de la firma del nodo
        // `Signature`.
        $signatureNode = $this->signSignature(
            $signatureNode,
            $certificate
        );
        $xmlSignature = $signatureNode->getXml()->getXml();

        // Agregar la firma del XML en el nodo Signature.
        $signatureElement = $doc->createElement('Signature', '');
        $doc->documentElement->appendChild($signatureElement);
        $xmlSigned = str_replace('<Signature/>', $xmlSignature, $doc->saveXml());

        // Entregar el string XML del documento XML firmado.
        return $xmlSigned;
    }

    /**
     * {@inheritDoc}
     */
    public function digestXmlReference(
        XmlInterface $doc,
        ?string $reference = null
    ): string {
        // Se hará la digestión de una referencia (ID) específico en el XML.
        if (!empty($reference)) {
            $xpath = '//*[@ID="' . ltrim($reference, '#') . '"]';
            $dataToDigest = $doc->C14NWithIsoEncoding($xpath);
        }
        // Cuando no hay referencia, el digest es sobre todo el documento XML.
        // Si el XML ya tiene un nodo "Signature" dentro del nodo raíz se debe
        // eliminar ese nodo del XML antes de obtener su C14N.
        else {
            $docClone = clone $doc;
            $rootElement = $docClone->getDocumentElement();
            $signatureElement = $rootElement
                ->getElementsByTagName('Signature')
                ->item(0)
            ;
            if ($signatureElement) {
                $rootElement->removeChild($signatureElement);
            }
            $dataToDigest = $docClone->C14NWithIsoEncoding();
        }

        // Calcular la digestión sobre los datos del XML en formato C14N.
        $digestValue = base64_encode(sha1($dataToDigest, true));

        // Entregar el digest calculado.
        return $digestValue;
    }

    /**
     * Firma el nodo `SignedInfo` del documento XML utilizando un certificado
     * digital. Si no se ha proporcionado previamente un certificado, este
     * puede ser pasado como argumento en la firma.
     *
     * @return SignatureInterface El nodo de la firma que se firmará.
     * @param CertificateInterface $certificate Certificado digital a usar para firmar.
     * @return SignatureInterface El nodo de la firma firmado.
     * @throws LogicException Si no están las condiciones para firmar.
     */
    private function signSignature(
        SignatureInterface $signatureNode,
        CertificateInterface $certificate
    ): SignatureInterface {
        // Validar que esté asignado el DigestValue.
        if ($signatureNode->getDigestValue() === null) {
            throw new LogicException(
                'No es posible generar la firma del nodo Signature si aun no se asigna el DigestValue.'
            );
        }

        // Validar que esté asignado el certificado digital.
        if ($signatureNode->getX509Certificate() === null) {
            throw new LogicException(
                'No es posible generar la firma del nodo Signature si aun no se asigna el certificado digital.'
            );
        }

        // Crear el documento XML del nodo de la firma electrónica.
        $nodeXml = $this->createSignatureNodeXml(
            $signatureNode
        );

        // Generar el string XML de los datos que se firmarán.
        $xpath = "//*[local-name()='Signature']/*[local-name()='SignedInfo']";
        $signedInfoC14N = $nodeXml->C14NWithIsoEncoding($xpath);

        // Generar la firma de los datos, el tag `SignedInfo`.
        $signature = $this->sign(
            $signedInfoC14N,
            $certificate->getPrivateKey()
        );

        // Asignar la firma calculada al nodo de la firma.
        $signatureNode->setSignatureValue($signature);

        // Volver a crear el Xml del nodo de la firma.
        $this->createSignatureNodeXml($signatureNode);

        // Entregar el nodo de la firma.
        return $signatureNode;
    }

    /**
     * Crea la instancia Xml de Signature y la asigna a esta.
     *
     * @param SignatureInterface $signatureNode
     * @return XmlInterface
     */
    private function createSignatureNodeXml(
        SignatureInterface $signatureNode
    ): XmlInterface {
        $xml = new Xml();
        $xml->formatOutput = false;
        $xml = $this->xmlComponent->getEncoderWorker()->encode(
            data: $signatureNode->getData(),
            doc: $xml // Se pasa el Xml para asignar formatOutput.
        );

        $signatureNode->setXml($xml);

        return $signatureNode->getXml();
    }
}
