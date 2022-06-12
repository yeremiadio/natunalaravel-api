<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'role_name' => 'admin',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('roles')->insert([
            'role_name' => 'user',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('categories')->insert([
            'category_name' => 'Keripik',
            'category_slug' => 'keripik',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        // Category::factory()
        //     ->count(5)
        //     ->create();
        // Product::factory()
        //     ->count(50)
        //     ->create();
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'bumdesnatuna@gmail.com',
            'role_id' => 1,
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::Random(50),
            'password' => Hash::make('password'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        // DB::table('users')->insert([
        //     'name' => 'user',
        //     'email' => 'user@xyz.com',
        //     'role_id' => 2,
        //     'email_verified_at' => Carbon::now(),
        //     'remember_token' => Str::Random(50),
        //     'password' => Hash::make('password'),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now()
        // ]);
    }
}
