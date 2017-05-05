<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $hidden = [
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

    public function status() {
      return $this->hasMany('App\Status');
    }

    public function wp_version() {
      return $this->hasMany('App\WpVersion');
    }

    public function events() {
      return $this->hasMany('App\Events');
    }
}
