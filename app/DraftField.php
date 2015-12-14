<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftField extends Model
{
  protected $fillable = ['changerequest_id','property','content'];
  protected $guarded = [];
  protected $table = 't_changes_cells';

  public function changerequest()
  {
    return $this->belongsTo('App\ChangeRequest');
  }

}

?>
