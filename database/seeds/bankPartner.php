<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\BankPartner as bankparter;

class bankPartner extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bank_partner=[
           'name' => 'SBI',
           'branch_name' => 'Nikol',
           'account_type' => 'Saving',
           'account_holder_name' => 'Hiren Kavad',
           'account_number' => '12121212121',
           'ifsc' => 'SBIN45454A',
           'image' => 'https://infex-storage.sgp1.cdn.digitaloceanspaces.com/mlm-software/demo/BankPartner/4640b23b7e1197b1bc6002fa87d9a37f-2.png',
        ];
       	$bankpartner=bankparter::create($bank_partner);
    }
}
