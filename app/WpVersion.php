<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WpVersion extends Model
{
    protected $hidden = [
      'id',
      'project_id',
      'timestamp'
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
