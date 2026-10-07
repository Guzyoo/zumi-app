<?php

namespace Tests\Feature;

use App\Models\ParkingSlot;
use App\Events\ParkingSlotUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ParkingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_parking_slots_and_dispatch_event(): void
    {
        $this->withoutExceptionHandling();
        // Fake event Reverb WebSocket agar tidak dipanggil nyata saat test
        Event::fake();

        // 1. Arrange: Buat data slot A1 & A2 di database temporary
        ParkingSlot::factory()->create(['slot_code' => 'A1', 'is_occupied' => false]);
        ParkingSlot::factory()->create(['slot_code' => 'A2', 'is_occupied' => false]);

        // 2. Payload simulasi dari FastAPI
        $payload = [
            'slots' => [
                ['slot_code' => 'A1', 'is_occupied' => true],
                ['slot_code' => 'A2', 'is_occupied' => false],
            ]
        ];

        // 3. Act: Kirim request POST ke endpoint API zumi-app
        $response = $this->postJson('/api/v1/parking/update-slots', $payload);

        // 4. Assert: Cek HTTP Response & Database
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Parking status updated successfully',
                     'total_available' => 1
                 ]);

        $this->assertDatabaseHas('parking_slots', [
            'slot_code' => 'A1',
            'is_occupied' => true,
        ]);

        Event::assertDispatched(ParkingSlotUpdated::class);
    }
}