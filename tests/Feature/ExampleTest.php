<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirectToRoute('articles.index');
    }

    public function test_the_articles_page_returns_a_successful_response(): void
    {
        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
    }

    public function test_the_form_articles_page_returns_a_successful_response(): void
    {
        $response = $this->get(route('articles.form'));

        $response->assertStatus(200);
    }

    public function test_an_article_can_be_created_from_form(): void
    {
        $response = $this->post(route('articles.store'), [
            'title' => 'Judul Artikel Uji Coba',
            'description' => 'Deskripsi artikel uji coba.',
            'content' => 'Konten lengkap artikel uji coba.',
            'published_at' => '2026-10-08',
            'category' => 'Nasional',
        ]);

        $response->assertRedirectToRoute('articles.form');
        $this->assertDatabaseHas('articles', [
            'title' => 'Judul Artikel Uji Coba',
        ]);
    }
}
