<?php

declare(strict_types=1);

use Doctrine\Common\Collections\Criteria;

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

return [
    'products' => [
        // Los datos.
        // El 'id' es obligatorio en cada una de las entidades, se agregará
        // automáticamente en el test.
        'data' => [
            'prod-001' => [
                'name' => 'Laptop XPS',
                'category' => 'computers',
                'price' => 1299.99,
                'active' => true,
            ],
            'prod-002' => [
                'name' => 'Magic Mouse',
                'category' => 'accessories',
                'price' => 99.99,
                'active' => true,
            ],
            'prod-003' => [
                'name' => 'Old Keyboard',
                'category' => 'accessories',
                'price' => 29.99,
                'active' => false,
            ],
            'prod-004' => [
                'name' => 'Gaming Mouse',
                'category' => 'accessories',
                'price' => 149.99,
                'active' => true,
            ],
        ],
        // Los casos de prueba.
        'cases' => [
            'find_by_id' => [
                'method' => 'find',
                'args' => ['prod-001'],
                'expected' => [
                    'id' => 'prod-001',
                    'name' => 'Laptop XPS',
                    'category' => 'computers',
                    'price' => 1299.99,
                    'active' => true,
                ],
            ],
            'find_nonexistent' => [
                'method' => 'find',
                'args' => ['prod-999'],
                'expected' => null,
            ],
            'find_all' => [
                'method' => 'findAll',
                'args' => [],
                'expected' => [
                    [
                        'id' => 'prod-001',
                        'name' => 'Laptop XPS',
                        'category' => 'computers',
                        'price' => 1299.99,
                        'active' => true,
                    ],
                    [
                        'id' => 'prod-002',
                        'name' => 'Magic Mouse',
                        'category' => 'accessories',
                        'price' => 99.99,
                        'active' => true,
                    ],
                    [
                        'id' => 'prod-003',
                        'name' => 'Old Keyboard',
                        'category' => 'accessories',
                        'price' => 29.99,
                        'active' => false,
                    ],
                    [
                        'id' => 'prod-004',
                        'name' => 'Gaming Mouse',
                        'category' => 'accessories',
                        'price' => 149.99,
                        'active' => true,
                    ],
                ],
            ],
            'find_active_accessories' => [
                'method' => 'findBy',
                'args' => [
                    ['category' => 'accessories', 'active' => true], // criteria
                    ['price' => 'DESC'],                             // orderBy
                    null,                                            // limit
                    null,                                            // offset
                ],
                'expected' => [
                    [
                        'id' => 'prod-004',
                        'name' => 'Gaming Mouse',
                        'category' => 'accessories',
                        'price' => 149.99,
                        'active' => true,
                    ],
                    [
                        'id' => 'prod-002',
                        'name' => 'Magic Mouse',
                        'category' => 'accessories',
                        'price' => 99.99,
                        'active' => true,
                    ],
                ],
            ],
            'find_with_limit_offset' => [
                'method' => 'findBy',
                'args' => [
                    ['category' => 'accessories'], // criteria
                    ['price' => 'ASC'],            // orderBy
                    2,                             // limit
                    1,                             // offset
                ],
                'expected' => [
                    [
                        'id' => 'prod-002',
                        'name' => 'Magic Mouse',
                        'category' => 'accessories',
                        'price' => 99.99,
                        'active' => true,
                    ],
                    [
                        'id' => 'prod-004',
                        'name' => 'Gaming Mouse',
                        'category' => 'accessories',
                        'price' => 149.99,
                        'active' => true,
                    ],
                ],
            ],
            'find_one_by' => [
                'method' => 'findOneBy',
                'args' => [
                    ['category' => 'computers'], // criteria
                    null,                        // orderBy
                ],
                'expected' => [
                    'id' => 'prod-001',
                    'name' => 'Laptop XPS',
                    'category' => 'computers',
                    'price' => 1299.99,
                    'active' => true,
                ],
            ],
            'find_one_by_no_results' => [
                'method' => 'findOneBy',
                'args' => [
                    ['category' => 'phones'], // criteria que no existe
                    null,                     // orderBy
                ],
                'expected' => null,
            ],
            'count_all' => [
                'method' => 'count',
                'args' => [],
                'expected' => 4,
            ],
            'criteria' => [
                'method' => 'findByCriteria',
                'args' => [
                    Criteria::create()
                        ->where(Criteria::expr()->lte('price', 100))
                    ,
                ],
                'expected' => [
                    [
                        'id' => 'prod-002',
                        'name' => 'Magic Mouse',
                        'category' => 'accessories',
                        'price' => 99.99,
                        'active' => true,
                    ],
                    [
                        'id' => 'prod-003',
                        'name' => 'Old Keyboard',
                        'category' => 'accessories',
                        'price' => 29.99,
                        'active' => false,
                    ],
                ],
            ],
        ],
    ],
];
