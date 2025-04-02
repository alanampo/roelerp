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

namespace Derafu\Lib\Core\Package\Prime\Component\Template\Service;

use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\DataFormatterInterface;
use Derafu\Lib\Core\Package\Prime\Component\Template\Contract\DataHandlerInterface;
use Derafu\Lib\Core\Support\Store\Contract\RepositoryInterface;
use Throwable;

/**
 * Servicio de formateo de datos.
 *
 * Permite recibir un valor y formatearlo según un mapa de handlers predefinido
 * mediante su identificador.
 */
class DataFormatter implements DataFormatterInterface
{
    /**
     * Mapeo de identificadores a la forma que se usará para darle formato a los
     * valores asociados al identificador.
     *
     * @var array<string,string|array|callable|DataHandlerInterface|RepositoryInterface>
     */
    private array $handlers;

    /**
     * Handler por defecto de los formatos.
     *
     * @var DataHandlerInterface
     */
    private DataHandlerInterface $handler;

    /**
     * Constructor del servicio.
     *
     * @param array $handlers Mapa de handlers para los formatos.
     * @param DataHandlerInterface|null $handler Handler por defecto a usar.
     */
    public function __construct(
        array $handlers = [],
        ?DataHandlerInterface $handler = null
    ) {
        $this->setHandlers($handlers);
        $this->handler = $handler ?? new DataHandler();
    }

    /**
     * {@inheritDoc}
     */
    public function setHandlers(array $handlers): static
    {
        $this->handlers = $handlers;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * {@inheritDoc}
     */
    public function addHandler(
        string $id,
        string|array|callable|DataHandlerInterface|RepositoryInterface $handler
    ): static {
        $this->handlers[$id] = $handler;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHandler(string $id): string|array|callable|DataHandlerInterface|RepositoryInterface|null
    {
        return $this->handlers[$id] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function format(string $id, mixed $data): string
    {
        // El ID es el formato.
        if (isset($this->handlers[$id])) {
            return $this->handle($id, $id, $data);
        }

        // Si no hay handler exacto revisar si el ID tiene partes. Si el ID
        // tiene partes se busca si la primera parte está definida como handler.
        if (str_contains($id, '.')) {
            // Separar en handler e ID y si existe el formato se usa.
            [$handler, $id] = explode('.', $id, 2);
            if (isset($this->handlers[$handler])) {
                return $this->handle($handler, $id, $data);
            }
        }

        // Buscar si hay un handler genérico (comodín).
        if (isset($this->handlers['*'])) {
            return $this->handle('*', $id, $data);
        }

        // Si no hay handler para manejar se tratará de convertir a string.
        return $this->toString($data);
    }

    /**
     * Maneja el formateo de los datos según cierto handler.
     *
     * @param string $name Nombre del handler registrado que se debe utilizar.
     * @param string $id Identificador pasado del formato.
     * @param mixed $data Datos a formatear.
     * @return string Datos formateados.
     */
    private function handle(string $name, string $id, mixed $data): string
    {
        $handler = $this->handlers[$name];

        if ($handler instanceof DataHandlerInterface) {
            return $handler->handle($id, $data);
        } else {
            if ($this->handler instanceof DataHandler) {
                return $this->handler->handle($id, $data, $handler);
            } else {
                return $this->handler->handle($id, $data);
            }
        }
    }

    /**
     * Hace el mejor esfuerzo para tratar de convertir los datos a string.
     *
     * Si falla al serializar a string se entregará un string con el tipo de
     * dato de lo que se trató de serializar y el error por el cual falló.
     *
     * @param mixed $data
     * @return string
     */
    private function toString(mixed $data): string
    {
        // Si se puede convertir fácilmente, retornar los datos casteados.
        if (is_scalar($data) || $data === null) {
            return (string) $data;
        }

        // Si es objeto e implementa __toString() se usa.
        if (is_object($data) && method_exists($data, '__toString')) {
            return (string) $data;
        }

        // Para arreglos, objetos que no tengan __toString() y otros tipos,
        // serializar con JSON.
        try {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return sprintf(
                'La serialización para el tipo de dato %s falló: %s',
                get_debug_type($data),
                $e->getMessage()
            );
        }
    }
}
