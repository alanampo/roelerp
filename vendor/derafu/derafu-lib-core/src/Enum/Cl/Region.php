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

namespace Derafu\Lib\Core\Enum\Cl;

/**
 * Enum para representar las regiones de Chile.
 */
enum Region: int
{
    case TARAPACA = 1;
    case ANTOFAGASTA = 2;
    case ATACAMA = 3;
    case COQUIMBO = 4;
    case VALPARAISO = 5;
    case O_HIGGINS = 6;
    case MAULE = 7;
    case BIOBIO = 8;
    case ARAUCANIA = 9;
    case LOS_LAGOS = 10;
    case AYSEN = 11;
    case MAGALLANES = 12;
    case METROPOLITANA = 13;
    case LOS_RIOS = 14;
    case ARICA_PARINACOTA = 15;
    case NUBLE = 16;

    private const GLOSAS = [
        self::TARAPACA->value => 'Tarapacá',
        self::ANTOFAGASTA->value => 'Antofagasta',
        self::ATACAMA->value => 'Atacama',
        self::COQUIMBO->value => 'Coquimbo',
        self::VALPARAISO->value => 'Valparaíso',
        self::O_HIGGINS->value => 'Libertador General Bernardo O’Higgins',
        self::MAULE->value => 'Maule',
        self::BIOBIO->value => 'Biobío',
        self::ARAUCANIA->value => 'Araucanía',
        self::LOS_LAGOS->value => 'Los Lagos',
        self::AYSEN->value => 'Aysén',
        self::MAGALLANES->value => 'Magallanes',
        self::METROPOLITANA->value => 'Metropolitana',
        self::LOS_RIOS->value => 'Los Ríos',
        self::ARICA_PARINACOTA->value => 'Arica y Parinacota',
        self::NUBLE->value => 'Ñuble',
    ];

    /**
     * Entrega el código de la región normalizado a 2 dígitos.
     *
     * @return string
     */
    public function getCodigo(): string
    {
        return $this->value < 10 ? '0' . $this->value : (string) $this->value;
    }

    /**
     * Obtiene la glosa asociada a una región.
     *
     * @return string
     */
    public function getGlosa(): string
    {
        return self::GLOSAS[$this->value];
    }

    /**
     * Obtiene el nombre de la región.
     *
     * @return string
     */
    public function getNombre(): string
    {
        return $this->getGlosa();
    }

    /**
     * Entrega las provincias asociadas a la región.
     *
     * Los 2 primeros dígitos del código normalizado de la provincia
     * corresponden al código normalizado de la región.
     *
     * @return Provincia[]
     */
    public function getProvincias(): array
    {
        return array_filter(
            Provincia::cases(),
            fn ($provincia) =>
                substr($provincia->getCodigo(), 0, 2) === $this->getCodigo()
        );
    }

    /**
     * Entrega las comunas asociadas a la región.
     *
     * Los 2 primeros dígitos del código normalizado de la comuna
     * corresponden al código normalizado de la región.
     *
     * @return Comuna[]
     */
    public function getComunas(): array
    {
        return array_filter(
            Comuna::cases(),
            fn ($comuna) =>
                substr($comuna->getCodigo(), 0, 2) === $this->getCodigo()
        );
    }
}
