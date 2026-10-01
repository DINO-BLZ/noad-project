<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_email_is_stored_as_a_newsletter_subscriber(): void
    {
        $this->from(route('home'))
            ->post(route('newsletter.subscribe'), ['email' => ' Member@Example.com '])
            ->assertRedirect(route('home'))
            ->assertSessionHas('newsletter_status', 'Votre inscription à la newsletter est confirmée.');

        $subscriber = NewsletterSubscriber::sole();

        $this->assertSame('member@example.com', $subscriber->email);
        $this->assertNotNull($subscriber->subscribed_at);
    }

    public function test_repeated_subscription_is_idempotent(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'member@example.com']);
        $this->post(route('newsletter.subscribe'), ['email' => 'MEMBER@example.com'])
            ->assertSessionHas('newsletter_status', 'Cette adresse est déjà inscrite à la newsletter.');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_invalid_email_is_not_stored(): void
    {
        $this->from(route('home'))
            ->post(route('newsletter.subscribe'), ['email' => 'not-an-email'])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }
}
