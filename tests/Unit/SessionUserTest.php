<?php

namespace Kristiansnts\FilamentApiLogin\Tests\Unit;

use Kristiansnts\FilamentApiLogin\Auth\SessionUser;
use Kristiansnts\FilamentApiLogin\Tests\TestCase;
use Filament\Panel;

class SessionUserTest extends TestCase
{
    public function test_user_creation_with_attributes()
    {
        $attributes = [
            'id' => 1,
            'email' => 'test@example.com',
            'username' => 'testuser',
            'role' => 'admin'
        ];

        $user = new SessionUser($attributes);

        $this->assertEquals(1, $user->getAuthIdentifier());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('testuser', $user->getName());
    }

    public function test_auth_identifier_methods()
    {
        $user = new SessionUser(['id' => 123]);

        $this->assertEquals('id', $user->getAuthIdentifierName());
        $this->assertEquals(123, $user->getAuthIdentifier());
        $this->assertEquals(123, $user->getKey());
    }

    public function test_auth_password_methods()
    {
        $user = new SessionUser([]);

        $this->assertEquals('', $user->getAuthPassword());
        $this->assertEquals('password', $user->getAuthPasswordName());
    }

    public function test_remember_token_methods()
    {
        $user = new SessionUser([]);

        $this->assertEquals('', $user->getRememberToken());
        $this->assertEquals('', $user->getRememberTokenName());
        
        $user->setRememberToken('token');
        $this->assertEquals('', $user->getRememberToken());
    }

    public function test_panel_access()
    {
        $user = new SessionUser([]);
        $panel = $this->createMock(Panel::class);

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_attribute_access_via_magic_methods()
    {
        $attributes = [
            'id' => 1,
            'email' => 'test@example.com',
            'username' => 'testuser',
            'custom_field' => 'custom_value'
        ];

        $user = new SessionUser($attributes);

        $this->assertEquals(1, $user->id);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('custom_value', $user->custom_field);
    }

    public function test_isset_magic_method()
    {
        $user = new SessionUser(['email' => 'test@example.com']);

        $this->assertTrue(isset($user->email));
        $this->assertFalse(isset($user->nonexistent));
    }

    public function test_name_mapping_fallback()
    {
        $user = new SessionUser(['username' => 'testuser']);

        $this->assertEquals('testuser', $user->name);
        $this->assertEquals('testuser', $user->getName());
        $this->assertEquals('testuser', $user->getFilamentName());
    }

    public function test_name_with_explicit_name_attribute()
    {
        $user = new SessionUser([
            'name' => 'John Doe',
            'username' => 'johndoe'
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('John Doe', $user->getName());
    }

    public function test_email_fallback_behavior()
    {
        $user = new SessionUser(['email' => 'test@example.com']);

        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function test_get_attribute_method()
    {
        $user = new SessionUser(['test_field' => 'test_value']);

        $this->assertEquals('test_value', $user->getAttribute('test_field'));
        $this->assertNull($user->getAttribute('nonexistent'));
    }

    public function test_to_array_method()
    {
        $attributes = [
            'id' => 1,
            'email' => 'test@example.com',
            'username' => 'testuser'
        ];

        $user = new SessionUser($attributes);

        $this->assertEquals($attributes, $user->toArray());
    }

    public function test_empty_values_handling()
    {
        $user = new SessionUser([]);

        $this->assertNull($user->getAuthIdentifier());
        $this->assertEquals('', $user->getEmail());
        $this->assertEquals('', $user->getName());
        $this->assertEquals('', $user->getFilamentName());
    }

    public function test_null_attribute_values()
    {
        $user = new SessionUser(['email' => null, 'username' => null]);

        $this->assertNull($user->email);
        $this->assertNull($user->username);
        $this->assertEquals('', $user->getEmail());
        $this->assertEquals('', $user->getName());
    }
}