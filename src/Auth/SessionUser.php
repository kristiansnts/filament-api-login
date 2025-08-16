<?php

namespace Kristiansnts\FilamentApiLogin\Auth;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;

class SessionUser implements Authenticatable, FilamentUser
{
    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->attributes['id'] ?? null;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void
    {
        // Not implemented for session-based auth
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Allow access for all authenticated users
    }

    public function __get($key)
    {
        return $this->getAttributeValue($key);
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    public function getAttributeValue($key)
    {
        // Map common attribute names for new API response format
        if ($key === 'name' && !isset($this->attributes['name'])) {
            return $this->attributes['username'] ?? null;
        }
        
        if ($key === 'email' && !isset($this->attributes['email'])) {
            return $this->attributes['email'] ?? null;
        }
        
        return $this->attributes[$key] ?? null;
    }

    public function getAttribute($key)
    {
        return $this->getAttributeValue($key);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function getEmail(): string
    {
        return $this->attributes['email'] ?? '';
    }

    public function getName(): string
    {
        return $this->attributes['username'] ?? $this->attributes['name'] ?? '';
    }

    public function getFilamentName(): string
    {
        return $this->getName();
    }

    public function getKey()
    {
        return $this->getAuthIdentifier();
    }
}