<?php
/**
 * Configuración de JWT (JSON Web Tokens)
 */

return [
    // Clave secreta para firmar los tokens (CAMBIAR EN PRODUCCIÓN)
    'secret_key' => 'TU_CLAVE_SECRETA_SUPER_SEGURA_CAMBIAR_EN_PRODUCCION_' . md5(__DIR__),

    // Algoritmo de encriptación
    'algorithm' => 'HS256',

    // Tiempo de expiración del access token (2 semanas)
    'access_token_expire' => 1209600, // 14 * 24 * 60 * 60

    // Tiempo de expiración del refresh token (60 días)
    'refresh_token_expire' => 5184000, // 60 * 24 * 60 * 60

    // Emisor del token
    'issuer' => 'roel-erp-api',

    // Audiencia
    'audience' => 'roel-erp-client',
];
