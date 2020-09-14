<?php
use App\Booking;
use App\Table;
use App\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i = 0; $i < 20; $i++) {
            $user = create(User::class);
            $table = create(Table::class);
            $booking = create(Booking::class, [
                'user_id' => $user->id,
                'table_id' => $table->id,
            ]);
        }
    }
}
