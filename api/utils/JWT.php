<?php
/**
 * Utilidad para manejar JSON Web Tokens
 * Implementación simple de JWT sin dependencias externas
 */

class JWT {
    private static $config;

    private static function getConfig() {
        if (!self::$config) {
            self::$config = require __DIR__ . '/../config/jwt.php';
        }
        return self::$config;
    }

    /**
     * Codifica datos en base64 URL-safe
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodifica datos base64 URL-safe
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Genera un JWT
     *
     * @param array $payload Datos a incluir en el token
     * @param string $type Tipo de token: 'access' o 'refresh'
     * @return string Token JWT
     */
    public static function encode($payload, $type = 'access') {
        $config = self::getConfig();

        // Header
        $header = [
            'typ' => 'JWT',
            'alg' => $config['algorithm']
        ];

        // Agregar claims estándar
        $now = time();
        $expireKey = $type === 'refresh' ? 'refresh_token_expire' : 'access_token_expire';

        $payload['iat'] = $now; // Issued at
        $payload['exp'] = $now + $config[$expireKey]; // Expiration
        $payload['iss'] = $config['issuer']; // Issuer
        $payload['aud'] = $config['audience']; // Audience
        $payload['type'] = $type; // Tipo de token

        // Codificar header y payload
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // Crear firma
        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $config['secret_key'],
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        // Retornar token completo
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * Decodifica y valida un JWT
     *
     * @param string $token Token JWT
     * @return array|false Payload decodificado o false si es inválido
     */
    public static function decode($token) {
        $config = self::getConfig();

        // Separar partes del token
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        // Verificar firma
        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $config['secret_key'],
            true
        );
        $signatureCheck = self::base64UrlEncode($signature);

        if ($signatureEncoded !== $signatureCheck) {
            return false;
        }

        // Decodificar payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (!$payload) {
            return false;
        }

        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        // Verificar issuer y audience
        if (isset($payload['iss']) && $payload['iss'] !== $config['issuer']) {
            return false;
        }

        if (isset($payload['aud']) && $payload['aud'] !== $config['audience']) {
            return false;
        }

        return $payload;
    }

    /**
     * Extrae el token del header Authorization
     *
     * @return string|null Token o null si no existe
     */
    public static function getBearerToken() {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );

            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        // Extraer token del formato "Bearer <token>"
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
