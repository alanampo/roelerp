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

namespace Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract;

use Derafu\Lib\Core\Foundation\Contract\WorkerInterface;

/**
 * Interfaz para la clase que permite crear un certificado digital autofirmado.
 */
interface FakerWorkerInterface extends WorkerInterface
{
    /**
     * Crea un certificado digital autofirmado (falso) para pruebas.
     *
     * @param string|null $id Identificador del usuario (RUN).
     * @param string|null $name Nombre del usuario del certificado.
     * @param string|null $email Correo electrónico del usuario del certificado.
     * @return CertificateInterface
     */
    public function create(
        ?string $id = null,
        ?string $name = null,
        ?string $email = null
    ): CertificateInterface;
}
