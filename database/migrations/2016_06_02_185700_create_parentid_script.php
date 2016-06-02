<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParentidScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::table('t_templates', function ($table) {
			$table->integer('parent_id')->nullable()->after('section_id');
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
			$table->dropColumn('parent_id');
		});
	}
}
