<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;

class ModuleAuthGroupSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        $rows = array (
  0 => 
  array(
     'group_id' => 1,
     'type' => 'admin',
     'group_name' => '超级管理员',
     'role_json' => NULL,
     'created_at' => '2023-12-29 10:02:24',
     'updated_at' => '2024-01-03 11:21:50',
  ),
  1 => 
  array(
     'group_id' => 2,
     'type' => 'member',
     'group_name' => '普通用户',
     'role_json' => NULL,
     'created_at' => '2023-12-29 10:07:25',
     'updated_at' => '2024-01-05 09:57:51',
  ),
);

        foreach ($rows as $row) {
            \DB::table('module_auth_group')->updateOrInsert([
                'group_id' => $row['group_id'],
            ], $row);
        }


    }
}
