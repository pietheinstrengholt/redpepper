<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeign extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//on template_id
		Schema::table('t_template_columns', function (Blueprint $table) {
			$table->foreign('template_id')->references('id')->on('t_templates')->onDelete('cascade');
		});
		Schema::table('t_template_rows', function (Blueprint $table) {
			$table->foreign('template_id')->references('id')->on('t_templates')->onDelete('cascade');
		});
		Schema::table('t_technical', function (Blueprint $table) {
			$table->foreign('template_id')->references('id')->on('t_templates')->onDelete('cascade');
		});
		Schema::table('t_content', function (Blueprint $table) {
			$table->foreign('template_id')->references('id')->on('t_templates')->onDelete('cascade');
		});
		Schema::table('t_changes', function (Blueprint $table) {
			$table->foreign('template_id')->references('id')->on('t_templates')->onDelete('cascade');
		});
		Schema::table('t_history_content', function (Blueprint $table) {
			$table->foreign('template_id')->references('id')->on('t_templates')->onDelete('cascade');
		});
		Schema::table('t_history_technical', function (Blueprint $table) {
			$table->foreign('template_id')->references('id')->on('t_templates')->onDelete('cascade');
		});

		//on changerequest_id
		Schema::table('t_changes_content', function (Blueprint $table) {
			$table->foreign('changerequest_id')->references('id')->on('t_changes')->onDelete('cascade');
		});
		Schema::table('t_changes_technical', function (Blueprint $table) {
			$table->foreign('changerequest_id')->references('id')->on('t_changes')->onDelete('cascade');
		});

		//on section_id
		Schema::table('t_templates', function (Blueprint $table) {
			$table->foreign('section_id')->references('id')->on('t_sections')->onDelete('cascade');
		});

		//on username_id
		Schema::table('t_usernames_rights', function (Blueprint $table) {
			$table->foreign('username_id')->references('id')->on('t_usernames')->onDelete('cascade');
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
