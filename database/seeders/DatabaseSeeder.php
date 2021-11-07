<?php

namespace Database\Seeders;

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
            'category_name' => 'Category Test',
            'category_slug' => 'category-test',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('products')->insert([
            'title' => 'Example title',
            'description' => 'Example description',
            'category_id' => 1,
            'slug' => 'example-title',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@xyz.com',
            'role_id' => 1,
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::Random(50),
            'password' => Hash::make('password'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
