<?php
/**
 * Utilidad para enviar respuestas HTTP JSON estandarizadas
 */

class Response {
    /**
     * Envía una respuesta JSON exitosa
     */
    public static function success($data = null, $message = 'Operación exitosa', $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Envía una respuesta JSON de error
     */
    public static function error($message = 'Error en la operación', $statusCode = 400, $details = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'status' => 'error',
            'message' => $message
        ];

        if ($details !== null) {
            $response['details'] = $details;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Envía una respuesta no autorizada
     */
    public static function unauthorized($message = 'No autorizado') {
        self::error($message, 401);
    }

    /**
     * Envía una respuesta prohibida
     */
    public static function forbidden($message = 'Acceso prohibido') {
        self::error($message, 403);
    }

    /**
     * Envía una respuesta de recurso no encontrado
     */
    public static function notFound($message = 'Recurso no encontrado') {
        self::error($message, 404);
    }

    /**
     * Envía una respuesta de error del servidor
     */
    public static function serverError($message = 'Error interno del servidor') {
        self::error($message, 500);
    }

    /**
     * Envía una respuesta de validación fallida
     */
    public static function validationError($errors, $message = 'Error de validación') {
        self::error($message, 422, $errors);
    }
}
