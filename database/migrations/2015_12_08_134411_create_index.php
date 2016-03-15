<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	public function up()
	{
		Schema::table('t_template_columns', function (Blueprint $table) {
			$table->index('template_id');
			$table->index('column_code');
		});
		Schema::table('t_template_rows', function (Blueprint $table) {
			$table->index('template_id');
			$table->index('row_code');
		});
		Schema::table('t_technical', function (Blueprint $table) {
			$table->index('template_id');
			$table->index('row_code');
			$table->index('column_code');
		});
		Schema::table('t_content', function (Blueprint $table) {
			$table->index('template_id');
			$table->index('row_code');
			$table->index('column_code');
		});
		Schema::table('t_changes_content', function (Blueprint $table) {
			$table->index('changerequest_id');
		});
		Schema::table('t_changes_technical', function (Blueprint $table) {
			$table->index('changerequest_id');
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
