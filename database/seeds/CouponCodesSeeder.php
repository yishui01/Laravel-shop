<?php

use Illuminate\Database\Seeder;

class CouponCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\CouponCode::class, 20)->create();
    }
}
