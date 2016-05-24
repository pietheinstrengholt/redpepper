<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectsScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::create('t_subjects', function (Blueprint $table) {
			$table->increments('id');
			$table->text('subject_name');
			$table->string('subject_description');
			$table->longText('subject_longdesc')->nullable();
			$table->timestamps();
		});

		// Insert subjects content
		DB::table('t_subjects')->insert(
			array(
				'subject_name' => 'COREP',
				'subject_description' => 'Common Reporting (COREP) is the standardized reporting framework issued by the EBA for the Capital Requirements Directive reporting.'
			)
		);

		// Insert subjects content
		DB::table('t_subjects')->insert(
			array(
				'subject_name' => 'FINREP',
				'subject_description' => 'FINREP reporting is a standardized EU-wide framework for reporting financial (accounting) data.'
			)
		);

		// Insert subjects content
		DB::table('t_subjects')->insert(
			array(
				'subject_name' => 'Liquidity reports',
				'subject_description' => 'Liquidity covers the Liquidity coverage ratio templates, the stable funding templates and other liquidity reports.'
			)
		);

		// Insert subjects content
		DB::table('t_subjects')->insert(
			array(
				'subject_name' => 'Other reports',
				'subject_description' => 'This section covers all other regulatory reports, e.g. issues by the local NSA (National Supervisory Authority).'
			)
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
	{
		Schema::drop('t_subjects');
	}
}
