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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail;

use Derafu\Lib\Core\Foundation\Abstract\AbstractComponent;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\MailComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\PostmanInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\ReceiverWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\SenderWorkerInterface;

/**
 * Componente de correo electrónico.
 *
 * Gestiona los correos electrónicos. Tanto el envío mediante SenderWorker y la
 * recepción mediante ReceiverWorker.
 */
class MailComponent extends AbstractComponent implements MailComponentInterface
{
    /**
     * Constructor del componente con sus dependencias.
     *
     * @param ReceiverWorkerInterface $receiverWorker
     * @param SenderWorkerInterface $senderWorker
     */
    public function __construct(
        private ReceiverWorkerInterface $receiverWorker,
        private SenderWorkerInterface $senderWorker
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkers(): array
    {
        return [
            'receiver' => $this->receiverWorker,
            'sender' => $this->senderWorker,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getReceiverWorker(): ReceiverWorkerInterface
    {
        return $this->receiverWorker;
    }

    /**
     * {@inheritDoc}
     */
    public function getSenderWorker(): SenderWorkerInterface
    {
        return $this->senderWorker;
    }

    /**
     * {@inheritDoc}
     */
    public function receive(PostmanInterface $postnam): array
    {
        return $this->receiverWorker->receive($postnam);
    }

    /**
     * {@inheritDoc}
     */
    public function send(PostmanInterface $postnam): array
    {
        return $this->senderWorker->send($postnam);
    }
}
