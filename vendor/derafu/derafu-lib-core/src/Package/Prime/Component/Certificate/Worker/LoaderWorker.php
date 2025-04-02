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

namespace Derafu\Lib\Core\Package\Prime\Component\Certificate\Worker;

use Derafu\Lib\Core\Foundation\Abstract\AbstractWorker;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\LoaderWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Entity\Certificate;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Exception\CertificateException;

/**
 * Clase que maneja la configuración y carga de certificados digitales para la
 * firma electrónica.
 */
class LoaderWorker extends AbstractWorker implements LoaderWorkerInterface
{
    /**
     * {@inheritDoc}
     */
    public function createFromFile(string $filepath, string $password): Certificate
    {
        if (!is_readable($filepath)) {
            throw new CertificateException(sprintf(
                'No fue posible leer el archivo del certificado digital desde %s',
                $filepath
            ));
        }

        $data = file_get_contents($filepath);

        return self::createFromData($data, $password);
    }

    /**
     * {@inheritDoc}
     */
    public function createFromData(string $data, string $password): Certificate
    {
        $certs = [];

        if (openssl_pkcs12_read($data, $certs, $password) === false) {
            throw new CertificateException(sprintf(
                'No fue posible leer los datos del certificado digital.',
            ));
        }

        return self::createFromKeys($certs['cert'], $certs['pkey']);
    }

    /**
     * {@inheritDoc}
     */
    public function createFromArray(array $data): Certificate
    {
        $publicKey = $data['publicKey'] ?? $data['cert'] ?? null;
        $privateKey = $data['privateKey'] ?? $data['pkey'] ?? null;

        if ($publicKey === null) {
            throw new CertificateException(
                'La clave pública del certificado no fue encontrada.'
            );
        }

        if ($privateKey === null) {
            throw new CertificateException(
                'La clave privada del certificado no fue encontrada.'
            );
        }

        return self::createFromKeys($publicKey, $privateKey);
    }

    /**
     * {@inheritDoc}
     */
    public function createFromKeys(string $publicKey, string $privateKey): Certificate
    {
        return new Certificate($publicKey, $privateKey);
    }
}
