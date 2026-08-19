<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthSanctumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_login_issues_bearer_token_and_me_returns_user_and_token_metadata(): void
    {
        $user = $this->makeUserWithRole('editor', [
            'email' => 'editor@example.com',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'editor@example.com',
            'password' => 'password',
            'device_name' => 'phpunit-device',
        ]);

        $loginResponse->assertOk();
        $loginResponse->assertJsonPath('meta.api_version', 'v1');
        $loginResponse->assertJsonPath('data.user.email', 'editor@example.com');
        $loginResponse->assertJsonPath('data.auth.mode', 'bearer');
        $loginResponse->assertJsonPath('data.auth.guard', 'sanctum');
        $loginResponse->assertJsonPath('data.auth.token_type', 'Bearer');
        $loginResponse->assertJsonPath('data.auth.abilities.0', 'content:read');

        $token = $loginResponse->json('data.auth.access_token');
        $this->assertIsString($token);

        $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me');

        $meResponse->assertOk();
        $meResponse->assertJsonPath('data.user.id', $user->id);
        $meResponse->assertJsonPath('data.token.name', 'phpunit-device');
        $meResponse->assertJsonPath('data.token.current', true);
    }

    public function test_tokens_list_and_logout_revoke_current_token(): void
    {
        $user = $this->makeUserWithRole('editor', [
            'email' => 'tokens@example.com',
        ]);

        $token = $user->createToken('phpunit-current', $user->apiAbilities())->plainTextToken;
        $currentTokenId = $user->tokens()->latest('id')->value('id');

        $otherPlainTextToken = $user->createToken('phpunit-other', $user->apiAbilities())->plainTextToken;
        $otherTokenId = $user->tokens()->latest('id')->value('id');

        $tokensResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/tokens');

        $tokensResponse->assertOk();
        $tokensResponse->assertJsonCount(2, 'data');
        $tokensResponse->assertJsonFragment(['id' => $currentTokenId, 'current' => true]);
        $tokensResponse->assertJsonFragment(['id' => $otherTokenId, 'current' => false]);

        $deleteResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/auth/tokens/'.$otherTokenId);

        $deleteResponse->assertOk();
        $deleteResponse->assertJsonPath('data.deleted', true);
        $deleteResponse->assertJsonPath('data.current', false);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $otherTokenId]);

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertOk();
        $logoutResponse->assertJsonPath('data.logged_out', true);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $currentTokenId]);

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id, 'name' => 'phpunit-other']);
        $this->assertIsString($otherPlainTextToken);
    }

    public function test_invalid_login_and_unauthenticated_requests_use_stable_error_contract(): void
    {
        $this->makeUserWithRole('viewer', [
            'email' => 'viewer@example.com',
        ]);

        $invalidLoginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'viewer@example.com',
            'password' => 'wrong-password',
        ]);

        $invalidLoginResponse->assertStatus(422);
        $invalidLoginResponse->assertJsonPath('error.code', 'validation_error');
        $invalidLoginResponse->assertJsonPath('meta.api_version', 'v1');
        $this->assertIsArray($invalidLoginResponse->json('error.details.email'));

        $unauthenticatedResponse = $this->getJson('/api/v1/me');
        $unauthenticatedResponse->assertUnauthorized();
        $unauthenticatedResponse->assertJsonPath('error.code', 'unauthenticated');
        $unauthenticatedResponse->assertJsonPath('meta.api_version', 'v1');
    }
}
