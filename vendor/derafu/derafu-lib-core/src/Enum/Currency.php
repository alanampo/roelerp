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

namespace Derafu\Lib\Core\Enum;

/**
 * Enum con monedas en estándar ISO 4217.
 */
enum Currency: string
{
    /**
     * Pesos chilenos.
     */
    case CLP = 'CLP';

    /**
     * Unidad de Fomento de Chile (UF).
     */
    case CLF = 'CLF';

    /**
     * Unidad Tributaria Mensual de Chile.
     */
    case UTM = 'UTM';

    /**
     * Unidad Tributaria Anual de Chile.
     */
    case UTA = 'UTA';

    /**
     * Dólar de Estados Unidos.
     */
    case USD = 'USD';

    /**
     * Euro.
     */
    case EUR = 'EUR';

    /**
     * I love Bitcoin <3
     */
    case BTC = 'BTC';

    /**
     * Peso argentino.
     */
    case ARS = 'ARS';

    /**
     * Libra esterlina.
     */
    case GBP = 'GBP';

    /**
     * Corona sueca.
     */
    case SEK = 'SEK';

    /**
     * Dólar de Hong Kong.
     */
    case HKD = 'HKD';

    /**
     * Rand sudafricano.
     */
    case ZAR = 'ZAR';

    /**
     * Peso colombiano.
     */
    case COP = 'COP';

    /**
     * Peso mexicano.
     */
    case MXN = 'MXN';

    /**
     * Bolívar venezolano.
     */
    case VES = 'VES';

    /**
     * Dólar de Singapur.
     */
    case SGD = 'SGD';

    /**
     * Rupia india.
     */
    case INR = 'INR';

    /**
     * Nuevo dólar taiwanés.
     */
    case TWD = 'TWD';

    /**
     * Dirham de Emiratos Árabes Unidos.
     */
    case AED = 'AED';

    /**
     * Won surcoreano.
     */
    case KRW = 'KRW';

    /**
     * Zloty polaco.
     */
    case PLN = 'PLN';

    /**
     * Corona checa.
     */
    case CZK = 'CZK';

    /**
     * Forint húngaro.
     */
    case HUF = 'HUF';

    /**
     * Baht tailandés.
     */
    case THB = 'THB';

    /**
     * Lira turca.
     */
    case TRY = 'TRY';

    /**
     * Ringgit malayo.
     */
    case MYR = 'MYR';

    /**
     * Rublo ruso.
     */
    case RUB = 'RUB';

    /**
     * Rupia indonesia.
     */
    case IDR = 'IDR';

    /**
     * Grivna ucraniana.
     */
    case UAH = 'UAH';

    /**
     * Shekel israelí.
     */
    case ILS = 'ILS';

    /**
     * Peso filipino.
     */
    case PHP = 'PHP';

    /**
     * Riyal saudí.
     */
    case SAR = 'SAR';

    /**
     * Rupia pakistaní.
     */
    case PKR = 'PKR';

    /**
     * Dong vietnamita.
     */
    case VND = 'VND';

    /**
     * Libra egipcia.
     */
    case EGP = 'EGP';

    /**
     * Leu rumano.
     */
    case RON = 'RON';

    /**
     * Corona islandesa.
     */
    case ISK = 'ISK';

    /**
     * Rial iraní.
     */
    case IRR = 'IRR';

    /**
     * Colón costarricense.
     */
    case CRC = 'CRC';

    /**
     * Balboa panameño.
     */
    case PAB = 'PAB';

    /**
     * Guaraní paraguayo.
     */
    case PYG = 'PYG';

    /**
     * Sol peruano.
     */
    case PEN = 'PEN';

    /**
     * Peso uruguayo.
     */
    case UYU = 'UYU';

    /**
     * Dólar australiano.
     */
    case AUD = 'AUD';

    /**
     * Boliviano.
     */
    case BOB = 'BOB';

    /**
     * Yuan chino.
     */
    case CNY = 'CNY';

    /**
     * Real brasileño.
     */
    case BRL = 'BRL';

    /**
     * Corona danesa.
     */
    case DKK = 'DKK';

    /**
     * Dólar canadiense.
     */
    case CAD = 'CAD';

    /**
     * Yen japonés.
     */
    case JPY = 'JPY';

    /**
     * Franco suizo.
     */
    case CHF = 'CHF';

    /**
     * Corona noruega.
     */
    case NOK = 'NOK';

    /**
     * Dólar neozelandés.
     */
    case NZD = 'NZD';

    /**
     * Monedas no especificadas.
     *
     * En estricto rigor ISO 4217 define XXX como "Sin divisa".
     */
    case XXX = 'XXX';

    /**
     * Nombres de las monedas.
     *
     * Si un nombre no está definido se entregará el código estándar ISO 4217.
     *
     * @var array<string, array<string, string>>
     */
    private const NAMES = [
        // Nombres de monedas en español.
        'es' => [
            self::CLP->value => 'Peso chileno',
            self::CLF->value => 'Unidad de fomento de Chile',
            self::UTM->value => 'Unidad tributaria mensual de Chile',
            self::UTA->value => 'Unidad tributaria anual de Chile',
            self::USD->value => 'Dólar estadounidense',
            self::EUR->value => 'Euro',
            self::BTC->value => 'Bitcoin',
            self::ARS->value => 'Peso argentino',
            self::GBP->value => 'Libra esterlina',
            self::SEK->value => 'Corona sueca',
            self::HKD->value => 'Dólar de Hong Kong',
            self::ZAR->value => 'Rand sudafricano',
            self::COP->value => 'Peso colombiano',
            self::MXN->value => 'Peso mexicano',
            self::VES->value => 'Bolívar venezolano',
            self::SGD->value => 'Dólar de Singapur',
            self::INR->value => 'Rupia india',
            self::TWD->value => 'Nuevo dólar taiwanés',
            self::AED->value => 'Dirham de Emiratos Árabes Unidos',
            self::KRW->value => 'Won surcoreano',
            self::PLN->value => 'Zloty polaco',
            self::CZK->value => 'Corona checa',
            self::HUF->value => 'Forint húngaro',
            self::THB->value => 'Baht tailandés',
            self::TRY->value => 'Lira turca',
            self::MYR->value => 'Ringgit malayo',
            self::RUB->value => 'Rublo ruso',
            self::IDR->value => 'Rupia indonesia',
            self::UAH->value => 'Grivna ucraniana',
            self::ILS->value => 'Shekel israelí',
            self::PHP->value => 'Peso filipino',
            self::SAR->value => 'Riyal saudí',
            self::PKR->value => 'Rupia pakistaní',
            self::VND->value => 'Dong vietnamita',
            self::EGP->value => 'Libra egipcia',
            self::RON->value => 'Leu rumano',
            self::ISK->value => 'Corona islandesa',
            self::IRR->value => 'Rial iraní',
            self::CRC->value => 'Colón costarricense',
            self::PAB->value => 'Balboa panameño',
            self::PYG->value => 'Guaraní paraguayo',
            self::PEN->value => 'Sol peruano',
            self::UYU->value => 'Peso uruguayo',
            self::AUD->value => 'Dólar australiano',
            self::BOB->value => 'Boliviano',
            self::CNY->value => 'Yuan chino',
            self::BRL->value => 'Real brasileño',
            self::DKK->value => 'Corona danesa',
            self::CAD->value => 'Dólar canadiense',
            self::JPY->value => 'Yen japonés',
            self::CHF->value => 'Franco suizo',
            self::NOK->value => 'Corona noruega',
            self::NZD->value => 'Dólar neozelandés',
            self::XXX->value => 'Sin divisa',
        ],
        // Nombres de monedas en Inglés.
        'en' => [
            self::CLP->value => 'Chilean Peso',
            self::CLF->value => 'Chilean Unit of Account (UF)',
            self::UTM->value => 'Monthly Tax Unit of Chile',
            self::UTA->value => 'Annual Tax Unit of Chile',
            self::USD->value => 'United States Dollar',
            self::EUR->value => 'Euro',
            self::BTC->value => 'Bitcoin',
            self::ARS->value => 'Argentine Peso',
            self::GBP->value => 'British Pound',
            self::SEK->value => 'Swedish Krona',
            self::HKD->value => 'Hong Kong Dollar',
            self::ZAR->value => 'South African Rand',
            self::COP->value => 'Colombian Peso',
            self::MXN->value => 'Mexican Peso',
            self::VES->value => 'Venezuelan Bolívar',
            self::SGD->value => 'Singapore Dollar',
            self::INR->value => 'Indian Rupee',
            self::TWD->value => 'New Taiwan Dollar',
            self::AED->value => 'United Arab Emirates Dirham',
            self::KRW->value => 'South Korean Won',
            self::PLN->value => 'Polish Zloty',
            self::CZK->value => 'Czech Koruna',
            self::HUF->value => 'Hungarian Forint',
            self::THB->value => 'Thai Baht',
            self::TRY->value => 'Turkish Lira',
            self::MYR->value => 'Malaysian Ringgit',
            self::RUB->value => 'Russian Ruble',
            self::IDR->value => 'Indonesian Rupiah',
            self::UAH->value => 'Ukrainian Hryvnia',
            self::ILS->value => 'Israeli Shekel',
            self::PHP->value => 'Philippine Peso',
            self::SAR->value => 'Saudi Riyal',
            self::PKR->value => 'Pakistani Rupee',
            self::VND->value => 'Vietnamese Dong',
            self::EGP->value => 'Egyptian Pound',
            self::RON->value => 'Romanian Leu',
            self::ISK->value => 'Icelandic Krona',
            self::IRR->value => 'Iranian Rial',
            self::CRC->value => 'Costa Rican Colón',
            self::PAB->value => 'Panamanian Balboa',
            self::PYG->value => 'Paraguayan Guarani',
            self::PEN->value => 'Peruvian Sol',
            self::UYU->value => 'Uruguayan Peso',
            self::AUD->value => 'Australian Dollar',
            self::BOB->value => 'Bolivian Boliviano',
            self::CNY->value => 'Chinese Yuan',
            self::BRL->value => 'Brazilian Real',
            self::DKK->value => 'Danish Krone',
            self::CAD->value => 'Canadian Dollar',
            self::JPY->value => 'Japanese Yen',
            self::CHF->value => 'Swiss Franc',
            self::NOK->value => 'Norwegian Krone',
            self::NZD->value => 'New Zealand Dollar',
            self::XXX->value => 'No Currency',
        ],
    ];

    /**
     * Símbolos de las monedas.
     *
     * @var array<string, string>
     */
    private const SYMBOLS = [
        self::CLP->value => '$',
        self::CLF->value => 'UF',
        self::UTM->value => 'UTM',
        self::UTA->value => 'UTA',
        self::USD->value => '$',
        self::EUR->value => '€',
        self::BTC->value => '₿',
        self::ARS->value => '$',
        self::GBP->value => '£',
        self::SEK->value => 'kr',
        self::HKD->value => 'HK$',
        self::ZAR->value => 'R',
        self::COP->value => '$',
        self::MXN->value => '$',
        self::VES->value => 'Bs.',
        self::SGD->value => 'S$',
        self::INR->value => '₹',
        self::TWD->value => 'NT$',
        self::AED->value => 'د.إ',
        self::KRW->value => '₩',
        self::PLN->value => 'zł',
        self::CZK->value => 'Kč',
        self::HUF->value => 'Ft',
        self::THB->value => '฿',
        self::TRY->value => '₺',
        self::MYR->value => 'RM',
        self::RUB->value => '₽',
        self::IDR->value => 'Rp',
        self::UAH->value => '₴',
        self::ILS->value => '₪',
        self::PHP->value => '₱',
        self::SAR->value => '﷼',
        self::PKR->value => '₨',
        self::VND->value => '₫',
        self::EGP->value => '£',
        self::RON->value => 'lei',
        self::ISK->value => 'kr',
        self::IRR->value => '﷼',
        self::CRC->value => '₡',
        self::PAB->value => 'B/.',
        self::PYG->value => '₲',
        self::PEN->value => 'S/',
        self::UYU->value => '$U',
        self::AUD->value => 'A$',
        self::BOB->value => 'Bs.',
        self::CNY->value => '¥',
        self::BRL->value => 'R$',
        self::DKK->value => 'kr',
        self::CAD->value => 'C$',
        self::JPY->value => '¥',
        self::CHF->value => 'CHF',
        self::NOK->value => 'kr',
        self::NZD->value => 'NZ$',
        self::XXX->value => '',
    ];

    /**
     * Cantidad de decimales que cada moneda puede tener.
     *
     * Si un decimal no está definido se entregará "2" por defecto.
     *
     * @var array<string, int>
     */
    private const DECIMALS = [
        self::CLP->value => 0,
        self::UTM->value => 0,
        self::UTA->value => 0,
        self::BTC->value => 8,
        self::KRW->value => 0,
        self::VND->value => 0,
        self::ISK->value => 0,
        self::PYG->value => 0,
        self::JPY->value => 0,
    ];

    /**
     * Separadores de decimal de las monedas.
     *
     * Si no está definido el separador se entregará "." por defecto.
     *
     * @var array <string, string>
     */
    private const DECIMAL_SEPARATORS = [
        self::CLP->value => ',',
        self::CLF->value => ',',
        self::UTM->value => ',',
        self::UTA->value => ',',
    ];

    /**
     * Separadores de miles de las monedas.
     *
     * Si no está definido el separador se entregará "," por defecto.
     *
     * @var array <string, string>
     */
    private const THOUSANDS_SEPARATORS = [
        self::CLP->value => '.',
        self::CLF->value => '.',
        self::UTM->value => '.',
        self::UTA->value => '.',
    ];

    /**
     * Plantillas para el renderizado de un monto de las monedas.
     *
     * Si no existe una plantilla se entregará usando la plantilla estándar:
     *
     *   `{{symbol}} {{amount}}`
     *
     * @var array <string, string>
     */
    private const TEMPLATES = [
        self::CLF->value => '{{amount}} {{symbol}}',
        self::UTM->value => '{{amount}} {{symbol}}',
        self::UTA->value => '{{amount}} {{symbol}}',
        self::EUR->value => '{{amount}} {{symbol}}',
        self::JPY->value => '{{symbol}}{{amount}}',
        self::GBP->value => '{{symbol}}{{amount}}',
        self::CHF->value => '{{amount}} {{symbol}}',
        self::CNY->value => '{{symbol}}{{amount}}',
        self::SEK->value => '{{amount}} {{symbol}}',
        self::DKK->value => '{{amount}} {{symbol}}',
        self::PLN->value => '{{amount}} {{symbol}}',
        self::CZK->value => '{{amount}} {{symbol}}',
        self::HUF->value => '{{amount}} {{symbol}}',
        self::THB->value => '{{symbol}}{{amount}}',
        self::VND->value => '{{amount}}{{symbol}}',
        self::KRW->value => '{{symbol}}{{amount}}',
    ];

    /**
     * Entrega el código de la moneda en estándar ISO 4217.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->name;
    }

    /**
     * Entrega el nombre de la moneda.
     *
     * @param string $language
     * @return string
     */
    public function getName(string $language = 'es'): string
    {
        $name = self::NAMES[$language][$this->value] ?? null;

        if ($name !== null) {
            return $name;
        }

        if ($language !== 'en') {
            return $this->getName('en');
        }

        return $this->getCode();
    }

    /**
     * Entrega el símbolo de la moneda.
     *
     * @return string
     */
    public function getSymbol(): string
    {
        return self::SYMBOLS[$this->value];
    }

    /**
     * Entrega la cantidad de decimales de la moneda.
     *
     * @return int
     */
    public function getDecimals(): int
    {
        return self::DECIMALS[$this->value] ?? 2;
    }

    /**
     * Entrega el separador de decimal que usa la moneda.
     *
     * @return string
     */
    public function getDecimalSeparator(): string
    {
        return self::DECIMAL_SEPARATORS[$this->value] ?? '.';
    }

    /**
     * Entrega el separado de miles que usa la moneda.
     *
     * @return string
     */
    public function getThousandsSeparator(): string
    {
        return self::THOUSANDS_SEPARATORS[$this->value] ?? ',';
    }

    /**
     * Valida si un monto es correcto para la moneda según sus decimales.
     *
     * @param int|float $amount
     * @return bool
     */
    public function isValidAmount(int|float $amount): bool
    {
        $decimals = $this->getDecimals();
        $factor = pow(10, $decimals);

        return (floor($amount * $factor) === $amount * $factor);
    }

    /**
     * Aproxima el monto según los decimales que la moneda tiene.
     *
     * @param int|float $amount
     * @return int|float
     */
    public function round(int|float $amount): int|float
    {
        $decimals = $this->getDecimals();

        $roundedAmount = round((float) $amount, $decimals);

        return $decimals === 0 ? (int) $roundedAmount : $roundedAmount;
    }

    /**
     * Formatea como string el monto de una moneda.
     *
     * Redondea a los decimales de la moneda y le da formanto usando el
     * separador decimal y de miles de la moneda.
     *
     * @param int|float $amount
     * @return string
     */
    public function format(int|float $amount): string
    {
        $roundedAmount = $this->round($amount);

        return number_format(
            (float) $roundedAmount,
            $this->getDecimals(),
            $this->getDecimalSeparator(),
            $this->getThousandsSeparator()
        );
    }

    /**
     * Renderiza el monto de la moneda usando su plantilla.
     *
     * Entrega un string con el monto aproximado a los decimales de la moneda y
     * el símbolo en el formato en que se usa en la moneda.
     *
     * @param int|float $amount
     * @return string
     */
    public function render(int|float $amount): string
    {
        $template = $this->getTemplate();

        $formatedAmount = $this->format($amount);

        return str_replace(
            ['{{symbol}}', '{{amount}}'],
            [$this->getSymbol(), $formatedAmount],
            $template
        );
    }

    /**
     * Entrega la plantilla que se debe usar para renderizar un monto de la
     * moneda.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return self::TEMPLATES[$this->value] ?? '{{symbol}} {{amount}}';
    }

    /**
     * Devuelve un array con la información completa de la moneda.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'symbol' => $this->getSymbol(),
            'decimals' => $this->getDecimals(),
            'decimal_separator' => $this->getDecimalSeparator(),
            'thousands_separator' => $this->getThousandsSeparator(),
            'template' => $this->getTemplate(),
        ];
    }
}
