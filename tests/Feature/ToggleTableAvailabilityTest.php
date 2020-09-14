<?php

namespace Tests\Feature;

use App\Table;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ToggleTableAvailabilityTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('passport:install');
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        Artisan::call('db:seed', ['--class' => 'BookingSeeder']);
        $this->signInAdmin();
    }

    /** @test */
    public function toggle_table_availability()
    {
        $this->withoutExceptionHandling();

        $table = Table::inRandomOrder()->first();

        $this->put("/admin/tables/toggle-availability/$table->id")
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'message', 'table']);

        $this->assertDatabaseHas('tables', [
            'availability' => (string) (int) !$table->availability,
            'id' => $table->id,
        ]);
    }
}
