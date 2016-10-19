<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectidRightsScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::table('t_usernames_rights', function ($table) {
			$table->integer('subject_id')->nullable()->after('username_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
	{
		Schema::table('t_usernames_rights', function ($table) {
			$table->dropColumn('subject_id');
		});
	}
}
