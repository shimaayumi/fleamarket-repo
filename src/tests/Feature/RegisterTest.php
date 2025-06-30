<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;


    //1.会員登録機能 名前が入力されていない場合、バリデーションメッセージが表示される
    public function test_requires_name_to_register()
    {
        $response = $this->post('/register', [
            'email' => 'user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');
    }

    //1.会員登録機能 メールアドレスが入力されていない場合、バリデーションメッセージが表示される
    public function test_requires_email_to_register()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    //1.会員登録機能 パスワードが入力されていない場合、バリデーションメッセージが表示される
    public function test_requires_password_to_register()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('password');
    }
    //1.会員登録機能 パスワードが7文字以下の場合、バリデーションメッセージが表示される
    public function test_requires_password_to_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }
    //1.会員登録機能 パスワードが確認用パスワードと一致しない場合、バリデーションメッセージが表示される
    public function test_requires_password_to_match_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }
    //1.会員登録機能 全ての項目が入力されている場合、会員情報が登録され、ログイン画面に遷移される
    public function test_registers_the_user_and_redirects_to_the_login_page()
    {
        // ユーザーを登録
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // メール確認後にリダイレクトすることを確認
        $response->assertRedirect('/email/verify');

        // ユーザーのデータがデータベースに保存されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
        ]);

        // メール確認をシミュレート（メールアドレスを確認済みにする）
        $user = \App\Models\User::where('email', 'user@example.com')->first();
        $user->markEmailAsVerified();

        
        $this->actingAs($user);
        $response = $this->get('/mypage/profile');  // ホームなどのページを訪問
        $response->assertStatus(200); // ログイン後にアクセス可能であることを確認
    }

   
    
}
