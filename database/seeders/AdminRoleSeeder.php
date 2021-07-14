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
                'phone_number' => '+8562052416362',
                'firebase_uid' => 'L0acBwThDpfybrHhM0PpSDNxua13',
                'password' => \bcrypt("admin@2020"),
                'picture' => 'default_image.jpg',
                'created_at' => now(),
                'updated_at' => now(),
                'dob' => '2000-12-20',
                'village' => 'ໜອງໜ້ຽວ',
                'city' => 'ສີໂຄດຕະບອງ',
                'province' => 'ນະຄອນຫຼວງວຽງຈັນ',
                'active' => '1',
                'topic' => '"notification_token_L0acBwThDpfybrHhM0PpSDNxua13"
',
            ]
        );
        $user->attachRole("superadmin");

    }
}
