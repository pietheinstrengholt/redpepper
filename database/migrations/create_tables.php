<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_usernames', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('password', 60);
			$table->string('role')->default('guest');
			$table->text('firstname');
			$table->text('lastname');
			$table->integer('department_id')->unsigned();
            $table->rememberToken();
            $table->timestamp('created_at');
        });
		
        Schema::create('t_password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at');
        });		
		
        Schema::create('t_departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('department_name')->unique();
            $table->string('department_description')->nullable();
            $table->timestamps();
        });

        Schema::create('t_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('section_name');
            $table->string('section_description')->nullable();
            $table->string('section_longdesc')->nullable();
			$table->string('reporting_frequency')->nullable();
			$table->integer('subject_id')->default(0);
			$table->string('visible')->default('true');
            $table->timestamps();
        });
		
        Schema::create('t_templates', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('section_id')->unsigned();
            $table->string('template_name');
			$table->string('template_shortdesc');
            $table->string('template_longdesc')->nullable();
            $table->string('frequency_description')->nullable();
			$table->string('reporting_dates_description')->nullable();
			$table->string('links_other_temp_description')->nullable();
			$table->string('process_and_organisation_description')->nullable();
			$table->integer('type_id')->nullable();
			$table->integer('sortorder')->nullable();
			$table->string('visible')->default('true');
            $table->timestamps();
        });
		
        Schema::create('t_template_columns', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('template_id')->unsigned();
            $table->string('column_num');
            $table->string('column_code')->nullable();
			$table->string('column_reference')->nullable();
            $table->string('column_description')->nullable();
			$table->string('column_property')->nullable();
            $table->timestamps();
        });
		
        Schema::create('t_template_rows', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('template_id')->unsigned();
            $table->string('row_num');
            $table->string('row_code')->nullable();
			$table->string('row_reference')->nullable();
            $table->string('row_description')->nullable();
			$table->string('row_property')->nullable();
            $table->timestamps();
        });

        Schema::create('t_template_cells', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('template_id')->unsigned();
            $table->string('row_code');
			$table->string('column_code');
			$table->string('property');
            $table->string('content')->nullable();
            $table->timestamps();
        });
		
        Schema::create('t_content', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('template_id')->unsigned();
            $table->string('field_id');
			$table->string('content_type');
            $table->string('content')->nullable();
            $table->timestamps();
        });
		
        Schema::create('t_technical_info_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type_name')->unique();
            $table->string('type_description')->nullable();
            $table->timestamps();
        });
		
        Schema::create('t_technical_info_souce', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source_name')->unique();
            $table->string('source_description')->nullable();
            $table->timestamps();
        });

        Schema::create('t_technical_info', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('template_id')->unsigned();
			$table->integer('source_id')->unsigned();
			$table->integer('type_id')->unsigned();
            $table->string('row_code');
			$table->string('column_code');
			$table->string('content');
            $table->longText('description')->nullable();
            $table->timestamps();
        });

        Schema::create('t_usernames_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('username_id')->unsigned();
            $table->integer('section_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('t_changes', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('template_id')->unsigned();
            $table->string('row_code');
			$table->string('column_code');
			$table->integer('creator_id');
			$table->integer('approver_id');
			$table->string('status')->default('draft');
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('t_changes_cells', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('changerequest_id')->unsigned();
            $table->string('property');
            $table->string('content')->nullable();
            $table->timestamps();
        });

        Schema::create('t_changes_content', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('changerequest_id')->unsigned();
            $table->string('field_id');
			$table->string('content_type');
            $table->string('content')->nullable();
            $table->timestamps();
        });

        Schema::create('t_changes_technical', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('changerequest_id')->unsigned();
			$table->integer('source_id')->unsigned();
			$table->integer('type_id')->unsigned();
			$table->string('content');
            $table->longText('description')->nullable();
            $table->timestamps();
        });

        Schema::create('t_history_content', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('changerequest_id')->unsigned();
			$table->integer('template_id')->unsigned();
			$table->string('row_code')->nullable();
			$table->string('column_code')->nullable();
			$table->string('content_type');
			$table->string('content')->nullable();
			$table->string('change_type');
            $table->longText('description')->nullable();
			$table->integer('approved_by');
			$table->timestamp('submission_date');
            $table->timestamps();
        });

        Schema::create('t_history_technical', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('changerequest_id')->unsigned();
			$table->integer('template_id')->unsigned();
			$table->string('row_code')->nullable();
			$table->string('column_code')->nullable();
			$table->integer('source_id')->unsigned();
			$table->integer('type_id')->unsigned();			
			$table->string('content')->nullable();
            $table->longText('description')->nullable();
			$table->integer('approved_by');
			$table->timestamp('submission_date');
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
        Schema::drop('t_usernames');
		Schema::drop('t_password_resets');
		Schema::drop('t_usernames_rights');		
		Schema::drop('t_departments');
		Schema::drop('t_sections');
		Schema::drop('t_templates');
		Schema::drop('t_template_columns');
		Schema::drop('t_template_rows');
		Schema::drop('t_template_cells');
		Schema::drop('t_content');
		Schema::drop('t_technical_info');
		Schema::drop('t_technical_info_type');
		Schema::drop('t_technical_info_souce');
		Schema::drop('t_changes_cells');
		Schema::drop('t_changes_content');
		Schema::drop('t_changes_technical');	
		Schema::drop('t_history_content');
		Schema::drop('t_history_technical');	
		Schema::drop('t_changes');	
    }
}
