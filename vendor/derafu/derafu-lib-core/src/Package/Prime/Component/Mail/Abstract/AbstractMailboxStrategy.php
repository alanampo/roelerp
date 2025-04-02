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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail\Abstract;

use Derafu\Lib\Core\Foundation\Abstract\AbstractStrategy;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\PostmanInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\ReceiverStrategyInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Exception\MailException;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Factory\EnvelopeFactory;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Support\Mailbox;
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;
use Exception;

/**
 * Estrategia base para la recepción de correos electrónicos usando un Mailbox.
 */
abstract class AbstractMailboxStrategy extends AbstractStrategy implements ReceiverStrategyInterface
{
    /**
     * Constructor de la estrategia con sus dependencias.
     *
     * @param EnvelopeFactory $envelopeFactory
     */
    public function __construct(protected EnvelopeFactory $envelopeFactory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function receive(PostmanInterface $postman): array
    {
        $options = $this->resolveOptions($postman->getOptions());

        $mailbox = $this->createMailbox($options);

        $criteria = $options->get('transport.search.criteria');
        $markAsSeen = $options->get('transport.search.markAsSeen');
        $attachmentFilters = $options->get('transport.search.attachmentFilters');

        try {
            // Obtener los ID de los correos según el criterio de búsqueda.
            $mailsIds = $mailbox->searchMailbox($criteria);

            // Obtener cada correo mediante su ID y armar el sobre para el
            // cartero. Los correos nunca se marcan acá como leídos. Se procesan
            // todos antes de marcarlos como leídos por si algo falla al
            // procesar.
            foreach ($mailsIds as $mailId) {
                $mail = $mailbox->getMail($mailId, markAsSeen: false);
                $envelope = $this->envelopeFactory->createFromIncomingMail(
                    $mail,
                    $attachmentFilters
                );
                $postman->addEnvelope($envelope);
            }

            // Marcar los mensajes como leídos si así se solicitó.
            if ($markAsSeen) {
                $mailbox->markMailsAsRead($mailsIds);
            }
        } catch (Exception $e) {
            throw new MailException(
                message: sprintf(
                    'Ocurrió un error al recibir los correos: %s',
                    $e->getMessage()
                ),
                previous: $e
            );
        }

        return $postman->getEnvelopes();
    }

    /**
     * Crea una casilla de correo.
     *
     * @param DataContainerInterface $options Opciones de la casilla de correo.
     * @return Mailbox
     */
    protected function createMailbox(DataContainerInterface $options): Mailbox
    {
        $dsn = $this->resolveDsn($options);
        $username = $options->get('transport.username');
        $password = $options->get('transport.password');

        $mailbox = new Mailbox($dsn, $username, $password);

        $this->resolveEndpoint($options);

        return $mailbox;
    }

    /**
     * Resuelve el DSN para la casilla de correo.
     *
     * @param DataContainerInterface $options Opciones de la casilla de correo.
     * @return string DSN.
     */
    protected function resolveDsn(DataContainerInterface $options): string
    {
        $transportOptions = $options->get('transport');

        if (empty($transportOptions['dsn'])) {
            throw new MailException('No está definido el DSN para el Mailbox.');
        }

        return $transportOptions['dsn'];
    }

    /**
     * Resuelve el endpoint personalizado.
     *
     * @param DataContainerInterface $options Opciones de la casilla de correo.
     * @return string Endpoint personalizado.
     */
    protected function resolveEndpoint(DataContainerInterface $options): string
    {
        $transportOptions = $options->get('transport');

        if (!empty($transportOptions['endpoint'])) {
            return $transportOptions['endpoint'];
        }

        $endpoint = $this->resolveDsn($options);

        $options->set('transport.endpoint', $endpoint);

        return $endpoint;
    }
}
