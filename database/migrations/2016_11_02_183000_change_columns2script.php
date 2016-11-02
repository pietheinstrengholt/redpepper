<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumns2Script extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		DB::statement('ALTER TABLE t_terms MODIFY COLUMN term_description MEDIUMTEXT');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
	{
		//
	}
}
