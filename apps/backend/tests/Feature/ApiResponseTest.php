<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_api_always_responds_with_json(): void
    {
        config(['app.name' => 'Test API', 'app.version' => '2.3.4', 'scramble.public' => false]);

        $this->get('/')
            ->assertOk()
            ->assertExactJson(['name' => 'Test API', 'version' => '2.3.4', 'docs' => null]);

        $this->get('/docs/api.json')->assertForbidden();

        $this->get('/does-not-exist')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Recurso no encontrado.', 'code' => 'not_found']);

        $this->get('/api/auth/me')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'No autenticado.', 'code' => 'unauthenticated']);
    }

    public function test_api_publishes_its_openapi_documentation_when_enabled(): void
    {
        config(['scramble.public' => true]);

        $this->get('/')
            ->assertOk()
            ->assertJsonPath('docs.ui', url('docs/api'))
            ->assertJsonPath('docs.openapi', url('docs/api.json'));

        $this->get('/docs/api.json')
            ->assertOk()
            ->assertJsonPath('info.title', 'Gestión de usuarios · API')
            ->assertJsonPath('components.securitySchemes.http.scheme', 'bearer')
            ->assertJsonPath('paths./auth/login.post.security', []);
    }
}
