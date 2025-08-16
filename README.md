# Filament External Auth

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kristiansnts/filament-external-auth.svg?style=flat-square)](https://packagist.org/packages/kristiansnts/filament-external-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/kristiansnts/filament-external-auth.svg?style=flat-square)](https://packagist.org/packages/kristiansnts/filament-external-auth)

Token-based authentication for FilamentPHP that authenticates against external APIs without requiring local database users.

## Features

- 🔐 **External API Authentication** - Authenticate users against your existing API
- 🚫 **No Local Users** - No need for local database user records
- 🎫 **Token-Based** - Secure session management with API tokens
- 🔧 **Easy Setup** - Simple configuration and installation
- 📝 **Fully Customizable** - Customize API requests, user mapping, and access control

## Installation

You can install the package via Composer:

```bash
composer require kristiansnts/filament-external-auth
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-external-auth-config"
```

## Configuration

### 1. Environment Variables

Add these variables to your `.env` file:

```env
FILAMENT_EXTERNAL_AUTH_URL=https://your-api.com/api/auth
FILAMENT_EXTERNAL_AUTH_TIMEOUT=30
```

### 2. Authentication Guard

Add the external guard to your `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'external' => [
        'driver' => 'external_session',
    ],
],
```

### 3. Filament Panel Configuration

Update your Filament Panel Provider to use the external authentication:

```php
<?php

namespace App\Providers\Filament;

use Kristiansnts\FilamentExternalAuth\Pages\Auth\Login;
use Filament\Panel;
use Filament\PanelProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class) // Use the package's login page
            ->authGuard('external') // Use the external guard
            ->colors([
                'primary' => Color::Amber,
            ])
            // ... rest of your configuration
    }
}
```

## Usage

### Basic Authentication Flow

1. User enters credentials on the Filament login page
2. Package sends credentials to your external API
3. API validates and returns token + user data
4. Package stores token and user data in session
5. User is authenticated and can access Filament

### API Response Format

Your external API should return a response in this format:

```json
{
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "data": {
        "id": "123",
        "email": "user@example.com",
        "username": "john_doe",
        "role": "admin"
    }
}
```

### Customizing API Requests

You can customize the API request by extending the `ExternalAuthService`:

```php
<?php

namespace App\Services;

use Kristiansnts\FilamentExternalAuth\Services\ExternalAuthService as BaseService;

class CustomExternalAuthService extends BaseService
{
    public function authenticate(string $email, string $password): ?array
    {
        // Add custom headers, modify request format, etc.
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-API-Key' => config('app.api_key'),
            ])
            ->post($this->apiUrl, [
                'username' => $email, // Use 'username' instead of 'email'
                'password' => $password,
                'client_id' => config('app.client_id'),
            ]);

        // Custom response handling
        return $this->handleResponse($response, $email);
    }
}
```

Then bind your custom service in a service provider:

```php
$this->app->bind(
    \Kristiansnts\FilamentExternalAuth\Services\ExternalAuthService::class,
    \App\Services\CustomExternalAuthService::class
);
```

### Customizing User Access Control

Override the `canAccessPanel` method in your panel configuration:

```php
use Kristiansnts\FilamentExternalAuth\Auth\SessionUser;

// In your Panel Provider
->authGuard('external')
->middleware([
    // ... other middleware
    function ($request, $next) {
        $user = auth('external')->user();
        if ($user && !in_array($user->role, ['admin', 'moderator'])) {
            abort(403, 'Access denied');
        }
        return $next($request);
    }
])
```

## Configuration Options

The package configuration file includes these options:

- `api_url` - Your external authentication API endpoint
- `timeout` - API request timeout in seconds
- `log_failures` - Enable/disable logging of authentication failures

## Security Considerations

- ✅ API URL stored securely in environment variables
- ✅ No passwords stored locally
- ✅ Secure session management with Laravel's built-in security
- ✅ Token-based authentication
- ✅ Session regeneration on successful login
- ✅ Configurable request timeouts
- ✅ Failed attempt logging for monitoring

## Troubleshooting

### Common Issues

1. **API Connection Issues**: Check your `FILAMENT_EXTERNAL_AUTH_URL` and network connectivity
2. **Authentication Failures**: Verify your API response format matches the expected structure
3. **Session Issues**: Ensure your session driver is properly configured

### Debug Logging

Enable logging in the configuration to debug authentication issues:

```php
'log_failures' => true,
```

Or via environment variable:

```env
FILAMENT_EXTERNAL_AUTH_LOG_FAILURES=true
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [kristiansnts](https://github.com/kristiansnts)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.