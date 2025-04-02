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
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Entity\Certificate;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Exception\CertificateException;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Support\CertificateFaker;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Worker\FakerWorker;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Worker\LoaderWorker;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Certificate::class)]
#[CoversClass(CertificateException::class)]
#[CoversClass(CertificateFaker::class)]
#[CoversClass(FakerWorker::class)]
#[CoversClass(LoaderWorker::class)]
#[CoversClass(AsymmetricKey::class)]
class CertificateLoaderTest extends TestCase
{
    private LoaderWorker $loader;

    private FakerWorker $faker;

    protected function setUp(): void
    {
        $this->loader = new LoaderWorker();
        $this->faker = new FakerWorker($this->loader);
    }

    public function testCreateFromFile(): void
    {
        $password = 'hola_mundo';

        $data = $this->faker->create()->getPkcs12($password);
        $tempFile = tempnam(sys_get_temp_dir(), 'cert');
        file_put_contents($tempFile, $data);

        $certificate = $this->loader->createFromFile($tempFile, $password);

        $this->assertInstanceOf(CertificateInterface::class, $certificate);
        unlink($tempFile);
    }

    public function testCreateFromData(): void
    {
        $password = 'hola_mundo';

        $data = $this->faker->create()->getPkcs12($password);
        $certificate = $this->loader->createFromData($data, $password);

        $this->assertInstanceOf(CertificateInterface::class, $certificate);
    }

    public function testCreateFromArray(): void
    {
        $certs = $this->faker->create()->getKeys();
        $certificate = $this->loader->createFromArray($certs);
        $this->assertInstanceOf(CertificateInterface::class, $certificate);
    }

    /**
     * Asegura que se lance una excepción cuando se intenta cargar un archivo
     * de certificado que no es legible.
     */
    public function testCreateFromFileThrowsExceptionForUnreadableFile(): void
    {
        $this->expectException(CertificateException::class);
        $this->expectExceptionMessage('No fue posible leer el archivo del certificado digital desde');
        $this->loader->createFromFile('/path/no/existe/cert.p12', 'testpass');
    }

    /**
     * Valida que se lance una excepción cuando se intenta cargar un
     * certificado desde datos corruptos o no válidos.
     */
    public function testCreateFromDataThrowsExceptionForInvalidData(): void
    {
        $this->expectException(CertificateException::class);
        $this->expectExceptionMessage('No fue posible leer los datos del certificado digital.');
        $invalidData = 'datos_corruptos';
        $this->loader->createFromData($invalidData, 'testpass');
    }

    /**
     * Valida que se lance una excepción cuando el array no contiene una clave
     * pública.
     */
    public function testCreateFromArrayThrowsExceptionForMissingPublicKey(): void
    {
        $this->expectException(CertificateException::class);
        $this->expectExceptionMessage('La clave pública del certificado no fue encontrada.');
        $certs = $this->faker->create()->getKeys();
        unset($certs['cert']); // Eliminar la clave pública para simular un array inválido.
        $this->loader->createFromArray($certs);
    }

    /**
     * Valida que se lance una excepción cuando el array no contiene una clave
     * privada.
     */
    public function testCreateFromArrayThrowsExceptionForMissingPrivateKey(): void
    {
        $this->expectException(CertificateException::class);
        $this->expectExceptionMessage('La clave privada del certificado no fue encontrada.');
        $certs = $this->faker->create()->getKeys();
        unset($certs['pkey']); // Eliminar la clave privada para simular un array inválido.
        $this->loader->createFromArray($certs);
    }
}
