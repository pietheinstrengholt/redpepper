<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBimScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::drop('t_bim_terms');
		
		Schema::create('t_bim_glossaries', function (Blueprint $table) {
			$table->increments('id');
			$table->string('glossary_name')->unique();
			$table->string('glossary_description')->nullable();
			$table->integer('created_by');
			$table->timestamps();
		});

		// Insert glossary content
		DB::table('t_bim_glossaries')->insert(
			array(
				'glossary_name' => 'Main Glossary',
				'glossary_description' => 'Main Glossary'
			)
		);

		Schema::create('t_bim_terms', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('glossary_id')->unsigned();
			$table->string('term_name')->nullable();
			$table->string('term_description')->nullable();
			$table->integer('created_by');
			$table->timestamps();
		});

		Schema::create('t_bim_ontology', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('subject_id')->unsigned();
			$table->integer('relation_id')->unsigned();
			$table->integer('object_id')->unsigned();
			$table->integer('created_by');
			$table->timestamps();
		});

		Schema::create('t_bim_relation_types', function (Blueprint $table) {
			$table->increments('id');
			$table->string('relation_name')->unique();
			$table->string('relation_color')->nullable();
			$table->string('relation_description')->nullable();
			$table->integer('created_by');
			$table->timestamps();
		});

		Schema::table('t_bim_ontology', function (Blueprint $table) {
			$table->foreign('subject_id')->references('id')->on('t_bim_terms')->onDelete('cascade');
		});

		Schema::table('t_bim_ontology', function (Blueprint $table) {
			$table->foreign('object_id')->references('id')->on('t_bim_terms')->onDelete('cascade');
		});

		Schema::table('t_bim_terms', function (Blueprint $table) {
			$table->foreign('glossary_id')->references('id')->on('t_bim_glossaries')->onDelete('cascade');
		});

		Schema::table('t_bim_ontology', function (Blueprint $table) {
			$table->foreign('relation_id')->references('id')->on('t_bim_relation_types')->onDelete('cascade');
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
