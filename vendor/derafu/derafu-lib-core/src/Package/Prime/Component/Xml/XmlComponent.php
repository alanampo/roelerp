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

namespace Derafu\Lib\Core\Package\Prime\Component\Xml;

use Derafu\Lib\Core\Foundation\Abstract\AbstractComponent;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\DecoderWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\EncoderWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\ValidatorWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlComponentInterface;

/**
 * Servicio para trabajar con documentos XML.
 */
class XmlComponent extends AbstractComponent implements XmlComponentInterface
{
    /**
     * Codificador de arreglo PHP a XML.
     *
     * @var EncoderWorkerInterface
     */
    private EncoderWorkerInterface $encoder;

    /**
     * Decodificador de XML a arreglo PHP.
     *
     * @var DecoderWorkerInterface
     */
    private DecoderWorkerInterface $decoder;

    /**
     * Validador de documentos XML.
     *
     * @var ValidatorWorkerInterface
     */
    private ValidatorWorkerInterface $validator;

    /**
     * Constructor del componente.
     *
     * @param EncoderWorkerInterface $encoder
     * @param DecoderWorkerInterface $decoder
     * @param ValidatorWorkerInterface $validator
     */
    public function __construct(
        EncoderWorkerInterface $encoder,
        DecoderWorkerInterface $decoder,
        ValidatorWorkerInterface $validator
    ) {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkers(): array
    {
        return [
            'encoder' => $this->encoder,
            'decoder' => $this->decoder,
            'validator' => $this->validator,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getEncoderWorker(): EncoderWorkerInterface
    {
        return $this->encoder;
    }

    /**
     * {@inheritDoc}
     */
    public function getDecoderWorker(): DecoderWorkerInterface
    {
        return $this->decoder;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidatorWorker(): ValidatorWorkerInterface
    {
        return $this->validator;
    }
}
