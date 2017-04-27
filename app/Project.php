<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $hidden = [
        'id',
        'key'
    ];

    public function getKey() {
      $key = $this->key;
      if ( empty( $this->key ) || mb_strlen( $key ) < 8 ) {
        return false;
      }
      return substr($key, 0, 3) . '...' . substr($key, -3);
    }

    public function matchKey( $key ) {
      if ( $key == $this->key ) {
        return true;
      }
      return false;
    }
}
