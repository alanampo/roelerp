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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail\Worker\Receiver\Strategy;

use Derafu\Lib\Core\Package\Prime\Component\Mail\Abstract\AbstractMailboxStrategy;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\ReceiverStrategyInterface;
use Derafu\Lib\Core\Support\Store\Contract\DataContainerInterface;

/**
 * Clase para la recepción de correos utilizando IMAP.
 */
class ImapReceiverStrategy extends AbstractMailboxStrategy implements ReceiverStrategyInterface
{
    /**
     * Esquema de las opciones.
     *
     * @var array<string,array>
     */
    protected array $optionsSchema = [
        'strategy' => [
            'types' => 'string',
            'default' => 'imap',
        ],
        'transport' => [
            'types' => 'array',
            'schema' => [
                'host' => [
                    'types' => 'string',
                    'default' => 'imap.gmail.com',
                ],
                'port' => [
                    'types' => 'int',
                    'default' => 993,
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
                'mailbox' => [
                    'types' => 'string',
                    'default' => 'INBOX',
                ],
                'attachments_dir' => [
                    'types' => ['string', 'null'],
                    'default' => null,
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
                'search' => [
                    'types' => 'array',
                    'schema' => [
                        'criteria' => [
                            'types' => 'string',
                            'default' => 'UNSEEN',
                        ],
                        'markAsSeen' => [
                            'types' => 'bool',
                            'default' => false,
                        ],
                        'attachmentFilters' => [
                            'types' => 'array',
                            'default' => [],
                        ],
                    ],
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
            '{%s:%d/imap%s%s}%s',
            $transportOptions['host'],
            $transportOptions['port'],
            $transportOptions['encryption'] === 'ssl' ? '/ssl' : '',
            (
                isset($transportOptions['verify_peer'])
                && !$transportOptions['verify_peer']
            )
                ? '/novalidate-cert'
                : '',
            $transportOptions['mailbox']
        );

        $options->set('transport.dsn', $dsn);

        return $dsn;
    }
}
