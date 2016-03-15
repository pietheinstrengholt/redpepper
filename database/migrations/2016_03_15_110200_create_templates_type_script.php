<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTypeScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::table('t_templates', function ($table) {
			$table->integer('type_id')->after('visible')->unsigned();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
	{
		Schema::table('t_templates', function ($table) {
			$table->dropColumn('type_id');
		});
	}
}
