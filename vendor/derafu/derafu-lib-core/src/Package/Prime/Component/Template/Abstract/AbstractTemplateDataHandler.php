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

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Abstract;

use Closure;
use Derafu\Lib\Core\Package\Prime\Component\Entity\Contract\RepositoryInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\DataHandlerInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Exception\TemplateException;
use Derafu\Lib\Core\Package\Prime\Component\Template\Service\DataHandler;

/**
 * Base para las implementaciones del servicio que maneja y formatea los datos
 * de una plantilla.
 */
abstract class AbstractTemplateDataHandler implements DataHandlerInterface
{
    /**
     * Mapa de handlers.
     *
     * @var array
     */
    protected array $handlers;

    /**
     * Handler por defecto para manejar los casos.
     *
     * @var DataHandlerInterface
     */
    protected DataHandlerInterface $handler;

    /**
     * @inheritDoc
     */
    public function handle(string $id, mixed $data): string
    {
        // Si no hay valor asignado en los datos se entrega un string vacio.
        if (!$data) {
            return '';
        }

        // Buscar el handler del dato según su ID.
        $handler = $this->getHandler($id);

        // Corroborar que exista el handler global.
        if (!isset($this->handler)) {
            $this->handler = new DataHandler();
        }
        assert($this->handler instanceof DataHandler);

        // Ejecutar el handler sobre los datos para formatearlos.
        return $this->handler->handle($id, $data, $handler);
    }

    /**
     * Obtiene el handler de un campo a partir de su ID.
     *
     * @param string $id
     * @return string|array|callable|Closure|DataHandlerInterface|RepositoryInterface
     */
    protected function getHandler(string $id): string|array|callable|Closure|DataHandlerInterface|RepositoryInterface
    {
        if (!isset($this->handlers)) {
            $this->handlers = $this->createHandlers();
        }

        if (!isset($this->handlers[$id])) {
            throw new TemplateException(sprintf(
                'El formato para %s no está definido. Los disponibles son: %s.',
                $id,
                implode(', ', array_keys($this->handlers))
            ));
        }

        if (is_string($this->handlers[$id]) && str_starts_with($this->handlers[$id], 'alias:')) {
            [$alias, $handler] = explode(':', $this->handlers[$id], 2);

            if (!isset($this->handlers[$handler])) {
                throw new TemplateException(sprintf(
                    'El alias %s del formato para %s no está definido. Los disponibles son: %s.',
                    $handler,
                    $id,
                    implode(', ', array_keys($this->handlers))
                ));
            }

            return $this->handlers[$handler];
        }

        return $this->handlers[$id];
    }

    /**
     * Crea el mapa de campos a handlers para la plantilla que usará este
     * manejador de datos para su formateo.
     *
     * @return array
     */
    abstract protected function createHandlers(): array;
}
