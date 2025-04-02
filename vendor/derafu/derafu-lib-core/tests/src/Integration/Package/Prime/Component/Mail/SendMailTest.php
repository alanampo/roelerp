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

namespace Derafu\Lib\Tests\Integration\Package\Prime\Component\Mail;

use Derafu\Lib\Core\Foundation\Abstract\AbstractServiceRegistry;
use Derafu\Lib\Core\Foundation\Abstract\AbstractWorker;
use Derafu\Lib\Core\Foundation\Application;
use Derafu\Lib\Core\Foundation\Configuration;
use Derafu\Lib\Core\Foundation\Kernel;
use Derafu\Lib\Core\Foundation\ServiceConfigurationCompilerPass;
use Derafu\Lib\Core\Foundation\ServiceProcessingCompilerPass;
use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Abstract\AbstractMailerStrategy;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Contract\MailComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Mail\MailComponent;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Support\Envelope;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Support\Message;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Support\Postman;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Worker\Sender\Strategy\SmtpSenderStrategy;
use Derafu\Lib\Core\Package\Prime\Component\Mail\Worker\SenderWorker;
use Derafu\Lib\Core\Package\Prime\PrimePackage;
use Derafu\Lib\Core\Support\Store\Abstract\AbstractStore;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Mime\Address;

#[CoversClass(Application::class)]
#[CoversClass(AbstractServiceRegistry::class)]
#[CoversClass(AbstractWorker::class)]
#[CoversClass(Configuration::class)]
#[CoversClass(Kernel::class)]
#[CoversClass(ServiceConfigurationCompilerPass::class)]
#[CoversClass(ServiceProcessingCompilerPass::class)]
#[CoversClass(Selector::class)]
#[CoversClass(AbstractMailerStrategy::class)]
#[CoversClass(MailComponent::class)]
#[CoversClass(Envelope::class)]
#[CoversClass(Message::class)]
#[CoversClass(Postman::class)]
#[CoversClass(SenderWorker::class)]
#[CoversClass(SmtpSenderStrategy::class)]
#[CoversClass(PrimePackage::class)]
#[CoversClass(AbstractStore::class)]
#[CoversClass(DataContainer::class)]
class SendMailTest extends TestCase
{
    private MailComponentInterface $mail;

    protected function setUp(): void
    {
        $app = Application::getInstance();

        $this->mail = $app->getPrimePackage()->getMailComponent();
    }

    public function testSendMail(): void
    {
        $username = getenv('MAIL_USERNAME');
        $password = getenv('MAIL_PASSWORD');
        $from = $username;
        $to = getenv('MAIL_TO') ?: $from;

        if (!$username || !$password) {
            $this->markTestSkipped('No existe configuración para enviar correo.');
        }

        $postman = new Postman([
            'transport' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);

        $envelope = new Envelope(new Address($from), [new Address($to)]);

        $message = new Message();
        $message->subject('Hola Mundo!');
        $message->text('Este es un correo de prueba.');

        $envelope->addMessage($message);
        $postman->addEnvelope($envelope);

        $this->mail->send($postman);

        if ($message->hasError()) {
            $this->fail($message->getError()->getMessage());
        }

        $this->assertFalse($message->hasError());
    }
}
