<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\Rank;
class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rank_data=[
           		'name' => 'AFFILATE',
           		'capping' => '100000',
           	
        ];
       	Rank::create($rank_data);
    }
}
