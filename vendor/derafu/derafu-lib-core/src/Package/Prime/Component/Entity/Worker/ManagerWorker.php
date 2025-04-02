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

namespace Derafu\Lib\Core\Package\Prime\Component\Entity\Worker;

use Derafu\Lib\Core\Foundation\Abstract\AbstractWorker;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\DatasourceProviderWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\ManagerWorkerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\RepositoryInterface;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Entity\Entity;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Exception\ManagerException;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Mapping as DEM;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Repository\Repository;
use Exception;
use ReflectionClass;

/**
 * Worker "prime.entity.manager".
 */
class ManagerWorker extends AbstractWorker implements ManagerWorkerInterface
{
    /**
     * Sufijo de la interfaz.
     *
     * @var string
     */
    private const ENTITY_INTERFACE_SUFFIX = 'Interface';

    /**
     * Namespace de la interfaz.
     *
     * Importante: solo el nivel inmediatamente superior.
     *
     * @var string
     */
    private const ENTITY_INTERFACE_NAMESPACE = 'Contract';

    /**
     * Sufijo de la clase de entidad.
     *
     * Importante: no se utilizan sufijos en entidades, pero se deja
     * estandrizado acá en la constante.
     *
     * @var string
     */
    private const ENTITY_CLASS_SUFFIX = ''; // En blanco a propósito.

    /**
     * Namespace de la entidad.
     *
     * Importante: solo el nivel inmediatamente superior.
     *
     * @var string
     */
    private const ENTITY_CLASS_NAMESPACE = 'Entity';

    /**
     * Esquema de configuración del worker.
     *
     * @var array
     */
    protected array $configurationSchema = [
        'entityClass' => [
            'types' => 'string',
            'default' => Entity::class,
        ],
        'repositoryClass' => [
            'types' => 'string',
            'default' => Repository::class,
        ],
    ];

    /**
     * Listado de repositorios que ya han sido cargados desde sus orígenes de
     * datos.
     *
     * @var array<string,RepositoryInterface>
     */
    private array $loaded = [];

    /**
     * Constructor del worker.
     *
     * @param DatasourceProviderWorkerInterface $datasourceProviderWorker
     */
    public function __construct(
        private DatasourceProviderWorkerInterface $datasourceProviderWorker
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(string $repository): RepositoryInterface
    {
        // Si el repositorio no está cargado se carga.
        if (!isset($this->loaded[$repository])) {
            try {
                $this->loaded[$repository] = $this->loadRepository($repository);
            } catch (Exception $e) {
                throw new ManagerException($e->getMessage());
            }
        }

        // Retornar el repositorio solicitado.
        return $this->loaded[$repository];
    }

    /**
     * Carga un repositorio con los datos desde un origen de datos.
     *
     * @param string $repository
     * @return RepositoryInterface
     */
    private function loadRepository(string $repository): RepositoryInterface
    {
        // Resolver el repositorio que se debe crear.
        $entityClass = $this->resolveEntityClass($repository);
        $repositoryClass = $this->resolveRepositoryClass($entityClass);

        // Si el repositorio implementa RepositoryInterface es un repositorio
        // con datos que se obtienen desde DatasourceProvider y se deben cargar
        // al repositorio (en memoria).
        if (in_array(RepositoryInterface::class, class_implements($repositoryClass))) {
            $data = $this->datasourceProviderWorker->fetch($repository);
            $instance = new $repositoryClass($data, $entityClass);
        }

        // Si el repositorio es otro tipo de clase se instancia sin datos y se
        // espera que el repositorio resuelva su carga (ej: desde una base de
        // datos).
        else {
            // TODO: Mejorar retorno de RepositoryInterface con este caso.
            $instance = new $repositoryClass();
        }

        // Asignar la instnacia del repositorio como cargada y retornar.
        $this->loaded[$repository] =  $instance;
        return $this->loaded[$repository];
    }

    /**
     * Determina la clase de la entidad que se debe utilizar con el repositorio.
     *
     * El identificador del repositorio puede ser el FQCN de la clase de la
     * entidad (lo ideal) o un identificador genérico que se resolverá a la
     * clase de entidad configurada en el worker.
     *
     * Si el identificador del repositorio es una interfaz se espera:
     *
     *   - La interfaz esté dentro de un namespace "Contract".
     *   - La interfaz tenga como sufijo "Interface".
     *   - La entidad esté dentro del namespace "Entity" sin sufijo.
     *
     * @param string $repository Identificador del repositorio.
     * @return string Clase de la entidad para el repositorio.
     */
    private function resolveEntityClass(string $repository): string
    {
        // Si el identificador del repositorio "parece" clase se asume que es la
        // clase de la entidad o una interfaz de la entidad en el mismo
        // namespace.
        if (str_contains($repository, '\\')) {
            $entityClass = $this->guessEntityClass($repository);
        }

        // Se entrega la clase de la entidad por defecto cuando el identificador
        // no es una clase. Si no tiene "\" entonces no tiene namespace, en este
        // caso se asume no es una clase.
        else {
            $entityClass = $this->getConfiguration()->get('entityClass');
        }

        // Lanzar error si la clase no existe pues podría haber sido mal
        // escrita por el programador.
        if (!class_exists($entityClass)) {
            throw new ManagerException(sprintf(
                'La clase de entidad %s no existe. ¿Estará mal escrita?',
                $entityClass
            ));
        }

        // Entregar la clase de la entidad.
        return $entityClass;
    }

    /**
     * Adivina la clase de entidad en caso que la clase de entrada sea una
     * interfaz.
     *
     * @param string $class
     * @return string
     */
    private function guessEntityClass(string $class): string
    {
        // Si la clase es una interfaz se asume una clase de entidad en el mismo
        // namespace. Esto es rígido y requiere un formato para el FQCN de la
        // clase y la interfaz. Por ahora es suficiente.
        if (str_ends_with($class, self::ENTITY_INTERFACE_SUFFIX)) {
            $length = strlen($class) - strlen(self::ENTITY_INTERFACE_SUFFIX);
            return str_replace(
                '\\' . self::ENTITY_INTERFACE_NAMESPACE .  '\\',
                '\\' . self::ENTITY_CLASS_NAMESPACE  . '\\',
                substr($class, 0, $length)
            ) . self::ENTITY_CLASS_SUFFIX;
        }

        // Se entrega la misma clase, pues no tiene el formato esperado para
        // adiviar la clase de entidad.
        return $class;
    }

    /**
     * Determina la clase del repositorio que se debe utilizar para una entidad.
     *
     * Si la clase de la entidad no provee la información de su repositorio
     * asociado se entregará la clase de repositorio configurada en el worker.
     *
     * @param string $entityClass Clase de la entidad.
     * @return string Clase del repositorio.
     */
    private function resolveRepositoryClass(string $entityClass): string
    {
        // Se trata de obtener la clase del repositorio desde el método estático
        // de la entidad getRepositoryClass().
        if (method_exists($entityClass, 'getRepositoryClass')) {
            $repositoryClass = call_user_func([$entityClass, 'getRepositoryClass']);
            if ($repositoryClass) {
                return $repositoryClass;
            }
        }

        // Se trata de obtener la clase del repositorio desde un atributo PHP de
        // la clase de la entidad.
        $reflectionClass = new ReflectionClass($entityClass);
        $attributes = $reflectionClass->getAttributes(DEM\Entity::class);
        if (!empty($attributes)) {
            $repositoryClass = $attributes[0]->newInstance()->repositoryClass;
            if ($repositoryClass) {
                return $repositoryClass;
            }
        }

        // Se entrega la clase del repositorio por defecto configurada.
        $repositoryClass = $this->getConfiguration()->get('repositoryClass');

        // Lanzar error si la clase no existe pues podría haber sido mal
        // escrita por el programador.
        if (!class_exists($repositoryClass)) {
            throw new ManagerException(sprintf(
                'La clase de repositorio %s no existe. ¿Estará mal escrita?',
                $repositoryClass
            ));
        }

        // Entregar clase del repositorio.
        return $repositoryClass;
    }
}
