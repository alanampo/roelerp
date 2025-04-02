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

namespace Derafu\Lib\Core\Package\Prime;

use Derafu\Lib\Core\Foundation\Abstract\AbstractPackage;
use Derafu\Lib\Core\Package\Prime\Component\Certificate\Contract\CertificateComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\EntityComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Log\Contract\LogComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\MailComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Signature\Contract\SignatureComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\TemplateComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Contract\XmlComponentInterface;
use Derafu\Lib\Core\Package\Prime\Contract\PrimePackageInterface;

/**
 * Clase del paquete Prime.
 */
class PrimePackage extends AbstractPackage implements PrimePackageInterface
{
    /**
     * Constructor del paquete.
     *
     * @param CertificateComponentInterface $certificate
     * @param EntityComponentInterface $entity
     * @param LogComponentInterface $log
     * @param MailComponentInterface $mail
     * @param SignatureComponentInterface $signature
     * @param TemplateComponentInterface $template
     * @param XmlComponentInterface $xml
     */
    public function __construct(
        private CertificateComponentInterface $certificate,
        private EntityComponentInterface $entity,
        private LogComponentInterface $log,
        private MailComponentInterface $mail,
        private SignatureComponentInterface $signature,
        private TemplateComponentInterface $template,
        private XmlComponentInterface $xml,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getComponents(): array
    {
        return [
            'certificate' => $this->certificate,
            'entity' => $this->entity,
            'log' => $this->log,
            'mail' => $this->mail,
            'signature' => $this->signature,
            'template' => $this->template,
            'xml' => $this->xml,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getCertificateComponent(): CertificateComponentInterface
    {
        return $this->certificate;
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityComponent(): EntityComponentInterface
    {
        return $this->entity;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogComponent(): LogComponentInterface
    {
        $config = $this->getComponentConfiguration('log');

        return $this->log->setConfiguration($config);
    }

    /**
     * {@inheritDoc}
     */
    public function getMailComponent(): MailComponentInterface
    {
        return $this->mail;
    }

    /**
     * {@inheritDoc}
     */
    public function getSignatureComponent(): SignatureComponentInterface
    {
        return $this->signature;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateComponent(): TemplateComponentInterface
    {
        return $this->template;
    }

    /**
     * {@inheritDoc}
     */
    public function getXmlComponent(): XmlComponentInterface
    {
        return $this->xml;
    }
}
