<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		if (Schema::hasTable('t_activity_log')) {
			Schema::drop('t_activity_log');
		}
		Schema::create('t_activity_log', function (Blueprint $table) {
			$table->increments('id');
			$table->string('description');
			$table->integer('created_by')->unsigned();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
	{
		Schema::drop('t_activity_log');
	}
}
