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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail\Factory;

use DateTimeImmutable;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\EnvelopeInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\MessageInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Support\Envelope;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Support\Message;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;
use Symfony\Component\Mime\Address;

/**
 * Fábrica para crear un objeto Envelope a partir de una instancia de Mail.
 */
class EnvelopeFactory
{
    /**
     * Crea el sobre a partir de los datos de un correo entrante.
     *
     * @param IncomingMail $mail
     * @param array $attachmentFilters
     * @return EnvelopeInterface
     */
    public function createFromIncomingMail(
        IncomingMail $mail,
        array $attachmentFilters = []
    ): EnvelopeInterface {
        // Determinar quién envía el correo.
        if (!empty($mail->senderAddress)) {
            $senderAddress = $mail->senderAddress;
            $senderName = $mail->senderName ?? '';
        } else {
            $senderAddress = $mail->fromAddress;
            $senderName = $mail->fromName ?? '';
        }

        // Crear el listado completo con receptores del correo.
        $recipients = array_merge($mail->to, $mail->cc, $mail->bcc);

        // Crear el sobre.
        $envelope = new Envelope(
            new Address($senderAddress, $senderName),
            array_map(
                fn ($email, $name) => new Address($email, $name ?? ''),
                array_keys($recipients),
                $recipients
            )
        );

        // Crear el mensaje y agregarlo al sobre.
        $message = $this->createMessage($mail, $attachmentFilters);
        $envelope->addMessage($message);

        // Retornar el sobre con el mensaje.
        return $envelope;
    }

    /**
     * Crea el mensaje a partir de los datos de un correo entrante.
     *
     * @param IncomingMail $mail
     * @param array $attachmentFilters
     * @return MessageInterface
     */
    private function createMessage(
        IncomingMail $mail,
        array $attachmentFilters = []
    ): MessageInterface {
        // Crear el mensaje.
        $message = new Message();

        // Agregar el ID del mensaje (en el contexto del transporte).
        $message->id($mail->id);

        // Agregar la fecha del mensaje.
        $message->date(new DateTimeImmutable($mail->date));

        // Agregar remitente.
        $message->from(new Address($mail->fromAddress, $mail->fromName ?? ''));

        // Agregar destinatarios principales (TO).
        if (!empty($mail->to)) {
            $message->to(...array_map(
                fn ($email, $name) => new Address($email, $name ?? ''),
                array_keys($mail->to),
                $mail->to
            ));
        }

        // Agregar destinatarios en copia (CC).
        if (!empty($mail->cc)) {
            $message->cc(...array_map(
                fn ($email, $name) => new Address($email, $name ?? ''),
                array_keys($mail->cc),
                $mail->cc
            ));
        }

        // Agregar destinatarios ocultos (BCC).
        if (!empty($mail->bcc)) {
            $message->bcc(...array_map(
                fn ($email, $name) => new Address($email, $name ?? ''),
                array_keys($mail->bcc),
                $mail->bcc
            ));
        }

        // Agregar asunto.
        if (!empty($mail->subject)) {
            $message->subject($mail->subject);
        }

        // Agregar cuerpo del mensaje como texto plano.
        if (!empty($mail->textPlain)) {
            $message->text($mail->textPlain);
        }

        // Agregar cuerpo del mensaje como HTML.
        if (!empty($mail->textHtml)) {
            $message->html($mail->textHtml);
        }

        // Agregar los archivos adjuntos si existen.
        foreach ($mail->getAttachments() as $attachment) {
            if (
                !$attachmentFilters
                || $this->attachmentPassFilters($attachment, $attachmentFilters)
            ) {
                $message->attach(
                    $attachment->getContents(),
                    $attachment->name,
                    $attachment->mimeType
                );
            }
        }

        // Entregar el mensaje del correo electrónico.
        return $message;
    }

    /**
     * Aplica los filtros a una parte del mensaje.
     *
     * @param IncomingMailAttachment $attachment Parte del mensaje a filtrar.
     * @param array $filters Filtros a usar.
     * @return bool `true` si la parte del mensaje pasa los filtros, `false` en
     * caso contrario.
     */
    private function attachmentPassFilters(
        IncomingMailAttachment $attachment,
        array $filters
    ): bool {
        // Filtrar por: subtype.
        if (!empty($filters['subtype'])) {
            $subtype = strtoupper($attachment->subtype);
            $subtypes = array_map('strtoupper', $filters['subtype']);
            if (!in_array($subtype, $subtypes)) {
                return false;
            }
        }

        // Filtrar por: extension.
        if (!empty($filters['extension'])) {
            $extension = strtolower(pathinfo(
                $attachment->name,
                PATHINFO_EXTENSION
            ));
            $extensions = array_map('strtolower', $filters['extension']);
            if (!in_array($extension, $extensions)) {
                return false;
            }
        }

        // Pasó los filtros ok.
        return true;
    }
}
