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

namespace Derafu\Lib\Core\Package\Prime\Contract;

use Derafu\Lib\Core\Foundation\Contract\PackageInterface;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\EntityComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Log\Contract\LogComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\MailComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\SignatureComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\TemplateComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlComponentInterface;

/**
 * Interfaz del paquete Prime.
 */
interface PrimePackageInterface extends PackageInterface
{
    /**
     * Entrega el componente "prime.certificate".
     *
     * @return CertificateComponentInterface
     */
    public function getCertificateComponent(): CertificateComponentInterface;

    /**
     * Entrega el componente "prime.entity".
     *
     * @return EntityComponentInterface
     */
    public function getEntityComponent(): EntityComponentInterface;

    /**
     * Entrega el componente "prime.log".
     *
     * @return LogComponentInterface
     */
    public function getLogComponent(): LogComponentInterface;

    /**
     * Entrega el componente "prime.mail".
     *
     * @return MailComponentInterface
     */
    public function getMailComponent(): MailComponentInterface;

    /**
     * Entrega el componente "prime.signature".
     *
     * @return SignatureComponentInterface
     */
    public function getSignatureComponent(): SignatureComponentInterface;

    /**
     * Entrega el componente "prime.template".
     *
     * @return TemplateComponentInterface
     */
    public function getTemplateComponent(): TemplateComponentInterface;

    /**
     * Entrega el componente "prime.xml".
     *
     * @return XmlComponentInterface
     */
    public function getXmlComponent(): XmlComponentInterface;
}
