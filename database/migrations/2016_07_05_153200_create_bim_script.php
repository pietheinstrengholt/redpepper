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
		if (Schema::hasTable('t_bim_terms_properties')) {
			Schema::drop('t_bim_terms_properties');
		}

		if (Schema::hasTable('t_bim_ontology')) {
			Schema::drop('t_bim_ontology');
		}

		if (Schema::hasTable('t_bim_relation_types')) {
			Schema::drop('t_bim_relation_types');
		}

		if (Schema::hasTable('t_bim_terms')) {
			Schema::drop('t_bim_terms');
		}

		if (Schema::hasTable('t_bim_glossaries')) {
			Schema::drop('t_bim_glossaries');
		}

		if (Schema::hasTable('t_bim_status')) {
			Schema::drop('t_bim_status');
		}

		Schema::create('t_bim_glossaries', function (Blueprint $table) {
			$table->increments('id');
			$table->string('glossary_name')->unique();
			$table->string('glossary_description')->nullable();
			$table->integer('status_id')->unsigned();
			$table->integer('created_by');
			$table->timestamps();
		});

		Schema::create('t_bim_terms', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('glossary_id')->unsigned();
			$table->string('term_name')->nullable();
			$table->string('term_description')->nullable();
			$table->integer('status_id')->unsigned();
			$table->integer('owner_id')->unsigned();
			$table->integer('created_by');
			$table->timestamps();
		});

		Schema::create('t_bim_terms_properties', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('term_id')->unsigned();
			$table->string('property_name');
			$table->string('property_value')->nullable();
			$table->integer('created_by');
			$table->timestamps();
		});

		Schema::create('t_bim_ontology', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('subject_id')->unsigned();
			$table->integer('relation_id')->unsigned();
			$table->integer('object_id')->unsigned();
			$table->integer('status_id')->unsigned();
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

		Schema::create('t_bim_status', function (Blueprint $table) {
			$table->increments('id');
			$table->string('status_name')->unique();
			$table->string('status_description')->nullable();
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

		Schema::table('t_bim_ontology', function (Blueprint $table) {
			$table->foreign('status_id')->references('id')->on('t_bim_status')->onDelete('cascade');
		});

		Schema::table('t_bim_terms', function (Blueprint $table) {
			$table->foreign('status_id')->references('id')->on('t_bim_status')->onDelete('cascade');
		});

		Schema::table('t_bim_glossaries', function (Blueprint $table) {
			$table->foreign('status_id')->references('id')->on('t_bim_status')->onDelete('cascade');
		});

		Schema::table('t_bim_terms_properties', function (Blueprint $table) {
			$table->foreign('term_id')->references('id')->on('t_bim_terms')->onDelete('cascade');
		});

		Schema::table('t_bim_ontology', function ($table) {
			$table->index('subject_id');
			$table->index('object_id');
		});

		// Insert glossary content
		DB::table('t_bim_status')->insert(
			array(
				'status_name' => 'Approved',
				'status_description' => 'Approved status'
			)
		);

		// Insert glossary content
		DB::table('t_bim_glossaries')->insert(
			array(
				'glossary_name' => 'Main Glossary',
				'glossary_description' => 'Main Glossary',
				'status_id' => 1
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
		//
	}
}
