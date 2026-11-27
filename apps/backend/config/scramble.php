<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;
use Dedoc\Scramble\OpenApiVersion;
use Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy;

return [
    'public' => (bool) env('API_DOCS_PUBLIC', false),

    'api_path' => 'api',

    'api_domain' => null,

    'export_path' => 'storage/app/openapi.json',

    'cache' => [
        'key' => 'scramble.openapi',
        'store' => 'file',
    ],

    'openapi_version' => OpenApiVersion::V3_1,

    'info' => [
        'version' => env('APP_VERSION', '1.0.0'),
        'description' => implode("\n\n", [
            'API REST para la autenticación y la administración de usuarios.',
            '**Autenticación.** `POST /auth/login` devuelve un token. Envíalo como `Authorization: Bearer <token>`; en esta página, pégalo en el botón de seguridad de cualquier endpoint protegido.',
            '**Errores.** Siempre `{ "message", "code" }`. En validación (422) llega `{ "message", "errors": { "campo": ["…"] } }`.',
            '**Paginación.** Las listas responden `{ "data", "links", "meta" }` y aceptan `page` y `per_page`.',
        ]),
    ],

    'ui' => [
        'title' => 'Gestión de usuarios · API',
    ],

    'dev_tools' => [
        'enabled' => env('SCRAMBLE_DEV_TOOLS', env('APP_DEBUG', false)),
    ],

    'renderer' => 'elements',

    'renderers' => [
        'elements' => [
            'view' => 'scramble::docs',
            'theme' => 'light',
            'hideTryIt' => false,
            'hideSchemas' => false,
            'logo' => '',
            'tryItCredentialsPolicy' => 'include',
            'layout' => 'responsive',
            'router' => 'hash',
        ],
        'scalar' => [
            'view' => 'scramble::scalar',
            'cdn' => 'https://cdn.jsdelivr.net/npm/@scalar/api-reference',
            'theme' => 'laravel',
            'proxyUrl' => 'https://proxy.scalar.com',
            'darkMode' => false,
            'showDeveloperTools' => 'never',
            'agent' => ['disabled' => true],
            'credentials' => 'include',
        ],
        'fabric' => [
            'view' => 'scramble::fabric',
        ],
    ],

    'servers' => null,

    'enum_cases_description_strategy' => 'description',

    'enum_cases_names_strategy' => false,

    'enum_cases_auto_description_strategy' => false,

    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],

    'security_strategy' => MiddlewareAuthSecurityStrategy::class,
];
