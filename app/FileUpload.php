<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
	protected $fillable = ['file_name','file_description','created_by'];
	protected $guarded = [];
	protected $table = 't_upload_files';
}

?>