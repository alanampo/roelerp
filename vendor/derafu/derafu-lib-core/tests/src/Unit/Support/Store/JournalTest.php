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

namespace Derafu\Lib\Tests\Unit\Support\Store;

use Derafu\Lib\Core\Support\Store\Journal;
use Derafu\Lib\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(Journal::class)]
class JournalTest extends TestCase
{
    private Journal $journal;

    protected function setUp(): void
    {
        $this->journal = new Journal();
    }

    public function testJournalAddAndRetrieveElements(): void
    {
        // Agregar elementos.
        $this->journal->add('first');
        $this->journal->add('second');
        $this->journal->add('third');

        // Verificar orden normal (más antiguo a más nuevo).
        $this->assertSame(
            ['first', 'second', 'third'],
            $this->journal->all()
        );

        // Verificar orden inverso (más nuevo a más antiguo).
        $this->assertSame(
            ['third', 'second', 'first'],
            $this->journal->reverse()
        );
    }

    public function testJournalClearJournal(): void
    {
        $this->journal->add('item');
        $this->journal->clear();

        $this->assertSame([], $this->journal->all());
    }

    public function testJournalJournalWithDifferentTypes(): void
    {
        $object = new stdClass();
        $array = ['key' => 'value'];
        $number = 42;

        $this->journal->add($object);
        $this->journal->add($array);
        $this->journal->add($number);

        $allItems = $this->journal->all();

        $this->assertCount(3, $allItems);
        $this->assertSame($object, $allItems[0]);
        $this->assertSame($array, $allItems[1]);
        $this->assertSame($number, $allItems[2]);
    }
}
