<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\v1\AuthController;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_logout_revokes_current_access_token(): void
    {
        $token = new class {
            public bool $deleted = false;

            public function delete(): void
            {
                $this->deleted = true;
            }
        };

        $user = new class($token) {
            public function __construct(private readonly object $token)
            {
            }

            public function currentAccessToken(): object
            {
                return $this->token;
            }
        };

        $request = Request::create('/api/v1/logout', 'POST');
        $request->setUserResolver(fn () => $user);

        $controller = new AuthController($this->createStub(AuthService::class));
        $response = $controller->logout($request);

        $this->assertTrue($token->deleted);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('success', $response->getData(true)['status']);
    }
}
