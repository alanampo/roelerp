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
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\FakerWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\LoaderWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Support\CertificateFaker;

/**
 * Clase que se encarga de generar certificados autofirmados (para pruebas).
 */
class FakerWorker extends AbstractWorker implements FakerWorkerInterface
{
    protected string $certificateFakerClass = CertificateFaker::class;

    public function __construct(
        private LoaderWorkerInterface $loader
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function create(
        ?string $id = null,
        ?string $name = null,
        ?string $email = null,
        ?string $password = null
    ): CertificateInterface {
        $class = $this->certificateFakerClass;
        $faker = new $class();

        $faker->setSubject(
            serialNumber: $id ?? '11222333-9',
            CN: $name ?? 'Daniel',
            emailAddress: $email ?? 'daniel.bot@example.com'
        );

        if ($password !== null) {
            $faker->setPassword($password);
        }

        $array = $faker->toArray();

        return $this->loader->createFromArray($array);
    }
}
