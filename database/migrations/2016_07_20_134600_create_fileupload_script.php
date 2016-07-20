<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileuploadScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::create('t_upload_files', function (Blueprint $table) {
			$table->increments('id');
			$table->string('file_name')->unique();
			$table->string('file_description')->nullable();
			$table->integer('created_by');
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
		//
	}
}
