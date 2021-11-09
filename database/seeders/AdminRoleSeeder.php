<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\Models\User::create(
            [
                'name' => 'admin',
                'surname' => 'administrator',
                'phone_number' => '+8562076148754',
                'firebase_uid' => 'b0bdxVi9zNOli6bXbTCD0YzbR1k1',
                'password' => \bcrypt("Kongdee@2021g"),
                'picture' => 'default_image.jpg',
                'created_at' => now(),
                'updated_at' => now(),

                'active' => '1',
                'topic' => '"notification_token_b0bdxVi9zNOli6bXbTCD0YzbR1k1"
',
            ]
        );
        $user->attachRole("superadmin");

    }
}
