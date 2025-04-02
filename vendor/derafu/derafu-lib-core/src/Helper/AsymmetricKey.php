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

namespace Derafu\Lib\Core\Helper;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;

/**
 * Clase que proporciona utilidades comunes para certificados RSA que utilizan
 * cifrado asimétrico. Estos son usados, por ejemplo, en firma electrónica.
 */
class AsymmetricKey
{
    /**
     * Normaliza una clave pública (certificado) añadiendo encabezados y pies
     * si es necesario.
     *
     * @param string $publicKey Clave pública que se desea normalizar.
     * @param int $wordwrap Largo al que se debe dejar cada línea del archivo.
     * @return string Clave pública normalizada.
     */
    public static function normalizePublicKey(
        string $publicKey,
        int $wordwrap = Str::WORDWRAP
    ): string {
        if (!str_contains($publicKey, '-----BEGIN CERTIFICATE-----')) {
            $body = trim($publicKey);
            $publicKey = '-----BEGIN CERTIFICATE-----' . "\n";
            $publicKey .= Str::wordWrap($body, $wordwrap) . "\n";
            $publicKey .= '-----END CERTIFICATE-----' . "\n";
        }

        return $publicKey;
    }

    /**
     * Normaliza una clave privada añadiendo encabezados y pies si es necesario.
     *
     * @param string $privateKey Clave privada que se desea normalizar.
     * @param int $wordwrap Largo al que se debe dejar cada línea del archivo.
     * @return string Clave privada normalizada.
     */
    public static function normalizePrivateKey(
        string $privateKey,
        int $wordwrap = Str::WORDWRAP
    ): string {
        if (!str_contains($privateKey, '-----BEGIN PRIVATE KEY-----')) {
            $body = trim($privateKey);
            $privateKey = '-----BEGIN PRIVATE KEY-----' . "\n";
            $privateKey .= Str::wordWrap($body, $wordwrap) . "\n";
            $privateKey .= '-----END PRIVATE KEY-----' . "\n";
        }

        return $privateKey;
    }

    /**
     * Genera una clave pública a partir de un módulo y un exponente.
     *
     * @param string $modulus Módulo de la clave.
     * @param string $exponent Exponente de la clave.
     * @return string Clave pública generada.
     */
    public static function generatePublicKeyFromModulusExponent(
        string $modulus,
        string $exponent
    ): string {
        $modulus = new BigInteger(base64_decode($modulus), 256);
        $exponent = new BigInteger(base64_decode($exponent), 256);

        $rsa = PublicKeyLoader::load([
            'n' => $modulus,
            'e' => $exponent,
        ]);

        return (string) $rsa->toString('PKCS1');
    }
}
