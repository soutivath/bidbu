<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Language::create([
            "name"=>"lao"
        ]);
        \App\Models\Language::create([
            "name"=>"english"
        ]);
    }
}
