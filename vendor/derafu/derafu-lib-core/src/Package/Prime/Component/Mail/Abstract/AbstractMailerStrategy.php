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
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\SenderStrategyInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Exception\MailException;
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;
use Exception;
use Symfony\Component\Mailer\Envelope as SymfonyEnvelope;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email as SymfonyEmail;

/**
 * Estrategia base para el envío de correos electrónicos utilizando el Mailer de
 * Symfony.
 */
abstract class AbstractMailerStrategy extends AbstractStrategy implements SenderStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function send(PostmanInterface $postman): array
    {
        $options = $this->resolveOptions($postman->getOptions());

        $mailer = $this->createMailer($options);

        foreach ($postman->getEnvelopes() as $envelope) {
            assert($envelope instanceof SymfonyEnvelope);
            foreach ($envelope->getMessages() as $message) {
                assert($message instanceof SymfonyEmail);
                try {
                    $mailer->send($message, clone $envelope);
                } catch (Exception $e) {
                    $message->error($e);
                }
            }
        }

        return $postman->getEnvelopes();
    }

    /**
     * Crea un remitente de correo.
     *
     * @param DataContainerInterface $options Opciones del remitente.
     * @return Mailer
     */
    protected function createMailer(DataContainerInterface $options): Mailer
    {
        $dsn = $this->resolveDsn($options);
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $this->resolveEndpoint($options);

        return $mailer;
    }

    /**
     * Resuelve el DSN para el mailer.
     *
     * @param DataContainerInterface $options Configuración del mailer.
     * @return string DSN.
     */
    protected function resolveDsn(DataContainerInterface $options): string
    {
        $transportOptions = $options->get('transport');

        if (empty($transportOptions['dsn'])) {
            throw new MailException('No está definido el DSN para el Mailer.');
        }

        return $transportOptions['dsn'];
    }

    /**
     * Resuelve el endpoint personalizado.
     *
     * @param DataContainerInterface $options Configuración del mailer.
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
