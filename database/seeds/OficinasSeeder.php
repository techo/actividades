 <?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OficinasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Oficina', 2)->create();
    }
}