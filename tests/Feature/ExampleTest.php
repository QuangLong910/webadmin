<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_trang_quan_tri_chua_dang_nhap_chuyen_ve_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_trang_dang_nhap_mo_duoc(): void
    {
        $this->get('/login')->assertOk()->assertSee('Đăng nhập');
    }
}
