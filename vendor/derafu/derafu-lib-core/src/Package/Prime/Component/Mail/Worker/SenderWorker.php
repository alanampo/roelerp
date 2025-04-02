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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail\Worker;

use Derafu\Lib\Core\Foundation\Abstract\AbstractWorker;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\PostmanInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\SenderStrategyInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\SenderWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Exception\MailException;
use Throwable;

/**
 * Clase para el envío de correos electrónicos.
 */
class SenderWorker extends AbstractWorker implements SenderWorkerInterface
{
    /**
     * Esquema de las opciones.
     *
     * @var array<string,array|bool>
     */
    protected array $optionsSchema = [
        'strategy' => [
            'types' => 'string',
            'default' => 'smtp',
        ],
        'transport' => [
            'types' => 'array',
            'default' => [],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function send(PostmanInterface $postman): array
    {
        $options = $this->resolveOptions($postman->getOptions());
        $strategy = $this->getStrategy($options->get('strategy'));

        assert($strategy instanceof SenderStrategyInterface);

        try {
            $envelopes = $strategy->send($postman);
        } catch (Throwable $e) {
            throw new MailException(
                message: $e->getMessage(),
                previous: $e
            );
        }

        return $envelopes;
    }
}
