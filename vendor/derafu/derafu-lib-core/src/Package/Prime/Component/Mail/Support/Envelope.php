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

use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\EnvelopeInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\MessageInterface;
use Symfony\Component\Mailer\Envelope as SymfonyEnvelope;
use Symfony\Component\Mime\Email as SymfonyEmail;

/**
 * Clase que representa un sobre con mensajes de correo electrónico.
 */
class Envelope extends SymfonyEnvelope implements EnvelopeInterface
{
    /**
     * Mensajes que el sobre contiene para ser enviados por correo.
     *
     * @var MessageInterface[]
     */
    private array $messages;

    /**
     * {@inheritDoc}
     */
    public function addMessage(MessageInterface $message): static
    {
        assert($message instanceof SymfonyEmail);

        $from = $message->getFrom();
        $sender = $message->getSender();

        if (!$from && !$sender) {
            $message->from($this->getSender());
        }

        $to = $message->getTo();
        $cc = $message->getCc();
        $bcc = $message->getBcc();

        if (!$to && !$cc && !$bcc) {
            $message->to(...$this->getRecipients());
        }

        $this->messages[] = $message;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Al clonar el objeto se eliminan los mensajes que el sobre contenía.
     *
     * Con esto queda un sobre "limpio" de Symfony sin los mensajes.
     *
     * @return void
     */
    public function __clone(): void
    {
        $this->messages = [];
    }
}
