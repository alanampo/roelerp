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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail\Worker\Sender\Strategy;

use Derafu\Lib\Core\Package\Prime\Component\Mail\Abstract\AbstractMailerStrategy;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\SenderStrategyInterface;
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;

/**
 * Estrategia para el envío de correos electrónicos utilizando SMTP.
 */
class SmtpSenderStrategy extends AbstractMailerStrategy implements SenderStrategyInterface
{
    /**
     * Esquema de las opciones.
     *
     * @var array<string,array>
     */
    protected array $optionsSchema = [
        'strategy' => [
            'types' => 'string',
            'default' => 'smtp',
        ],
        'transport' => [
            'types' => 'array',
            'schema' => [
                'host' => [
                    'types' => 'string',
                    'default' => 'smtp.gmail.com',
                ],
                'port' => [
                    'types' => 'int',
                    'default' => 465,
                ],
                'encryption' => [
                    'types' => ['string', 'null'],
                    'default' => 'ssl',
                ],
                'username' => [
                    'types' => 'string',
                    'required' => true,
                ],
                'password' => [
                    'types' => 'string',
                    'required' => true,
                ],
                'verify_peer' => [
                    'types' => 'bool',
                    'default' => true,
                ],
                'dsn' => [
                    'types' => 'string',
                ],
                'endpoint' => [
                    'types' => 'string',
                ],
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    protected function resolveDsn(DataContainerInterface $options): string
    {
        $transportOptions = $options->get('transport');

        if (!empty($transportOptions['dsn'])) {
            return $transportOptions['dsn'];
        }

        $dsn = sprintf(
            'smtp://%s:%s@%s:%d?encryption=%s&verify_peer=%d',
            $transportOptions['username'],
            $transportOptions['password'],
            $transportOptions['host'],
            $transportOptions['port'],
            (string) $transportOptions['encryption'],
            (int) $transportOptions['verify_peer'],
        );

        $options->set('transport.dsn', $dsn);

        return $dsn;
    }

    /**
     * {@inheritDoc}
     */
    protected function resolveEndpoint(DataContainerInterface $options): string
    {
        $transportOptions = $options->get('transport');

        if (!empty($transportOptions['endpoint'])) {
            return $transportOptions['endpoint'];
        }

        $endpoint = '';

        if (isset($options['encryption'])) {
            $endpoint .= $options['encryption'] . '://';
        }

        $endpoint .= $options['host'];

        if (isset($options['port'])) {
            $endpoint .= ':' . $options['port'];
        }

        if (isset($options['verify_peer']) && !$options['verify_peer']) {
            $endpoint .= '/novalidate-cert';
        }

        $options->set('transport.endpoint', $endpoint);

        return $endpoint;
    }
}
