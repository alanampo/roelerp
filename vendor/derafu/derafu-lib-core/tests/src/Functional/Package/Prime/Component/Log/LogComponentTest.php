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

namespace Derafu\Lib\Tests\Functional\Package\Prime\Component\Log;

use Derafu\Lib\Core\Helper\Selector;
use Derafu\Lib\Core\Package\Prime\Component\Log\Contract\LogComponentInterface;
use Derafu\Lib\Core\Package\Prime\Component\Log\Contract\LoggerWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Log\Entity\Caller;
use Derafu\Lib\Core\Package\Prime\Component\Log\Entity\Level;
use Derafu\Lib\Core\Package\Prime\Component\Log\Entity\Log;
use Derafu\Lib\Core\Package\Prime\Component\Log\LogComponent;
use Derafu\Lib\Core\Package\Prime\Component\Log\Worker\JournalHandler;
use Derafu\Lib\Core\Package\Prime\Component\Log\Worker\LineFormatter;
use Derafu\Lib\Core\Package\Prime\Component\Log\Worker\LoggerWorker;
use Derafu\Lib\Core\Package\Prime\Component\Log\Worker\Processor;
use Derafu\Lib\Core\Support\Store\Abstract\AbstractStore;
use Derafu\Lib\Core\Support\Store\DataContainer;
use Derafu\Lib\Core\Support\Store\Journal;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LogLevel as PsrLogLevel;

#[CoversClass(LogComponent::class)]
#[CoversClass(Level::class)]
#[CoversClass(Caller::class)]
#[CoversClass(Level::class)]
#[CoversClass(LineFormatter::class)]
#[CoversClass(LoggerWorker::class)]
#[CoversClass(Processor::class)]
#[CoversClass(Log::class)]
#[CoversClass(JournalHandler::class)]
#[CoversClass(AbstractStore::class)]
#[CoversClass(Journal::class)]
#[CoversClass(Selector::class)]
#[CoversClass(DataContainer::class)]
class LogComponentTest extends TestCase
{
    private LogComponentInterface $logComponent;

    private LoggerWorkerInterface $logger;

    private static array $testCases = [
        'messages' => [
            Level::DEBUG => 'Test DEBUG message.',
            Level::INFO => 'Test INFO message.',
            Level::NOTICE => 'Test NOTICE message.',
            Level::WARNING => 'Test WARNING message.',
            Level::ERROR => 'Test ERROR message.',
            Level::CRITICAL => 'Test CRITICAL message.',
            Level::ALERT => 'Test ALERT message.',
            Level::EMERGENCY => 'Test EMERGENCY message.',
        ],
        'levels' => [
            'PHP' => [
                Level::DEBUG => LOG_DEBUG,
                Level::INFO => LOG_INFO,
                Level::NOTICE => LOG_NOTICE,
                Level::WARNING => LOG_WARNING,
                Level::ERROR => LOG_ERR,
                Level::CRITICAL => LOG_CRIT,
                Level::ALERT => LOG_ALERT,
                Level::EMERGENCY => LOG_EMERG,
            ],
            'PSR-3' => [
                Level::DEBUG => PsrLogLevel::DEBUG,
                Level::INFO => PsrLogLevel::INFO,
                Level::NOTICE => PsrLogLevel::NOTICE,
                Level::WARNING => PsrLogLevel::WARNING,
                Level::ERROR => PsrLogLevel::ERROR,
                Level::CRITICAL => PsrLogLevel::CRITICAL,
                Level::ALERT => PsrLogLevel::ALERT,
                Level::EMERGENCY => PsrLogLevel::EMERGENCY,
            ],
            'LogComponent' => [
                Level::DEBUG => Level::DEBUG,
                Level::INFO => Level::INFO,
                Level::NOTICE => Level::NOTICE,
                Level::WARNING => Level::WARNING,
                Level::ERROR => Level::ERROR,
                Level::CRITICAL => Level::CRITICAL,
                Level::ALERT => Level::ALERT,
                Level::EMERGENCY => Level::EMERGENCY,
            ],
        ],
    ];

    protected function setUp(): void
    {
        $loggerWorker = new LoggerWorker(
            formatter: new LineFormatter()
        );
        $this->logComponent = new LogComponent($loggerWorker);
        $this->logger = $this->logComponent->getLoggerWorker();
    }

    public static function provideStandardLevelsAndMessages(): array
    {
        $cases = [];

        foreach (self::$testCases['messages'] as $level => $message) {
            $cases[$message . ' (' . $level . ')'] = [
                $level,
                $message,
            ];
        }

        return $cases;
    }

    public static function provideCustomLevelsAndMessages(): array
    {
        $cases = [];

        foreach (self::$testCases['levels'] as $levelType => $testCases) {
            foreach ($testCases as $id => $level) {
                $message = self::$testCases['messages'][$id];
                $cases[$message . ' (' . $levelType . ': ' . $level . ')'] = [
                    $level,
                    $message,
                ];
            }
        }

        return $cases;
    }

    #[DataProvider('provideStandardLevelsAndMessages')]
    public function testLogWithStandardLevelAndMessage(int $level, string $message): void
    {
        switch ($level) {
            case Level::EMERGENCY:
                $this->logger->emergency($message);
                break;
            case Level::ALERT:
                $this->logger->alert($message);
                break;
            case Level::CRITICAL:
                $this->logger->critical($message);
                break;
            case Level::ERROR:
                $this->logger->error($message);
                break;
            case Level::WARNING:
                $this->logger->warning($message);
                break;
            case Level::NOTICE:
                $this->logger->notice($message);
                break;
            case Level::INFO:
                $this->logger->info($message);
                break;
            case Level::DEBUG:
                $this->logger->debug($message);
                break;
        }

        $logs = $this->logComponent->getLogs();
        $this->assertCount(1, $logs);

        $logRecord = $logs[0];
        $this->assertInstanceOf(Log::class, $logRecord);
        $this->assertSame($level, $logRecord->code);
        $this->assertSame($message, $logRecord->message);
    }

    #[DataProvider('provideCustomLevelsAndMessages')]
    public function testLogWithCustomLevelAndMessage($level, $message): void
    {
        $this->logger->log($level, $message);

        $level = (new Level($level))->getCode();
        $logs = $this->logComponent->getLogs($level);

        $this->assertCount(1, $logs);

        $logRecord = $logs[0];
        $this->assertInstanceOf(Log::class, $logRecord);
        $this->assertSame($level, $logRecord->code);
        $this->assertSame($message, $logRecord->message);
    }

    public function testLogWithCaller(): void
    {

        $this->logger->error('With caller error message');

        $logs = $this->logComponent->getLogs(Level::ERROR);
        $this->assertCount(1, $logs);

        $logRecord = $logs[0];
        $this->assertInstanceOf(Log::class, $logRecord);
        $this->assertSame(Level::ERROR, $logRecord->code);
        $this->assertSame('With caller error message', $logRecord->message);
        $this->assertNotNull($logRecord->caller);
        $this->assertSame('testLogWithCaller', $logRecord->caller->function);
    }

    public function testFlushLogs(): void
    {
        $this->logger->error('First error message');
        $this->logger->warning('First warning message');
        $this->logger->error('Second error message');

        $logs = $this->logComponent->flushLogs(Level::ERROR);

        $this->assertCount(2, $logs);
        $this->assertSame('Second error message', $logs[0]->message);
        $this->assertSame('First error message', $logs[1]->message);

        $logsAfterFlush = $this->logComponent->getLogs(Level::ERROR);
        $this->assertEmpty($logsAfterFlush);
    }

    public function testClearLogs(): void
    {
        $this->logger->error('Error message');
        $this->logComponent->clearLogs(Level::ERROR);

        $logs = $this->logComponent->getLogs(Level::ERROR);
        $this->assertEmpty($logs);
    }

    public function testClearAllLogs(): void
    {
        $this->logger->error('Error message');
        $this->logger->warning('Warning message');
        $this->logComponent->clearLogs();

        $allLogs = $this->logComponent->getLogs();
        $this->assertEmpty($allLogs);
    }

    public function testLogWithContext(): void
    {
        $context = ['key' => 'value'];
        $this->logger->info('Info message with context', $context);

        $logs = $this->logComponent->getLogs(Level::INFO);
        $this->assertCount(1, $logs);

        $logRecord = $logs[0];
        $this->assertSame($context, $logRecord->context);
    }

    // Se verifica que lo que se escriba al log se pueda leer todo de vuelta.
    public function testWriteReadAll(): void
    {
        // Log que se probará.
        $cases = [
            Level::ERROR => [
                'Error N° 1',
                'Ejemplo error dos',
                'Este es el tercer error',
            ],
            Level::WARNING => [
                'Este es el primer warning',
                'Un segundo warning',
                'El penúltimo warning',
                'El warning final (4to)',
            ],
        ];

        // Se verificará leyendo el log en ambos ordenes (más nuevo a más viejo
        // y más viejo a más nuevo).
        foreach ([true, false] as $newFirst) {

            // Escribir al log.
            foreach ($cases as $level => $messages) {
                foreach ($messages as $contextCode => $message) {
                    if ($level === Level::ERROR) {
                        $this->logger->error(
                            $message,
                            [
                                'code' => $contextCode,
                            ]
                        );
                    } elseif ($level === Level::WARNING) {
                        $this->logger->warning(
                            $message,
                            [
                                'code' => $contextCode,
                            ]
                        );
                    }
                }
            }

            // Revisar lo que se escribió al log.
            foreach ($cases as $level => $messages) {
                $logs = $this->logComponent->flushLogs($level, $newFirst);
                $this->assertNotEmpty($logs);
                $this->assertCount(count($cases[$level]), $logs);

                if ($newFirst) {
                    krsort($messages);
                }

                foreach ($messages as $contextCode => $message) {
                    $log = array_shift($logs);
                    $this->assertSame(
                        $contextCode,
                        $log->context['code'] ?? null
                    );
                    $this->assertSame($message, $log->message);
                }
            }
        }
    }
}
