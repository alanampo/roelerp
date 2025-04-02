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

namespace Derafu\Lib\Core\Package\Prime\Component\Mail\Contract;

use Derafu\Lib\Core\Foundation\Contract\ComponentInterface;

/**
 * Interfaz para el componente "prime.mail".
 */
interface MailComponentInterface extends ComponentInterface
{
    /**
     * Entrega el worker "prime.mail.receiver".
     *
     * @return ReceiverWorkerInterface
     */
    public function getReceiverWorker(): ReceiverWorkerInterface;

    /**
     * Entrega el worker "prime.mail.sender".
     *
     * @return SenderWorkerInterface
     */
    public function getSenderWorker(): SenderWorkerInterface;

    /**
     * Recibe sobres con mensajes de correo electrónico mediante las opciones
     * de transporte definidas en el cartero.
     *
     * @param PostmanInterface $postman Cartero para el transporte del correo.
     * @return EnvelopeInterface[] Sobres con mensajes recibidos.
     */
    public function receive(PostmanInterface $postman): array;

    /**
     * Envía sobres con mensaje a través de correo electrónico mediante las
     * opciones de transporte definidas en el cartero.
     *
     * @param PostmanInterface $postman Cartero para el transporte del correo.
     * @return EnvelopeInterface[] Sobres con mensajes enviados.
     */
    public function send(PostmanInterface $postman): array;
}
