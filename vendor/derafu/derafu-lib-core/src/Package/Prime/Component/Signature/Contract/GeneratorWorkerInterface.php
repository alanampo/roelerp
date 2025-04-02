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

use Derafu\Lib\Core\Foundation\Contract\WorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Exception\SignatureException;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Exception\XmlException;

/**
 * Interfaz para la clase que maneja la generación de firmas electrónicas.
 */
interface GeneratorWorkerInterface extends WorkerInterface
{
    /**
     * Firma los datos proporcionados utilizando un certificado digital.
     *
     * @param string $data Datos que se desean firmar.
     * @param string $privateKey Clave privada que se utilizará para firmar.
     * @param string|int $signatureAlgorithm Algoritmo que se utilizará para
     * firmar (por defecto SHA1).
     * @return string Firma digital en base64.
     */
    public function sign(
        string $data,
        string $privateKey,
        string|int $signatureAlgorithm = OPENSSL_ALGO_SHA1
    ): string;

    /**
     * Firma un documento XML utilizando RSA y SHA1.
     *
     * @param XmlInterface|string $xml Documento XML que se desea firmar.
     * @param CertificateInterface $certificate Certificado digital para firmar.
     * @param ?string $reference Referencia a la que se hace la firma. Si no se
     * especifica se firmará el digest de todo el documento XML.
     * @return string String XML con la firma generada incluída en el tag
     * "Signature" al final del XML (último elemento dentro del nodo raíz).
     * @throws SignatureException Si ocurre algún problema al firmar.
     */
    public function signXml(
        XmlInterface|string $xml,
        CertificateInterface $certificate,
        ?string $reference = null
    ): string;

    /**
     * Genera la digestión SHA1 ("DigestValue") de un nodo del XML con cierta
     * referencia. Esto podrá ser usado luego para generar la firma del XML.
     *
     * Si no se indica una referencia se calculará el "DigestValue" sobre todo
     * el XML (nodo raíz).
     *
     * @param XmlInterface $doc Documento XML que se desea firmar.
     * @param ?string $reference Referencia a la que se hace la firma.
     * @return string Datos del XML que deben ser digeridos.
     * @throws XmlException En caso de no encontrar la referencia en el XML.
     */
    public function digestXmlReference(
        XmlInterface $doc,
        ?string $reference = null
    ): string;
}
