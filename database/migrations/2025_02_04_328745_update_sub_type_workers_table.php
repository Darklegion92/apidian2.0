<?php

use App\SubTypeWorker;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateSubTypeWorkersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::table('sub_type_workers')->where('code', '02')->delete();
        DB::table('sub_type_workers')->where('code', '03')->delete();
        DB::table('sub_type_workers')->where('code', '04')->delete();
        DB::table('sub_type_workers')->where('code', '12')->delete();
        DB::table('sub_type_workers')->where('code', '16')->delete();
        DB::table('sub_type_workers')->where('code', '18')->delete();
        DB::table('sub_type_workers')->where('code', '19')->delete();
        DB::table('sub_type_workers')->where('code', '20')->delete();
        DB::table('sub_type_workers')->where('code', '21')->delete();
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        SubTypeWorker::create(['name' => 'Independiente pensionado por vejez activo', 'code' => '02']);
        SubTypeWorker::where('code', '02')->update(['id' => 3, 'name' => 'Independiente pensionado por vejez activo', 'code' => '02']);

        SubTypeWorker::create(['name' => 'Cotizante no obligado a cotizar a pensión por edad', 'code' => '03']);
        SubTypeWorker::where('code', '03')->update(['id' => 4, 'name' => 'Cotizante no obligado a cotizar a pensión por edad', 'code' => '03']);

        SubTypeWorker::create(['name' => 'Cotizante con requisitos cumplidos para pensión', 'code' => '04']);
        SubTypeWorker::where('code', '04')->update(['id' => 5, 'name' => 'Cotizante con requisitos cumplidos para pensión', 'code' => '04']);

        SubTypeWorker::create(['name' => 'Cotizante a quien se le ha reconocido indemnización sustitutiva o devolución de saldos', 'code' => '12']);
        SubTypeWorker::where('code', '12')->update(['id' => 6, 'name' => 'Cotizante a quien se le ha reconocido indemnización sustitutiva o devolución de saldos', 'code' => '12']);

        SubTypeWorker::create(['name' => 'Cotizante perteneciente a un régimen de exceptuado de pensiones a entidades autorizadas para recibir aportes exclusivamente de un grupo de sus propios trabajadores', 'code' => '16']);
        SubTypeWorker::where('code', '16')->update(['id' => 7, 'name' => 'Cotizante perteneciente a un régimen de exceptuado de pensiones a entidades autorizadas para recibir aportes exclusivamente de un grupo de sus propios trabajadores', 'code' => '16']);

        SubTypeWorker::create(['name' => 'Cotizante pensionado con mesada superior a 25 smlmv	18', 'code' => '18']);
        SubTypeWorker::where('code', '18')->update(['id' => 8, 'name' => 'Cotizante pensionado con mesada superior a 25 smlmv	18', 'code' => '18']);

        SubTypeWorker::create(['name' => 'Residente en el exterior afiliado voluntario al sistema general de pensiones y/o afiliado', 'code' => '19']);
        SubTypeWorker::where('code', '19')->update(['id' => 9, 'name' => 'Residente en el exterior afiliado voluntario al sistema general de pensiones y/o afiliado', 'code' => '19']);

        SubTypeWorker::create(['name' => 'Conductores del servicio público de transporte terrestre automotor individual de pasajeros en vehículos taxi decreto 1047 de 2014', 'code' => '20']);
        SubTypeWorker::where('code', '20')->update(['id' => 10, 'name' => 'Conductores del servicio público de transporte terrestre automotor individual de pasajeros en vehículos taxi decreto 1047 de 2014', 'code' => '20']);

        SubTypeWorker::create(['name' => 'Conductores servicio taxi no aporte pensión dec. 1047', 'code' => '21']);
        SubTypeWorker::where('code', '21')->update(['id' => 11, 'name' => 'Conductores servicio taxi no aporte pensión dec. 1047', 'code' => '21']);
    }
}
