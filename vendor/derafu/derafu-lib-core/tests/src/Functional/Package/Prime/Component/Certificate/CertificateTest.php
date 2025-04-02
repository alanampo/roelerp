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

namespace Derafu\Lib\Tests\Functional\Package\Prime\Component\Certificate;

use Derafu\Lib\Core\Helper\AsymmetricKey;
use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Entity\Certificate;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Exception\CertificateException;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Support\CertificateFaker;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Worker\FakerWorker;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Worker\LoaderWorker;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FakerWorker::class)]
#[CoversClass(Certificate::class)]
#[CoversClass(CertificateException::class)]
#[CoversClass(CertificateFaker::class)]
#[CoversClass(LoaderWorker::class)]
#[CoversClass(AsymmetricKey::class)]
#[CoversClass(Str::class)]
class CertificateTest extends TestCase
{
    private FakerWorker $faker;

    protected function setUp(): void
    {
        $loader = new LoaderWorker();
        $this->faker = new FakerWorker($loader);
    }

    public function testCertificateDefaultData(): void
    {
        $certificate = $this->faker->create();
        $expected = [
            'getID' => '11222333-9',
            'getName' => 'Daniel',
            'getEmail' => 'daniel.bot@example.com',
            'isActive' => true,
            'getIssuer' => 'Derafu Autoridad Certificadora de Pruebas',
        ];
        $actual = [
            'getID' => $certificate->getID(),
            'getName' => $certificate->getName(),
            'getEmail' => $certificate->getEmail(),
            'isActive' => $certificate->isActive(),
            'getIssuer' => $certificate->getIssuer(),
        ];
        $this->assertSame($expected, $actual);
    }

    public function testCertificateCreationWithValidSerialNumber(): void
    {
        $certificate = $this->faker->create(id: '1-9');
        $this->assertSame('1-9', $certificate->getId());
    }

    public function testCertificateCreationWithInvalidSerialNumber(): void
    {
        $certificate = $this->faker->create(id: '1-2');
        $this->assertNotSame('1-9', $certificate->getID());
    }

    public function testCertificateCreationWithKSerialNumber(): void
    {
        $certificate = $this->faker->create(id: '10-k');
        $this->assertSame('10-K', $certificate->getID());
    }

    public function testGetModulus(): void
    {
        $certificate = $this->faker->create();
        $modulus = $certificate->getModulus();

        $this->assertNotEmpty($modulus);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\/+=\n]+$/', $modulus);
    }

    public function testGetExponent(): void
    {
        $certificate = $this->faker->create();
        $exponent = $certificate->getExponent();

        $this->assertNotEmpty($exponent);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\/+=]+$/', $exponent);
    }

    public function testGetNameThrowsExceptionForInvalidCertificate(): void
    {
        $this->expectException(CertificateException::class);

        $certificate = $this->faker->create(name: '');
        $certificate->getName();
    }

    public function testGetEmailThrowsExceptionForInvalidCertificate(): void
    {
        $this->expectException(CertificateException::class);

        $certificate = $this->faker->create(email: '');
        $certificate->getEmail();
    }

    public function testIsActiveForExpiredCertificate(): void
    {
        $certificate = $this->faker->create();

        $when = date('Y-m-d', strtotime('+10 year'));
        $this->assertFalse($certificate->isActive($when));
    }

    public function testGetExpirationDays(): void
    {
        $certificate = $this->faker->create();
        $days = $certificate->getExpirationDays();

        $this->assertGreaterThan(0, $days);
        $this->assertLessThanOrEqual(365, $days);
    }

    public function testGetDataReturnsParsedCertificateData(): void
    {
        $certificate = $this->faker->create();
        $data = $certificate->getData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('subject', $data);
        $this->assertArrayHasKey('issuer', $data);
    }
}
