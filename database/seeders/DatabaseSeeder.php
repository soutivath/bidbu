<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
      //  $this->call(\App\database\seeders\LaratrustSeeder::class);
      $this->call(LaratrustSeeder::class);
      $this->call(AdminRoleSeeder::class);
      $this->call(AddRoleToAdmin::class);
      $this->call(LanguageSeeder::class);


    }
}
