<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\DigitalPlatform;
use App\Models\DigitalSeries;
use App\Models\PhysicalVolume;
use App\Models\User;
use App\Models\UserDigitalTracking;
use App\Models\UserPhysicalCollection;
use App\Models\Work;
use Database\Seeders\DigitalPlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
            ->assertJsonPath('data.title', 'Haikyuu!!');

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
            ->assertJsonPath('data.volume_number', '8.00');

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
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.physical_volume_id', $volume->id);

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

    public function test_authenticated_user_can_create_work_tracking(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $work = Work::create(['title' => 'Slam Dunk']);

        $this->postJson('/api/my/work-tracking', [
            'work_id' => $work->id,
            'follow_physical_releases' => true,
            'preferred_language' => 'espanol',
            'preferred_country' => 'Espana',
            'last_owned_volume_number' => 4,
        ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.work_id', $work->id)
            ->assertJsonPath('data.last_owned_volume_number', '4.00');

        $this->assertDatabaseHas('user_work_trackings', [
            'user_id' => $user->id,
            'work_id' => $work->id,
        ]);
    }

    public function test_authenticated_user_can_list_seeded_digital_platforms(): void
    {
        $this->seed(DigitalPlatformSeeder::class);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/digital-platforms')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Manta'])
            ->assertJsonFragment(['name' => 'WEBTOON']);
    }

    public function test_authenticated_user_can_create_digital_series(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $platform = DigitalPlatform::create(['name' => 'Tapas']);

        $this->postJson('/api/digital-series', [
            'platform_id' => $platform->id,
            'title' => 'A Business Proposal',
            'platform_url' => 'https://tapas.io/series/a-business-proposal',
            'language' => 'ingles',
            'status' => 'completed',
            'latest_episode_detected' => 125,
        ])
            ->assertCreated()
            ->assertJsonPath('data.platform_id', $platform->id)
            ->assertJsonPath('data.title', 'A Business Proposal')
            ->assertJsonPath('data.latest_episode_detected', '125.00');

        $this->assertDatabaseHas('digital_series', [
            'platform_id' => $platform->id,
            'title' => 'A Business Proposal',
        ]);
    }

    public function test_works_are_listed_with_pagination_data(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Work::insert(collect(range(1, 3))->map(fn (int $i): array => [
            'title' => 'Work '.$i,
            'type' => 'manga',
            'status' => 'unknown',
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $this->getJson('/api/works?per_page=2')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_works_can_be_filtered_by_search(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Work::create(['title' => 'Frieren', 'author' => 'Kanehito Yamada']);
        Work::create(['title' => 'Dungeon Meshi', 'author' => 'Ryoko Kui']);

        $this->getJson('/api/works?search=Frieren')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Frieren');
    }

    public function test_per_page_is_capped_at_100(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Work::insert(collect(range(1, 105))->map(fn (int $i): array => [
            'title' => 'Paged Work '.$i,
            'type' => 'manga',
            'status' => 'unknown',
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $this->getJson('/api/works?per_page=200')
            ->assertOk()
            ->assertJsonCount(100, 'data')
            ->assertJsonPath('meta.per_page', 100);
    }

    public function test_my_physical_collection_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $owned = $this->createPhysicalVolume('Owned Volume');
        $wishlist = $this->createPhysicalVolume('Wishlist Volume');

        UserPhysicalCollection::create([
            'user_id' => $user->id,
            'physical_volume_id' => $owned->id,
            'ownership_status' => 'owned',
            'reading_status' => 'read',
        ]);
        UserPhysicalCollection::create([
            'user_id' => $user->id,
            'physical_volume_id' => $wishlist->id,
            'ownership_status' => 'wishlist',
            'reading_status' => 'not_started',
        ]);

        $this->getJson('/api/my/physical-collection?ownership_status=wishlist')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.ownership_status', 'wishlist');
    }

    public function test_my_physical_collection_response_uses_data_and_excludes_other_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownVolume = $this->createPhysicalVolume('Mine');
        $otherVolume = $this->createPhysicalVolume('Theirs');
        $ownItem = UserPhysicalCollection::create([
            'user_id' => $owner->id,
            'physical_volume_id' => $ownVolume->id,
            'ownership_status' => 'owned',
            'reading_status' => 'read',
        ]);
        $otherItem = UserPhysicalCollection::create([
            'user_id' => $other->id,
            'physical_volume_id' => $otherVolume->id,
            'ownership_status' => 'owned',
            'reading_status' => 'read',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/my/physical-collection')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonFragment(['id' => $ownItem->id])
            ->assertJsonMissing([
                'user_id' => $other->id,
                'physical_volume_id' => $otherItem->physical_volume_id,
            ]);
    }

    public function test_my_digital_tracking_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $platform = DigitalPlatform::create(['name' => 'WEBTOON']);
        $reading = DigitalSeries::create(['platform_id' => $platform->id, 'title' => 'Reading Series']);
        $paused = DigitalSeries::create(['platform_id' => $platform->id, 'title' => 'Paused Series']);
        UserDigitalTracking::create(['user_id' => $user->id, 'digital_series_id' => $reading->id, 'reading_status' => 'reading']);
        UserDigitalTracking::create(['user_id' => $user->id, 'digital_series_id' => $paused->id, 'reading_status' => 'paused']);

        $this->getJson('/api/my/digital-tracking?reading_status=paused')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reading_status', 'paused');
    }

    public function test_my_alerts_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Alert::create(['user_id' => $user->id, 'alert_type' => 'system', 'title' => 'Unread', 'message' => 'Unread', 'status' => 'unread']);
        Alert::create(['user_id' => $user->id, 'alert_type' => 'system', 'title' => 'Read', 'message' => 'Read', 'status' => 'read']);

        $this->getJson('/api/my/alerts?status=read')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'read');
    }

    public function test_book_lookup_rejects_invalid_isbn(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/book-lookup/isbn/not-a-code')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'ISBN/EAN must be a valid ISBN-10, ISBN-13, or EAN-13 code.');
    }

    public function test_book_lookup_accepts_valid_isbn_and_caches_result(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Http::fake([
            'openlibrary.org/isbn/*' => Http::response([
                'key' => '/books/OL7353617M',
                'title' => 'Matilda',
                'subtitle' => 'A Novel',
                'authors' => [['key' => '/authors/OL34184A']],
                'publishers' => ['Puffin'],
                'publish_date' => '1988-10-01',
                'isbn_10' => ['0140328726'],
                'isbn_13' => ['9780140328721'],
                'number_of_pages' => 240,
                'covers' => [8739161],
            ]),
        ]);

        $this->getJson('/api/book-lookup/isbn/978-0-140-32872-1')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Matilda')
            ->assertJsonPath('data.0.volume_title', 'A Novel')
            ->assertJsonPath('data.0.isbn_13', '9780140328721')
            ->assertJsonPath('data.0.provider', 'open_library');

        $this->assertDatabaseHas('book_metadata_lookups', [
            'provider' => 'open_library',
            'lookup_type' => 'isbn',
            'lookup_value' => '9780140328721',
            'success' => true,
        ]);

        Http::assertSentCount(1);

        $this->getJson('/api/book-lookup/isbn/9780140328721')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Matilda');

        Http::assertSentCount(1);
    }

    public function test_book_lookup_searches_by_title(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Http::fake([
            'openlibrary.org/search.json*' => Http::response([
                'docs' => [
                    [
                        'key' => '/works/OL45804W',
                        'title' => 'Yotsuba&!',
                        'author_name' => ['Kiyohiko Azuma'],
                        'publisher' => ['Yen Press'],
                        'first_publish_year' => 2003,
                        'language' => ['eng'],
                        'isbn' => ['0316073873', '9780316073875'],
                        'cover_i' => 12345,
                    ],
                ],
            ]),
        ]);

        $this->getJson('/api/book-lookup/search?query=Yotsuba')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Yotsuba&!')
            ->assertJsonPath('data.0.authors.0', 'Kiyohiko Azuma')
            ->assertJsonPath('data.0.isbn_13', '9780316073875')
            ->assertJsonPath('data.0.provider', 'open_library');
    }

    public function test_book_lookup_import_creates_work_and_physical_volume(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/book-lookup/import', $this->bookImportPayload())
            ->assertCreated()
            ->assertJsonPath('work.title', 'Yotsuba&!')
            ->assertJsonPath('physical_volume.title', 'Volume 1')
            ->assertJsonPath('collection_item', null);

        $this->assertDatabaseHas('works', [
            'id' => $response->json('work.id'),
            'title' => 'Yotsuba&!',
            'author' => 'Kiyohiko Azuma',
        ]);

        $this->assertDatabaseHas('physical_volumes', [
            'id' => $response->json('physical_volume.id'),
            'isbn' => '9780316073875',
            'ean' => '9780316073875',
        ]);
    }

    public function test_book_lookup_import_reuses_existing_physical_volume(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $first = $this->postJson('/api/book-lookup/import', $this->bookImportPayload())
            ->assertCreated()
            ->json('physical_volume.id');

        $second = $this->postJson('/api/book-lookup/import', $this->bookImportPayload([
            'volume_title' => 'Corrected title',
        ]))
            ->assertCreated()
            ->json('physical_volume.id');

        $this->assertSame($first, $second);
        $this->assertDatabaseCount('physical_volumes', 1);
    }

    public function test_book_lookup_import_can_add_volume_to_my_collection(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/book-lookup/import', $this->bookImportPayload([
            'add_to_collection' => true,
            'ownership_status' => 'wishlist',
            'reading_status' => 'not_started',
        ]))
            ->assertCreated()
            ->assertJsonPath('collection_item.user_id', $user->id)
            ->assertJsonPath('collection_item.ownership_status', 'wishlist');

        $this->assertDatabaseHas('user_physical_collections', [
            'user_id' => $user->id,
            'ownership_status' => 'wishlist',
        ]);
    }

    public function test_guest_cannot_import_book_lookup_to_collection(): void
    {
        $this->postJson('/api/book-lookup/import', $this->bookImportPayload([
            'add_to_collection' => true,
        ]))->assertUnauthorized();
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
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.digital_series_id', $series->id);
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
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.status', 'unread');
    }

    public function test_authenticated_user_can_mark_own_alert_as_read(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $alertId = $this->postJson('/api/my/alerts', [
            'alert_type' => 'system',
            'title' => 'Sync finished',
            'message' => 'New metadata is available.',
        ])->json('data.id');

        $this->putJson("/api/my/alerts/{$alertId}/read")
            ->assertOk()
            ->assertJsonPath('data.status', 'read')
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('alerts', [
            'id' => $alertId,
            'user_id' => $user->id,
            'status' => 'read',
        ]);
    }

    public function test_user_cannot_mark_another_users_alert_as_read(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $alert = Alert::create([
            'user_id' => $owner->id,
            'alert_type' => 'system',
            'title' => 'Private alert',
            'message' => 'Only the owner can read this.',
            'status' => 'unread',
        ]);

        Sanctum::actingAs($other);

        $this->putJson("/api/my/alerts/{$alert->id}/read")
            ->assertNotFound();

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'user_id' => $owner->id,
            'status' => 'unread',
            'read_at' => null,
        ]);
    }

    private function createPhysicalVolume(string $workTitle = 'One Piece'): PhysicalVolume
    {
        $work = Work::create(['title' => $workTitle]);

        return PhysicalVolume::create([
            'work_id' => $work->id,
            'volume_number' => 105,
            'title' => $workTitle.' Volume',
            'language' => 'espanol',
            'country' => 'Espana',
            'publisher' => 'Planeta Comic',
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function bookImportPayload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Yotsuba&!',
            'volume_title' => 'Volume 1',
            'authors' => ['Kiyohiko Azuma'],
            'publisher' => 'Yen Press',
            'published_date' => '2009-09-15',
            'language' => 'ingles',
            'country' => 'Estados Unidos',
            'isbn_13' => '978-0-316-07387-5',
            'ean' => '9780316073875',
            'volume_number' => 1,
            'provider' => 'open_library',
            'provider_id' => '/works/OL45804W',
            'provider_url' => 'https://openlibrary.org/works/OL45804W',
            'raw_data' => ['source' => 'test'],
        ];
    }
}
