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

namespace Derafu\Lib\Core\Package\Prime\Component\Signature;

use Derafu\Lib\Core\Foundation\Abstract\AbstractComponent;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\GeneratorWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\SignatureComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\ValidatorWorkerInterface;

/**
 * Servicio de firma electrónica.
 */
class SignatureComponent extends AbstractComponent implements SignatureComponentInterface
{
    /**
     * Generador de firmas electrónicas.
     *
     * @var GeneratorWorkerInterface
     */
    private GeneratorWorkerInterface $generator;

    /**
     * Validador de firmas electrónicas.
     *
     * @var ValidatorWorkerInterface
     */
    private ValidatorWorkerInterface $validator;

    /**
     * Constructor del servicio de firma electrónica.
     *
     * @param GeneratorWorkerInterface $generator
     * @param ValidatorWorkerInterface $validator
     */
    public function __construct(
        GeneratorWorkerInterface $generator,
        ValidatorWorkerInterface $validator
    ) {
        $this->generator = $generator;
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkers(): array
    {
        return [
            'generator' => $this->generator,
            'validator' => $this->validator,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getGeneratorWorker(): GeneratorWorkerInterface
    {
        return $this->generator;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidatorWorker(): ValidatorWorkerInterface
    {
        return $this->validator;
    }
}
