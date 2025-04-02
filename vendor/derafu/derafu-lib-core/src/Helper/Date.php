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

namespace Derafu\Lib\Core\Helper;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTime;
use InvalidArgumentException;

/**
 * Clase para trabajar con fecha en PHP.
 *
 * Extiende las funcionalidades de Carbon
 */
class Date extends Carbon
{
    /**
     * Días de la semana en español.
     *
     * @var array
     */
    private const DAYS = [
        'domingo',
        'lunes',
        'martes',
        'miércoles',
        'jueves',
        'viernes',
        'sábado',
    ];

    /**
     * Meses del año en español.
     *
     * @var array
     */
    private const MONTHS = [
        'enero',
        'febrero',
        'marzo',
        'abril',
        'mayo',
        'junio',
        'julio',
        'agosto',
        'septiembre',
        'octubre',
        'noviembre',
        'diciembre',
    ];

    /**
     * Formatea una fecha en formato YYYY-MM-DD a un string en español.
     *
     * @param string $date
     * @param bool $showDay
     * @return string
     */
    public static function formatSpanish(string $date, bool $showDay = true): string
    {
        $unixtime = strtotime($date);
        $string = date('j \d\e \M\O\N\T\H \d\e\l Y', $unixtime);
        if ($showDay) {
            $string = 'DAY ' . $string;
        }
        $day = self::DAYS[date('w', $unixtime)];
        $month = self::MONTHS[date('n', $unixtime) - 1];

        return str_replace(['DAY', 'MONTH'], [$day, $month], $string);
    }

    /**
     * Formatea un periodo pasado en formato YYYYMM a su representación en
     * español del mes.
     *
     * El resultado para 202501 será "Enero de 2025".
     *
     * @param int $period
     * @return string
     */
    public static function formatPeriodSpanish(int $period): string
    {
        // Extraer año y mes.
        $year = substr((string) $period, 0, 4);
        $month = substr((string) $period, 4, 2);

        // Validar el mes.
        $index = (int) $month - 1;
        if (!isset(self::MONTHS[$index])) {
            throw new InvalidArgumentException(sprintf(
                'Mes inválido: %s.',
                $month
            ));
        }

        // Retornar el mes y el año formateados.
        return ucfirst(self::MONTHS[$index]) . ' de ' . $year;
    }

    /**
     * Valida si la fecha es o no válida según el formato.
     *
     * @param string $date Fecha que se quiere validar.
     * @param string $format Formato que se quiere validar.
     * @return bool `true` Si la fecha está ok.
     */
    public static function validate(
        string $date,
        string $format = 'Y-m-d'
    ): bool {
        $dt = DateTime::createFromFormat($format, $date);

        return $dt !== false && !array_sum($dt->getLastErrors());
    }

    /**
     * Valida si una fecha está en el formato Y-m-d y la convierte a un nuevo
     * formato.
     *
     * @param string $date
     * @return string|null
     */
    public static function validateAndConvert(
        string $date,
        string $format = 'd/m/Y'
    ): ?string {
        try {

            $carbonDate = self::createFromFormat('Y-m-d', $date);
            if (
                $carbonDate === null
                || $carbonDate->format('Y-m-d') !== $date
                || $carbonDate->getLastErrors()['error_count'] > 0
            ) {
                return null;
            }
            return $carbonDate->format($format);
        } catch (InvalidFormatException $e) {
            return null;
        }
    }

    /**
     * Entrega el último día de un período.
     *
     * @param int|null $period
     * @return string
     */
    public static function lastDayPeriod(?int $period = null): string
    {
        if ($period === null) {
            $period = date('Ym');
        }

        $year = substr((string) $period, 0, 4);
        $month = substr((string) $period, 4, 2);

        $date = DateTime::createFromFormat('Y-m-d', "$year-$month-01");
        $lastDay = $date->format('t');

        return "$year-$month-$lastDay";
    }
}
