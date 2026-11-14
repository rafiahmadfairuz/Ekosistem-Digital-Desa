<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FriendlyErrorPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.debug' => false]);
    }

    public function test_not_found_page_is_friendly_and_keeps_404_status(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman yang dicari tidak tersedia.')
            ->assertDontSee('No query results');
    }

    public function test_forbidden_page_is_friendly_and_keeps_403_status(): void
    {
        Route::get('/testing-forbidden-page', fn () => abort(403, 'INTERNAL_PERMISSION_DETAIL'));

        $this->get('/testing-forbidden-page')
            ->assertForbidden()
            ->assertSee('Halaman tidak tersedia atau Anda tidak memiliki akses.')
            ->assertDontSee('INTERNAL_PERMISSION_DETAIL');
    }

    public function test_expired_session_page_is_friendly_and_keeps_419_status(): void
    {
        Route::get('/testing-expired-session-page', fn () => abort(419, 'INTERNAL_CSRF_DETAIL'));

        $this->get('/testing-expired-session-page')
            ->assertStatus(419)
            ->assertSee('Sesi Anda sudah berakhir.')
            ->assertDontSee('INTERNAL_CSRF_DETAIL');
    }

    public function test_server_error_page_does_not_expose_exception_details(): void
    {
        Route::get('/testing-server-error-page', fn () => abort(500, 'SECRET_DATABASE_DETAIL'));

        $this->get('/testing-server-error-page')
            ->assertServerError()
            ->assertSee('Terjadi gangguan saat memproses permintaan.')
            ->assertDontSee('SECRET_DATABASE_DETAIL')
            ->assertDontSee(base_path());
    }
}
