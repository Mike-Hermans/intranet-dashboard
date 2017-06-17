<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
  public $timestamps = false;
  
  public function project() {
    return $this->belongsTo('App\Project');
  }
}
