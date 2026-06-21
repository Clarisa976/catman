<?php

namespace Tests\Feature;

use App\Models\DigitalPlatform;
use App\Models\DigitalSeries;
use App\Models\PhysicalVolume;
use App\Models\User;
use App\Models\UserPhysicalCollection;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatManReadersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Usagi',
            'email' => 'usagi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'usagi@example.com']);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'usagi@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'usagi@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['user' => ['id', 'email'], 'token']);
    }

    public function test_authenticated_user_can_read_me(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_authenticated_user_can_create_work(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/works', [
            'title' => 'Haikyuu!!',
            'author' => 'Haruichi Furudate',
            'type' => 'manga',
            'status' => 'completed',
        ])
            ->assertCreated()
            ->assertJsonPath('title', 'Haikyuu!!');

        $this->assertDatabaseHas('works', ['title' => 'Haikyuu!!']);
    }

    public function test_authenticated_user_can_create_physical_volume(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $work = Work::create(['title' => 'Nana']);

        $this->postJson("/api/works/{$work->id}/physical-volumes", [
            'volume_number' => 8,
            'language' => 'espanol',
            'country' => 'Espana',
            'publisher' => 'Planeta Comic',
            'isbn' => '9781234567890',
        ])
            ->assertCreated()
            ->assertJsonPath('volume_number', '8.00');

        $this->assertDatabaseHas('physical_volumes', [
            'work_id' => $work->id,
            'volume_number' => 8,
        ]);
    }

    public function test_authenticated_user_can_add_physical_volume_to_collection(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $volume = $this->createPhysicalVolume();

        $this->postJson('/api/my/physical-collection', [
            'physical_volume_id' => $volume->id,
            'ownership_status' => 'owned',
            'reading_status' => 'read',
            'is_travel_memory' => true,
            'purchase_country' => 'Japon',
        ])
            ->assertCreated()
            ->assertJsonPath('user_id', $user->id)
            ->assertJsonPath('physical_volume_id', $volume->id);

        $this->assertDatabaseHas('user_physical_collections', [
            'user_id' => $user->id,
            'physical_volume_id' => $volume->id,
            'is_travel_memory' => true,
        ]);
    }

    public function test_user_cannot_list_or_modify_another_users_collection_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $volume = $this->createPhysicalVolume();
        $item = UserPhysicalCollection::create([
            'user_id' => $owner->id,
            'physical_volume_id' => $volume->id,
            'ownership_status' => 'owned',
            'reading_status' => 'not_started',
        ]);

        Sanctum::actingAs($other);

        $this->getJson('/api/my/physical-collection')
            ->assertOk()
            ->assertJsonMissing(['id' => $item->id]);

        $this->putJson("/api/my/physical-collection/{$item->id}", [
            'ownership_status' => 'sold',
        ])->assertNotFound();
    }

    public function test_authenticated_user_can_create_digital_tracking(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $platform = DigitalPlatform::create(['name' => 'Manta']);
        $series = DigitalSeries::create([
            'platform_id' => $platform->id,
            'title' => 'Under the Oak Tree',
            'language' => 'ingles',
        ]);

        $this->postJson('/api/my/digital-tracking', [
            'digital_series_id' => $series->id,
            'reading_status' => 'reading',
            'last_episode_read' => 12,
        ])
            ->assertCreated()
            ->assertJsonPath('user_id', $user->id)
            ->assertJsonPath('digital_series_id', $series->id);
    }

    public function test_authenticated_user_can_create_alert(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/my/alerts', [
            'alert_type' => 'system',
            'title' => 'Welcome',
            'message' => 'CatManReaders is ready.',
        ])
            ->assertCreated()
            ->assertJsonPath('user_id', $user->id)
            ->assertJsonPath('status', 'unread');
    }

    public function test_authenticated_user_can_mark_own_alert_as_read(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $alertId = $this->postJson('/api/my/alerts', [
            'alert_type' => 'system',
            'title' => 'Sync finished',
            'message' => 'New metadata is available.',
        ])->json('id');

        $this->putJson("/api/my/alerts/{$alertId}/read")
            ->assertOk()
            ->assertJsonPath('status', 'read')
            ->assertJsonPath('user_id', $user->id);

        $this->assertDatabaseHas('alerts', [
            'id' => $alertId,
            'user_id' => $user->id,
            'status' => 'read',
        ]);
    }

    private function createPhysicalVolume(): PhysicalVolume
    {
        $work = Work::create(['title' => 'One Piece']);

        return PhysicalVolume::create([
            'work_id' => $work->id,
            'volume_number' => 105,
            'language' => 'espanol',
            'country' => 'Espana',
            'publisher' => 'Planeta Comic',
        ]);
    }
}
