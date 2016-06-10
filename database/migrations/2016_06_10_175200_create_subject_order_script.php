<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectOrderScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::table('t_subjects', function ($table) {
			$table->text('subject_order')->nullable()->after('visible');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
	{
		Schema::table('t_subjects', function ($table) {
			$table->dropColumn('subject_order');
		});
	}
}
