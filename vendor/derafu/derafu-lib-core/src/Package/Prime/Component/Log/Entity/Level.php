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

namespace Derafu\Lib\Core\Package\Prime\Component\Log\Entity;

use InvalidArgumentException;
use LogicException;
use Monolog\Level as MonologLevel;
use Psr\Log\LogLevel as PsrLogLevel;

/**
 * Clase para los niveles de registros de la bitácora.
 */
class Level
{
    /**
     * Información de depuración detallada.
     *
     * @var int
     */
    public const DEBUG = MonologLevel::Debug->value;

    /**
     * Eventos de interés.
     *
     * Ejemplos:
     *
     *   - Registro de inicios de sesión de usuario.
     *   - Registro de consultas SQL.
     *
     * @var int
     */
    public const INFO = MonologLevel::Info->value;

    /**
     * Registros de eventos poco comúnes.
     *
     * @var int
     */
    public const NOTICE = MonologLevel::Notice->value;

    /**
     * Eventos excepcionales que no son errores.
     *
     * Ejemplos:
     *
     *   - Usos de APIs obsoletas.
     *   - Usos desaconsejados ("pobre") de APIs.
     *   - Usos no deseables o llamadas "poco correctas" que no necesariamente
     *     están mal pero podrían ser hechas de mejor forma.
     *
     * @var int
     */
    public const WARNING = MonologLevel::Warning->value;

    /**
     * Errores de tiempo de ejecución.
     */
    public const ERROR = MonologLevel::Error->value;

    /**
     * Condiciones críticas.
     */
    public const CRITICAL = MonologLevel::Critical->value;

    /**
     * Alertas que requieren que una acción sea tomada de manera inmediata.
     *
     * Ejemplos:
     *
     *   - Aplicación caída.
     *   - Tests que no pasan.
     *   - Fuentes de datos no disponibles.
     *
     * Esto debe lanzar una alerta que si o si debe llegar a "alguien" para que
     * la revise ASAP (as soon as possible).
     */
    public const ALERT = MonologLevel::Alert->value;

    /**
     * Alertas urgentes.
     */
    public const EMERGENCY = MonologLevel::Emergency->value;

    /**
     * Mapeo de niveles de logs de diferentes sistemas o fuentes a los niveles
     * usados por el servicio de bitácoras de la biblioteca.
     *
     * @var array<int|string,int>
     */
    private const LEVELS = [
        // Logs de PHP / RFC5424 (?).
        LOG_DEBUG => self::DEBUG,
        LOG_INFO => self::INFO,
        LOG_NOTICE => self::NOTICE,
        LOG_WARNING => self::WARNING,
        LOG_ERR => self::ERROR,
        LOG_CRIT => self::CRITICAL,
        LOG_ALERT => self::ALERT,
        LOG_EMERG => self::EMERGENCY,

        // Logs de PSR-3.
        PsrLogLevel::DEBUG => self::DEBUG,
        PsrLogLevel::INFO => self::INFO,
        PsrLogLevel::NOTICE => self::NOTICE,
        PsrLogLevel::WARNING => self::WARNING,
        PsrLogLevel::ERROR => self::ERROR,
        PsrLogLevel::CRITICAL => self::CRITICAL,
        PsrLogLevel::ALERT => self::ALERT,
        PsrLogLevel::EMERGENCY => self::EMERGENCY,
    ];

    /**
     * Código del nivel.
     *
     * @var integer
     */
    private int $code;

    /**
     * Constructor de la entidad.
     *
     * @param integer|string $code
     */
    public function __construct(int|string $code)
    {
        $this->setCode($code);
    }

    /**
     * Asigna el código de nivel del registro de la bitácora.
     *
     * @param int|string $code Código de nivel en cualquier formato soportado.
     * @return static
     * @throws LogicException Cuando el nivel es un string no soportado.
     */
    private function setCode(int|string $code): static
    {
        if (isset(self::LEVELS[$code])) {
            $this->code = self::LEVELS[$code];

            return $this;
        }

        if (is_string($code)) {
            throw new LogicException(sprintf(
                'El nivel de registro de bitácora (logging) %s no está soportado.',
                $code
            ));
        }

        $this->code = $code;

        return $this;
    }

    /**
     * Entrega el código de nivel.
     *
     * El código se entrega normalizado.
     *
     * @return integer
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Obtiene el nombre del nivel.
     *
     * @return string Nombre del nivel.
     */
    public function getName(): string
    {
        return $this->getMonologLevel()->name;
    }

    /**
     * Obtiene la instancia "enum" del nivel.
     *
     * @return MonologLevel
     */
    public function getMonologLevel(): MonologLevel
    {
        $level = MonologLevel::tryFrom($this->code);

        if ($level === null) {
            throw new InvalidArgumentException(sprintf(
                'El código de nivel de log %d es inválido como nivel de Monolog.',
                $this->code
            ));
        }

        return $level;
    }
}
