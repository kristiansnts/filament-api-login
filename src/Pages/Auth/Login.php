<?php

namespace Kristiansnts\FilamentApiLogin\Pages\Auth;

use Kristiansnts\FilamentApiLogin\Services\ExternalAuthService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Override the authentication logic
     */
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        // Authenticate against external API
        $externalAuthService = app(ExternalAuthService::class);
        $authResult = $externalAuthService->authenticate(
            $data['email'],
            $data['password']
        );

        if (!$authResult) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        // Handle nested data structure: token + data object
        $userData = $authResult['data'] ?? $authResult; // Support both formats
        
        // Store token and user data in session
        session([
            'external_auth_token' => $authResult['token'],
            'external_user_data' => $userData,
            'user_id' => $userData['operator_id'] ?? $userData['id'] ?? null,
            'user_email' => $userData['email'] ?? null,
            'user_name' => $userData['username'] ?? null,
            'user_role' => $userData['role'] ?? null,
        ]);

        session()->regenerate();

        return app(LoginResponse::class);
    }
}