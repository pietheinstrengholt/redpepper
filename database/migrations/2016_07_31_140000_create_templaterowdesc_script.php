<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateRowDescScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::table('t_templates', function ($table) {
			$table->text('row_header_desc')->nullable()->after('template_type');
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
			$table->dropColumn('row_header_desc');
		});
	}
}
