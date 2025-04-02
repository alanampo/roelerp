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

namespace Derafu\Lib\Tests\Unit\Helper;

use Derafu\Lib\Core\Helper\AsymmetricKey;
use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Tests\TestCase;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AsymmetricKey::class)]
#[CoversClass(Str::class)]
class AsymmetricKeyTest extends TestCase
{
    /**
     * Verifica que `normalizePublicKey()` agregue correctamente los encabezados
     * y pies cuando el certificado no los tiene.
     */
    public function testAsymmetricKeyNormalizePublicKeyWithoutHeaders(): void
    {
        $certBody = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7sN2a9z8/PQleNzl+Tbh...";
        $expectedCert = "-----BEGIN CERTIFICATE-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7sN2a9z8/PQleNzl+Tbh\n...\n-----END CERTIFICATE-----\n";

        $normalizedCert = AsymmetricKey::normalizePublicKey($certBody);

        $this->assertSame($expectedCert, $normalizedCert);
    }

    /**
     * Verifica que `normalizePublicKey()` no modifique un certificado que ya
     * tiene los encabezados y pies.
     */
    public function testAsymmetricKeyNormalizePublicKeyWithHeaders(): void
    {
        $cert = <<<CERT
        -----BEGIN CERTIFICATE-----
        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7sN2a9z8/PQleNzl+Tbh...
        -----END CERTIFICATE-----
        CERT;

        $normalizedCert = AsymmetricKey::normalizePublicKey($cert);

        $this->assertSame($cert, $normalizedCert);
    }

    /**
     * Verifica que `normalizePublicKey()` respete el `wordwrap` al agregar
     * encabezados y pies.
     */
    public function testAsymmetricKeyNormalizePublicKeyWithCustomWordwrap(): void
    {
        $certBody = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7sN2a9z8/PQleNzl+Tbh...";
        $wordwrap = 10;
        $expectedCert = "-----BEGIN CERTIFICATE-----\nMIIBIjANBg\nkqhkiG9w0B\nAQEFAAOCAQ\n8AMIIBCgKC\nAQEA7sN2a9\nz8/PQleNzl\n+Tbh...\n-----END CERTIFICATE-----\n";

        $normalizedCert = AsymmetricKey::normalizePublicKey($certBody, $wordwrap);

        $this->assertSame($expectedCert, $normalizedCert);
    }

    /**
     * Verifica que `normalizePrivateKey` agregue correctamente los encabezados
     * y pies cuando el certificado no los tiene.
     */
    public function testAsymmetricKeyNormalizePrivateKeyWithoutHeaders(): void
    {
        $certBody = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7sN2a9z8/PQleNzl+Tbh...";
        $expectedCert = "-----BEGIN PRIVATE KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7sN2a9z8/PQleNzl+Tbh\n...\n-----END PRIVATE KEY-----\n";

        $normalizedCert = AsymmetricKey::normalizePrivateKey($certBody);

        $this->assertSame($expectedCert, $normalizedCert);
    }

    /**
     * Verifica que `normalizePrivateKey` no modifique un certificado que ya tiene
     * los encabezados y pies.
     */
    public function testAsymmetricKeyNormalizePrivateKeyWithHeaders(): void
    {
        $cert = <<<CERT
        -----BEGIN PRIVATE KEY-----
        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7sN2a9z8/PQleNzl+Tbh...
        -----END PRIVATE KEY-----
        CERT;

        $normalizedCert = AsymmetricKey::normalizePrivateKey($cert);

        $this->assertSame($cert, $normalizedCert);
    }

    /**
     * Verifica que `normalizePrivateKey` respete el `wordwrap` al agregar
     * encabezados y pies.
     */
    public function testAsymmetricKeyNormalizePrivateKeyWithCustomWordwrap(): void
    {
        $certBody = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7sN2a9z8/PQleNzl+Tbh...";
        $wordwrap = 10;
        $expectedCert = "-----BEGIN PRIVATE KEY-----\nMIIBIjANBg\nkqhkiG9w0B\nAQEFAAOCAQ\n8AMIIBCgKC\nAQEA7sN2a9\nz8/PQleNzl\n+Tbh...\n-----END PRIVATE KEY-----\n";

        $normalizedCert = AsymmetricKey::normalizePrivateKey($certBody, $wordwrap);

        $this->assertSame($expectedCert, $normalizedCert);
    }

    /**
     * Verifica que `generatePublicKeyFromModulusExponent` genere correctamente
     * una clave pública a partir del módulo y exponente.
     */
    public function testAsymmetricKeyGeneratePublicKeyFromModulusExponent(): void
    {
        // Estos valores son solo ejemplos; en la práctica usarías valores reales.
        $modulus = base64_encode((new BigInteger('1234567890'))->toBytes());
        $exponent = base64_encode((new BigInteger('65537'))->toBytes());

        // Generar la clave pública esperada manualmente.
        $rsa = PublicKeyLoader::load([
            'n' => new BigInteger(base64_decode($modulus), 256),
            'e' => new BigInteger(base64_decode($exponent), 256),
        ]);
        $expectedPublicKey = $rsa->toString('PKCS1');

        $publicKey = AsymmetricKey::generatePublicKeyFromModulusExponent($modulus, $exponent);

        $this->assertSame($expectedPublicKey, $publicKey);
    }
}
