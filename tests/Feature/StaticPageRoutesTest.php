<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StaticPageRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_literal_blade_route_reference_is_registered(): void
    {
        $missingRoutes = [];

        foreach (File::allFiles(resource_path('views')) as $viewFile) {
            preg_match_all(
                '/(?<!>)route\(\s*[\'\"]([^\'\"]+)[\'\"]/',
                $contents = File::get($viewFile->getPathname()),
                $matches
            );

            foreach (array_unique($matches[1]) as $routeName) {
                $isGuarded = preg_match(
                    '/Route::has\(\s*[\'\"]'.preg_quote($routeName, '/').'[\'\"]\s*\)/',
                    $contents
                );

                if (! Route::has($routeName) && ! $isGuarded) {
                    $missingRoutes[] = $routeName.' in '.$viewFile->getRelativePathname();
                }
            }
        }

        $this->assertSame([], $missingRoutes);
    }

    public function test_public_static_pages_are_available(): void
    {
        $this->get(route('about'))->assertOk();
        $this->get(route('contact'))->assertOk();
        $this->get(route('shipping'))->assertOk();
        $this->get(route('returns'))->assertOk();
        $this->get(route('faq'))->assertOk();
        $this->get(route('cgv'))->assertOk();
        $this->get(route('legal'))->assertOk();
        $this->get(route('collections.index'))->assertOk();
        $this->get(route('journal.index'))->assertOk();
        $this->get(route('journal.show', 1))->assertOk();
    }

    public function test_newsletter_and_whitelist_forms_post_to_valid_routes(): void
    {
        $user = User::factory()->create();

        $this->post(route('newsletter.subscribe'), ['email' => 'demo@example.com'])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('whitelist.store'), ['drop_id' => 1])
            ->assertRedirect();
    }
}
