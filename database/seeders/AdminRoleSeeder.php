<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = DB::table("users")->insert(
            [
                'name'=>'admin',
                'surname'=>'administrator',
                'phone_number'=>'8562055646367',
                'firebase_uid'=>'6mKftJ7a60RAm1wzSEs1J7nptsz2',
                'password'=>\bcrypt("admin@2021"),
                'picture'=>'default_image.jpg',
                'created_at'=>now(),
                'updated_at'=>now(),
                'dob'=>'2000-12-20',
                'village'=>'ໜອງໜ້ຽວ',
                'city'=>'ສີໂຄດຕະບອງ',
                'province'=>'ນະຄອນຫຼວງວຽງຈັນ'
            ]
        );
       
    }
}
