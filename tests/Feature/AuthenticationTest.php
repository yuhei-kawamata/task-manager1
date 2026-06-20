<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Mime\BodyRendererInterface;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログイン画面を表示できる(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }


    /** @test */
    public function 正しい認証情報でログインできる(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        // Act
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Assert
        $response->assertRedirect(route('tasks.index'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function 間違ったパスワードではログインできない(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function 存在しないメールアドレスではログインできない(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'noexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function メールアドレスが空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('login'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワードが空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'email@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function ログアウトできる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(('/'));
        $this->assertGuest();
    }

    /** @test */
    public function 認証済みユーザーはログインページにアクセスするとリダイレクトされる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('tasks.index'));
    }
}