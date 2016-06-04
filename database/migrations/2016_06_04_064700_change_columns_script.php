<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		DB::statement('ALTER TABLE t_templates MODIFY COLUMN template_longdesc MEDIUMTEXT');
		DB::statement('ALTER TABLE t_sections MODIFY COLUMN section_longdesc MEDIUMTEXT');
		DB::statement('ALTER TABLE t_subjects MODIFY COLUMN subject_longdesc MEDIUMTEXT');
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
