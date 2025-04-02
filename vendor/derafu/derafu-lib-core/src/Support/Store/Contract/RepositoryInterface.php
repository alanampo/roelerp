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

namespace Derafu\Lib\Core\Support\Store\Contract;

use Doctrine\Common\Collections\Criteria;

/**
* Interfaz para repositorios de objetos/entidades.
*
* Proporciona métodos estándar para acceder y buscar objetos/entidades desde
* una fuente de datos.
*/
interface RepositoryInterface extends StoreInterface
{
    /**
     * Encuentra un objeto por su identificador.
     *
     * @param mixed $id Identificador del objeto
     * @param mixed $lockMode No utilizado en esta implementación.
     * @param mixed $lockVersion No utilizado en esta implementación.
     * @return object|null El objeto encontrado o null si no existe
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?object;

    /**
     * Encuentra todos los objetos en el repositorio.
     *
     * @return object[] Array de objetos encontrados
     */
    public function findAll(): array;

    /**
     * Encuentra objetos según criterios específicos.
     *
     * @param array $criteria Criterios de búsqueda en formato ['campo' => 'valor'].
     * @param array|null $orderBy Criterios de ordenamiento ['campo' => 'ASC|DESC'].
     * @param int|null $limit Cantidad máxima de resultados a retornar.
     * @param int|null $offset Cantidad de resultados a saltar.
     * @return object[] Array de objetos que cumplen los criterios.
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Encuentra un único objeto según criterios específicos.
     *
     * @param array $criteria Criterios de búsqueda en formato ['campo' => 'valor'].
     * @param array|null $orderBy Criterios de ordenamiento ['campo' => 'ASC|DESC'].
     * @return object|null El primer objeto que cumple los criterios o null si no existe.
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object;

    /**
     * Retorna el número total de objetos en el repositorio.
     *
     * @param array $criteria Criterios al contar en formato ['campo' => 'valor'].
     * @return int Cantidad de objetos.
     */
    public function count(array $criteria = []): int;

    /**
     * Aplica un criterio para filtrar entidades almacenadas.
     *
     * Este método permite filtrar y ordenar las entidades en el almacenamiento
     * de acuerdo a las condiciones definidas en un objeto `Criteria`.
     *
     * El resultado es un arrelgo que contiene únicamente las entidades que
     * cumplen con las condiciones.
     *
     * @param Criteria $criteria El objeto `Criteria` que define las
     * condiciones, el orden y los límites de los resultados.
     * @return array Un arreglo con las entidades que cumplen el criterio.
     * @see \Doctrine\Common\Collections\Criteria
     * @see \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByCriteria(Criteria $criteria): array;

    /**
     * Entrega el nombre de la clase que el repositorio gestiona.
     *
     * @return string
     */
    public function getClassName(): string;
}
