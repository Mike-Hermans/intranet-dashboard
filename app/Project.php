<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
  public $timestamps = false;
  protected $hidden = [
    'key'
  ];

  public function getKey() {
    $key = $this->key;
    if ( empty( $this->key ) || mb_strlen( $key ) < 8 ) {
      return null;
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

  public function notes() {
    return $this->hasMany('App\Note');
  }
}
