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

use PhpImap\Mailbox as PhpImapMailbox;
use stdClass;

/**
 * Casilla de correo electrónico que se usará en la estrategia que recibe
 * correos mediante IMAP.
 */
class Mailbox extends PhpImapMailbox
{
    /**
     * Verifica si se está conectado al servidor IMAP.
     *
     * @return bool `true` si se está conectado, `false` en caso contrario.
     */
    public function isConnected(): bool
    {
        return $this->getImapStream() !== false;
    }

    /**
     * Obtiene el estado de la casilla de correo.
     *
     * @param string|null $folder Carpeta a consultar, `null` para la actual.
     * @return stdClass Objeto con el estado de la casilla de correo.
     */
    public function status(?string $folder = null): stdClass
    {
        $originalMailbox = $this->getImapPath();

        if ($folder !== null) {
            $this->switchMailbox($folder);
        }

        $status = $this->statusMailbox();

        if ($folder !== null) {
            $this->switchMailbox($originalMailbox);
        }

        return $status;
    }

    /**
     * Cuenta la cantidad de mensajes sin leer en la casilla de correo.
     *
     * @param string|null $folder Carpeta a consultar, `null` para la actual.
     * @return int Cantidad de mensajes sin leer.
     */
    public function countUnreadMails(?string $folder = null): int
    {
        $status = $this->status($folder);

        return $status->unseen ?? 0;
    }
}
