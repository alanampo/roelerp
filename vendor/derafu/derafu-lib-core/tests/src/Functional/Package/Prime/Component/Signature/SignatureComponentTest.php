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

namespace Derafu\Lib\Tests\Functional\Package\Prime\Component\Signature;

use Derafu\Lib\Core\Helper\AsymmetricKey;
use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Core\Helper\Xml as XmlUtil;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Entity\Certificate;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Exception\CertificateException;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Support\CertificateFaker;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Worker\FakerWorker as CertificateFakerWorker;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Worker\LoaderWorker as CertificateLoaderWorker;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Entity\Signature;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Exception\SignatureException;
use Derafu\Lib\Core\Package\Prime\Component\Signature\SignatureComponent;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Worker\GeneratorWorker;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Worker\ValidatorWorker;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Entity\Xml;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Exception\XmlException;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Worker\DecoderWorker as XmlDecoderWorker;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Worker\EncoderWorker as XmlEncoderWorker;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Worker\ValidatorWorker as XmlValidatorWorker;
use Derafu\Lib\Core\Package\Prime\Component\Xml\XmlComponent;
use Derafu\Lib\Core\Support\Xml\XPathQuery;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Certificate::class)]
#[CoversClass(CertificateException::class)]
#[CoversClass(CertificateFaker::class)]
#[CoversClass(CertificateFakerWorker::class)]
#[CoversClass(CertificateLoaderWorker::class)]
#[CoversClass(Signature::class)]
#[CoversClass(SignatureComponent::class)]
#[CoversClass(GeneratorWorker::class)]
#[CoversClass(ValidatorWorker::class)]
#[CoversClass(Xml::class)]
#[CoversClass(XmlException::class)]
#[CoversClass(XmlDecoderWorker::class)]
#[CoversClass(XmlEncoderWorker::class)]
#[CoversClass(XmlValidatorWorker::class)]
#[CoversClass(XmlComponent::class)]
#[CoversClass(AsymmetricKey::class)]
#[CoversClass(Str::class)]
#[CoversClass(XmlUtil::class)]
#[CoversClass(XPathQuery::class)]
class SignatureComponentTest extends TestCase
{
    private string $xmlDir;

    private SignatureComponent $signatureComponent;

    private CertificateInterface $certificate;

    protected function setUp(): void
    {
        $this->xmlDir = self::getFixturesPath('package/prime/signature');

        $xmlEncoder = new XmlEncoderWorker();
        $xmlDecoder = new XmlDecoderWorker();
        $xmlValidator = new XmlValidatorWorker();
        $xmlComponent = new XmlComponent($xmlEncoder, $xmlDecoder, $xmlValidator);

        $generator = new GeneratorWorker($xmlComponent);
        $validator = new ValidatorWorker($generator, $xmlComponent);
        $this->signatureComponent = new SignatureComponent($generator, $validator);

        $certificateLoader = new CertificateLoaderWorker();
        $certificateFaker = new CertificateFakerWorker($certificateLoader);
        $this->certificate = $certificateFaker->create();
    }

    public function testSignatureComponentSignXmlString(): void
    {
        $xmlUnsigned = file_get_contents($this->xmlDir . '/unsigned.xml');
        $xmlSigned = $this->signatureComponent->getGeneratorWorker()->signXml(
            $xmlUnsigned,
            $this->certificate
        );

        $this->signatureComponent->getValidatorWorker()->validateXml($xmlSigned);
        $this->assertTrue(true);
    }

    public function testSignatureComponentSignXmlObject(): void
    {
        $xmlUnsigned = file_get_contents($this->xmlDir . '/unsigned.xml');
        $xml = new Xml();
        $xml->loadXml($xmlUnsigned);
        $xmlSigned = $this->signatureComponent->getGeneratorWorker()->signXml(
            $xml,
            $this->certificate
        );

        $this->signatureComponent->getValidatorWorker()->validateXml($xmlSigned);
        $this->assertTrue(true);
    }

    public function testSignatureComponentSignXmlWithReference(): void
    {
        $xmlUnsigned = file_get_contents($this->xmlDir . '/unsigned.xml');
        $xml = new Xml();
        $xml->loadXml($xmlUnsigned);
        $xmlSigned = $this->signatureComponent->getGeneratorWorker()->signXml(
            $xml,
            $this->certificate,
            'Derafu_SetDoc'
        );

        $this->signatureComponent->getValidatorWorker()->validateXml($xmlSigned);
        $this->assertTrue(true);
    }

    public function testSignatureComponentSignXmlWithInvalidReference(): void
    {
        $this->expectException(XmlException::class);

        $xmlUnsigned = file_get_contents($this->xmlDir . '/unsigned.xml');
        $xml = new Xml();
        $xml->loadXml($xmlUnsigned);
        $xmlSigned = $this->signatureComponent->getGeneratorWorker()->signXml(
            $xml,
            $this->certificate,
            'Derafu_SetDo'
        );
    }

    public function testSignatureComponentValidXmlSignature(): void
    {
        $xml = file_get_contents($this->xmlDir . '/valid_signed.xml');
        $this->signatureComponent->getValidatorWorker()->validateXml($xml);
        $this->assertTrue(true);
    }

    public function testSignatureComponentInvalidXmlSignature(): void
    {
        $this->expectException(SignatureException::class);

        $xml = file_get_contents($this->xmlDir . '/invalid_signed.xml');
        $this->signatureComponent->getValidatorWorker()->validateXml($xml);
    }
}
