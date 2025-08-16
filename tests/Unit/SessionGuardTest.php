<?php

namespace Kristiansnts\FilamentApiLogin\Tests\Unit;

use Kristiansnts\FilamentApiLogin\Auth\SessionGuard;
use Kristiansnts\FilamentApiLogin\Auth\SessionUser;
use Kristiansnts\FilamentApiLogin\Tests\TestCase;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

class SessionGuardTest extends TestCase
{
    private SessionGuard $guard;
    private Session $session;
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->session = $this->createMock(Session::class);
        $this->request = $this->createMock(Request::class);
        $this->guard = new SessionGuard($this->request, $this->session);
    }

    public function test_user_returns_null_when_no_session_data()
    {
        $this->session->expects($this->once())
            ->method('get')
            ->with('external_user_data')
            ->willReturn(null);

        $user = $this->guard->user();

        $this->assertNull($user);
    }

    public function test_user_returns_session_user_when_data_exists()
    {
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'username' => 'testuser'
        ];

        $this->session->expects($this->once())
            ->method('get')
            ->with('external_user_data')
            ->willReturn($userData);

        $user = $this->guard->user();

        $this->assertInstanceOf(SessionUser::class, $user);
        $this->assertEquals(1, $user->getAuthIdentifier());
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function test_user_caches_result_on_subsequent_calls()
    {
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'username' => 'testuser'
        ];

        $this->session->expects($this->once())
            ->method('get')
            ->with('external_user_data')
            ->willReturn($userData);

        $user1 = $this->guard->user();
        $user2 = $this->guard->user();

        $this->assertSame($user1, $user2);
    }

    public function test_validate_returns_true_when_session_has_required_data()
    {
        $this->session->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['external_auth_token'], ['external_user_data'])
            ->willReturn(true, true);

        $result = $this->guard->validate();

        $this->assertTrue($result);
    }

    public function test_validate_returns_false_when_missing_token()
    {
        $this->session->expects($this->once())
            ->method('has')
            ->with('external_auth_token')
            ->willReturn(false);

        $result = $this->guard->validate();

        $this->assertFalse($result);
    }

    public function test_validate_returns_false_when_missing_user_data()
    {
        $this->session->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['external_auth_token'], ['external_user_data'])
            ->willReturn(true, false);

        $result = $this->guard->validate();

        $this->assertFalse($result);
    }

    public function test_check_returns_true_when_user_exists()
    {
        $userData = ['id' => 1, 'email' => 'test@example.com'];

        $this->session->expects($this->once())
            ->method('get')
            ->with('external_user_data')
            ->willReturn($userData);

        $result = $this->guard->check();

        $this->assertTrue($result);
    }

    public function test_check_returns_false_when_no_user()
    {
        $this->session->expects($this->once())
            ->method('get')
            ->with('external_user_data')
            ->willReturn(null);

        $result = $this->guard->check();

        $this->assertFalse($result);
    }

    public function test_guest_returns_opposite_of_check()
    {
        $this->session->expects($this->exactly(2))
            ->method('get')
            ->with('external_user_data')
            ->willReturnOnConsecutiveCalls(
                ['id' => 1, 'email' => 'test@example.com'],
                null
            );

        $this->assertFalse($this->guard->guest());
        $this->assertTrue($this->guard->guest());
    }

    public function test_id_returns_user_identifier()
    {
        $userData = ['id' => 123, 'email' => 'test@example.com'];

        $this->session->expects($this->once())
            ->method('get')
            ->with('external_user_data')
            ->willReturn($userData);

        $id = $this->guard->id();

        $this->assertEquals(123, $id);
    }

    public function test_id_returns_null_when_no_user()
    {
        $this->session->expects($this->once())
            ->method('get')
            ->with('external_user_data')
            ->willReturn(null);

        $id = $this->guard->id();

        $this->assertNull($id);
    }

    public function test_set_user_sets_and_returns_guard()
    {
        $user = new SessionUser(['id' => 1, 'email' => 'test@example.com']);

        $result = $this->guard->setUser($user);

        $this->assertSame($this->guard, $result);
        $this->assertSame($user, $this->guard->user());
    }

    public function test_logout_clears_session_and_user()
    {
        $this->session->expects($this->once())
            ->method('forget')
            ->with([
                'external_auth_token',
                'external_user_data',
                'user_id',
                'user_email',
                'user_name',
                'user_role'
            ]);

        $user = new SessionUser(['id' => 1]);
        $this->guard->setUser($user);

        $this->guard->logout();

        $this->session->expects($this->once())
            ->method('get')
            ->with('external_user_data')
            ->willReturn(null);

        $this->assertNull($this->guard->user());
    }

    public function test_validate_accepts_credentials_parameter()
    {
        $this->session->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['external_auth_token'], ['external_user_data'])
            ->willReturn(true, true);

        $credentials = ['email' => 'test@example.com', 'password' => 'password'];
        $result = $this->guard->validate($credentials);

        $this->assertTrue($result);
    }
}