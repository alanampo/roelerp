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

use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\MessageInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Throwable;

/**
 * Clase que representa un mensaje de correo electrónico.
 */
class Message extends SymfonyEmail implements MessageInterface
{
    /**
     * Identificador único del mensaje (en el contexto del transporte).
     *
     * @var int
     */
    private int $id;

    /**
     * Error que ocurrió al transportar el mensaje.
     *
     * @var Throwable|null
     */
    private ?Throwable $error = null;

    /**
     * {@inheritDoc}
     */
    public function id(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): int
    {
        return $this->id ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function error(Throwable $error): static
    {
        $this->error = $error;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getError(): ?Throwable
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }
}
