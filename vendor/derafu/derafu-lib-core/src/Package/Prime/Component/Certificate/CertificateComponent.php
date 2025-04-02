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

namespace Derafu\Lib\Core\Package\Prime\Component\Certificate;

use Derafu\Lib\Core\Foundation\Abstract\AbstractComponent;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\FakerWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\LoaderWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\ValidatorWorkerInterface;

/**
 * Servicio que gestiona todo lo asociado a certificados digitales (aka: firmas
 * electrónicas).
 */
class CertificateComponent extends AbstractComponent implements CertificateComponentInterface
{
    /**
     * Instancia que permite generar certificados autofirmados.
     *
     * @var FakerWorkerInterface
     */
    private FakerWorkerInterface $faker;

    /**
     * Instancia que permite cargar (crear) certificados a partir de sus datos.
     *
     * @var LoaderWorkerInterface
     */
    private LoaderWorkerInterface $loader;

    /**
     * Instancia que permite validar un certificado digital.
     *
     * @var ValidatorWorkerInterface
     */
    private ValidatorWorkerInterface $validator;

    /**
     * Constructor del componente.
     *
     * @param LoaderWorkerInterface $loader
     * @param ValidatorWorkerInterface $validator
     * @param FakerWorkerInterface $faker
     */
    public function __construct(
        FakerWorkerInterface $faker,
        LoaderWorkerInterface $loader,
        ValidatorWorkerInterface $validator,
    ) {
        $this->faker = $faker;
        $this->loader = $loader;
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkers(): array
    {
        return [
            'faker' => $this->faker,
            'loader' => $this->loader,
            'validator' => $this->validator,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFakerWorker(): FakerWorkerInterface
    {
        return $this->faker;
    }

    /**
     * {@inheritDoc}
     */
    public function getLoaderWorker(): LoaderWorkerInterface
    {
        return $this->loader;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidatorWorker(): ValidatorWorkerInterface
    {
        return $this->validator;
    }
}
