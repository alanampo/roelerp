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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail\Support;

use Derafu\Lib\Core\Common\Trait\OptionsAwareTrait;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\EnvelopeInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\PostmanInterface;
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;

/**
 * Clase que representa un sobre con mensajes que se enviarán por correo
 * electrónico.
 */
class Postman implements PostmanInterface
{
    use OptionsAwareTrait;

    /**
     * Sobres que el cartero transportará.
     *
     * @var EnvelopeInterface[]
     */
    private array $envelopes = [];

    /**
     * Esquema de las opciones del cartero.
     *
     * @var array
     */
    private array $optionsSchema = [];

    /**
     * Constructor del cartero.
     *
     * @param array $options
     */
    public function __construct(DataContainerInterface|array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    public function addEnvelope(EnvelopeInterface $envelope): static
    {
        $this->envelopes[] = $envelope;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvelopes(): array
    {
        return $this->envelopes;
    }
}
