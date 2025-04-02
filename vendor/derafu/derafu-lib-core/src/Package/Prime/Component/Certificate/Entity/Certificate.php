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

namespace Derafu\Lib\Core\Package\Prime\Component\Certificate\Entity;

use DateTime;
use Derafu\Lib\Core\Helper\AsymmetricKey;
use Derafu\Lib\Core\Helper\Str;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Exception\CertificateException;
use phpseclib3\File\X509;

/**
 * Clase que representa un certificado digital.
 */
class Certificate implements CertificateInterface
{
    /**
     * Clave pública (certificado).
     *
     * @var string
     */
    private string $publicKey;

    /**
     * Clave privada.
     *
     * @var string
     */
    private string $privateKey;

    /**
     * Detalles de la clave privada.
     *
     * @var array
     */
    private array $privateKeyDetails;

    /**
     * Datos parseados del certificado X509.
     *
     * @var array
     */
    private array $data;

    /**
     * Contructor del certificado digital.
     *
     * @param string $publicKey Clave pública (certificado).
     * @param string $privateKey Clave privada.
     */
    public function __construct(string $publicKey, string $privateKey)
    {
        $this->publicKey = AsymmetricKey::normalizePublicKey($publicKey);
        $this->privateKey = AsymmetricKey::normalizePrivateKey($privateKey);
    }

    /**
     * {@inheritDoc}
     */
    public function getKeys(bool $clean = false): array
    {
        return [
            'cert' => $this->getPublicKey($clean),
            'pkey' => $this->getPrivateKey($clean),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getPublicKey(bool $clean = false): string
    {
        if ($clean) {
            return trim(str_replace(
                ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'],
                '',
                $this->publicKey
            ));
        }

        return $this->publicKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getCertificate(bool $clean = false): string
    {
        return $this->getPublicKey($clean);
    }

    /**
     * {@inheritDoc}
     */
    public function getPrivateKey(bool $clean = false): string
    {
        if ($clean) {
            return trim(str_replace(
                ['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----'],
                '',
                $this->privateKey
            ));
        }

        return $this->privateKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getPrivateKeyDetails(): array
    {
        if (!isset($this->privateKeyDetails)) {
            $this->privateKeyDetails = openssl_pkey_get_details(
                openssl_pkey_get_private($this->privateKey)
            );
        }

        return $this->privateKeyDetails;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): array
    {
        if (!isset($this->data)) {
            $this->data = openssl_x509_parse($this->publicKey);
        }

        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function getPkcs12(string $password): string
    {
        // Exportar el certificado final en formato PKCS#12.
        openssl_pkcs12_export(
            $this->getPublicKey(),
            $data,
            $this->getPrivateKey(),
            $password
        );

        // Entregar los datos del certificado digital.
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getId(bool $forceUpper = true): string
    {
        // Verificar el serialNumber en el subject del certificado.
        $serialNumber = $this->getData()['subject']['serialNumber'] ?? null;
        if ($serialNumber !== null) {
            $serialNumber = ltrim(trim($serialNumber), '0');
            return $forceUpper ? strtoupper($serialNumber) : $serialNumber;
        }

        // Obtener las extensiones del certificado.
        $x509 = new X509();
        $cert = $x509->loadX509($this->publicKey);
        if (isset($cert['tbsCertificate']['extensions'])) {
            foreach ($cert['tbsCertificate']['extensions'] as $extension) {
                if (
                    $extension['extnId'] === 'id-ce-subjectAltName'
                    && isset($extension['extnValue'][0]['otherName']['value']['ia5String'])
                ) {
                    $id = ltrim(
                        trim($extension['extnValue'][0]['otherName']['value']['ia5String']),
                        '0'
                    );
                    return $forceUpper ? strtoupper($id) : $id;
                }
            }
        }

        // No se encontró el ID, se lanza excepción.
        throw new CertificateException(
            'No fue posible obtener el ID (RUN) del certificado digital (firma electrónica). Se recomienda verificar el formato y contraseña del certificado.'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        $name = $this->getData()['subject']['CN'] ?? null;
        if ($name === null) {
            throw new CertificateException(
                'No fue posible obtener el Name (subject.CN) de la firma.'
            );
        }

        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail(): string
    {
        $email = $this->getData()['subject']['emailAddress'] ?? null;
        if ($email === null) {
            throw new CertificateException(
                'No fue posible obtener el Email (subject.emailAddress) de la firma.'
            );
        }

        return $email;
    }

    /**
     * {@inheritDoc}
     */
    public function getFrom(): string
    {
        return date('Y-m-d\TH:i:s', $this->getData()['validFrom_time_t']);
    }

    /**
     * {@inheritDoc}
     */
    public function getTo(): string
    {
        return date('Y-m-d\TH:i:s', $this->getData()['validTo_time_t']);
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalDays(): int
    {
        $start = new DateTime($this->getFrom());
        $end = new DateTime($this->getTo());
        $diff = $start->diff($end);
        return (int) $diff->format('%a');
    }

    /**
     * {@inheritDoc}
     */
    public function getExpirationDays(?string $from = null): int
    {
        if ($from === null) {
            $from = date('Y-m-d\TH:i:s');
        }
        $start = new DateTime($from);
        $end = new DateTime($this->getTo());
        $diff = $start->diff($end);
        return (int) $diff->format('%a');
    }

    /**
     * {@inheritDoc}
     */
    public function isActive(?string $when = null): bool
    {
        if ($when === null) {
            $when = date('Y-m-d');
        }

        if (!isset($when[10])) {
            $when .= 'T23:59:59';
        }

        return $when >= $this->getFrom() && $when <= $this->getTo();
    }

    /**
     * {@inheritDoc}
     */
    public function getIssuer(): string
    {
        return $this->getData()['issuer']['CN'];
    }

    /**
     * {@inheritDoc}
     */
    public function getModulus(int $wordwrap = Str::WORDWRAP): string
    {
        $modulus = $this->getPrivateKeyDetails()['rsa']['n'] ?? null;

        if ($modulus === null) {
            throw new CertificateException(
                'No fue posible obtener el módulo de la clave privada.'
            );
        }

        return Str::wordWrap(base64_encode($modulus), $wordwrap);
    }

    /**
     * {@inheritDoc}
     */
    public function getExponent(int $wordwrap = Str::WORDWRAP): string
    {
        $exponent = $this->getPrivateKeyDetails()['rsa']['e'] ?? null;

        if ($exponent === null) {
            throw new CertificateException(
                'No fue posible obtener el exponente de la clave privada.'
            );
        }

        return Str::wordWrap(base64_encode($exponent), $wordwrap);
    }
}
