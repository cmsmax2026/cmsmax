<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;

class ModuleAuthGroupUserSeeder extends Seeder
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
            array (
                'id' => 1,
                'uid' => 1,
                'group_id' => 1,
                'created_at' => '2023-12-29 11:59:20',
                'updated_at' => '2023-12-29 11:59:22',
            ),
        );

        foreach ($rows as $row) {
            \DB::table('module_auth_group_user')->updateOrInsert([
                'uid' => $row['uid'],
                'group_id' => $row['group_id'],
            ], $row);
        }
        
        
    }
}
