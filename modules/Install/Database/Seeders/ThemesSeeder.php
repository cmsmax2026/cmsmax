<?php

namespace Modules\Install\Database\Seeders;

use Illuminate\Database\Seeder;

class ThemesSeeder extends Seeder
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
     'id' => 1,
     'name' => '默认主题',
     'preview' => 'preview.jpg',
     'identification' => 'default',
     'status' => 1,
     'form' => 'local',
     'created_at' => '2024-03-06 14:39:34',
     'updated_at' => '2024-03-06 14:39:38',
  ),
);

        foreach ($rows as $row) {
            \DB::table('themes')->updateOrInsert([
                'identification' => $row['identification'],
            ], $row);
        }


    }
}
