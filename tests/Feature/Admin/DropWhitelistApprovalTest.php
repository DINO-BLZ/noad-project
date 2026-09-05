<?php

namespace Tests\Feature\Admin;

use App\Mail\WhitelistStatusMail;
use App\Models\Drop;
use App\Models\DropWhitelist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DropWhitelistApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function makeDrop(array $overrides = []): Drop
    {
        return Drop::create(array_merge([
            'name' => 'Test Drop',
            'slug' => 'test-drop-'.uniqid(),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3),
            'status' => 'active',
        ], $overrides));
    }

    public function test_admin_can_approve_a_pending_whitelist_request(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $drop = $this->makeDrop();

        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.drops.whitelist.approve', ['drop' => $drop->slug, 'whitelistId' => $whitelist->id])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('approved', $whitelist->fresh()->status);
        Mail::assertSent(WhitelistStatusMail::class);
    }

    public function test_admin_can_reject_a_pending_whitelist_request(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $drop = $this->makeDrop();

        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.drops.whitelist.reject', ['drop' => $drop->slug, 'whitelistId' => $whitelist->id])
        );

        $response->assertRedirect();
        $this->assertEquals('rejected', $whitelist->fresh()->status);
        Mail::assertSent(WhitelistStatusMail::class);
    }

    public function test_approval_is_refused_once_max_whitelist_slots_is_reached(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $drop = $this->makeDrop(['max_whitelist_slots' => 1]);

        $alreadyApprovedUser = User::factory()->create();
        DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $alreadyApprovedUser->id,
            'status' => 'approved',
        ]);

        $pendingUser = User::factory()->create();
        $pendingWhitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $pendingUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.drops.whitelist.approve', ['drop' => $drop->slug, 'whitelistId' => $pendingWhitelist->id])
        );

        $response->assertSessionHasErrors('whitelist');
        $this->assertEquals('pending', $pendingWhitelist->fresh()->status);
        Mail::assertNotSent(WhitelistStatusMail::class);
    }

    public function test_approval_is_allowed_when_max_whitelist_slots_is_null(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $drop = $this->makeDrop(['max_whitelist_slots' => null]);

        $user = User::factory()->create();
        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.drops.whitelist.approve', ['drop' => $drop->slug, 'whitelistId' => $whitelist->id])
        );

        $response->assertSessionHasNoErrors();
        $this->assertEquals('approved', $whitelist->fresh()->status);
    }

    public function test_non_admin_cannot_approve_a_whitelist_request(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $drop = $this->makeDrop();

        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.drops.whitelist.approve', ['drop' => $drop->slug, 'whitelistId' => $whitelist->id])
        );

        $response->assertForbidden();
        $this->assertEquals('pending', $whitelist->fresh()->status);
    }

    public function test_non_admin_cannot_reject_a_whitelist_request(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $drop = $this->makeDrop();

        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.drops.whitelist.reject', ['drop' => $drop->slug, 'whitelistId' => $whitelist->id])
        );

        $response->assertForbidden();
        $this->assertEquals('pending', $whitelist->fresh()->status);
    }

    public function test_guest_cannot_approve_a_whitelist_request(): void
    {
        $user = User::factory()->create();
        $drop = $this->makeDrop();

        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->post(
            route('admin.drops.whitelist.approve', ['drop' => $drop->slug, 'whitelistId' => $whitelist->id])
        );

        $response->assertRedirect(route('login'));
        $this->assertEquals('pending', $whitelist->fresh()->status);
    }
}