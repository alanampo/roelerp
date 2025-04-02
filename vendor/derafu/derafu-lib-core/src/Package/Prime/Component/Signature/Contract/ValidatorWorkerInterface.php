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
use Derafu\Lib\Core\Package\Prime\Component\Signature\Exception\SignatureException;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlInterface;

/**
 * Interfaz para la clase que maneja la validación de firmas electrónicas.
 */
interface ValidatorWorkerInterface extends WorkerInterface
{
    /**
     * Verifica la firma digital de datos.
     *
     * @param string $data Datos que se desean verificar.
     * @param string $signature Firma digital de los datos en base64.
     * @param string $publicKey Clave pública de la firma de los datos.
     * @param string|int $signatureAlgorithm Algoritmo que se usó para firmar
     * (por defecto SHA1).
     * @return bool `true` si la firma es válida, `false` si es inválida.
     * @throws SignatureException Si hubo un error al hacer la verificación.
     */
    public function validate(
        string $data,
        string $signature,
        string $publicKey,
        string|int $signatureAlgorithm = OPENSSL_ALGO_SHA1
    ): bool;

    /**
     * Verifica la validez de la firma de un XML utilizando RSA y SHA1.
     *
     * @param XmlInterface|string $xml String XML que se desea validar.
     * @return void
     * @throws SignatureException Si hubo un error al hacer la verificación.
     */
    public function validateXml(XmlInterface|string $xml): void;

    /**
     * Crea la instancia `Xml` de `Signature` a partir de un
     * string XML con el nodo de la firma.
     *
     * @param string $xml String con el XML del nodo `Signature'.
     */
    public function createSignatureNode(string $xml): SignatureInterface;

    /**
     * Validar DigestValue de los datos firmados.
     *
     * @param XmlInterface|string $xml Documento XML que se desea validar.
     * @param SignatureInterface $signatureNode Nodo de firma que se validará.
     * @return void
     * @throws SignatureException Si el DigestValue no es válido.
     */
    public function validateXmlDigestValue(
        XmlInterface|string $xml,
        SignatureInterface $signatureNode
    ): void;

    /**
     * Valida la firma del nodo `SignedInfo` del XML utilizando el certificado
     * X509.
     *
     * @param SignatureInterface $signatureNode Nodo de firma que se validará.
     * @throws SignatureException Si la firma electrónica del XML no es válida.
     */
    public function validateXmlSignatureValue(
        SignatureInterface $signatureNode
    ): void;
}
