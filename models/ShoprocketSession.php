<?php
class ShoprocketSession {
  
  /*
  public function __construct() {
    self::$_validRequest = true;
    self::_init();
  }
  */
  
  public static function setMaxLifetime($minutes) {
    
  }
  
  public static function touch() {
    if(ShoprocketCommon::sessionType() == 'database') {
      ShoprocketSessionDb::touch();
    }
  }
  
  public static function set($key, $value, $forceSave=false) {
    if(ShoprocketCommon::sessionType() == 'database') {
      ShoprocketSessionDb::set($key, $value, $forceSave);
    }
    else {
      ShoprocketSessionNative::set($key, $value, $forceSave);
    }
  }
  
  public static function drop($key, $forceSave=false) {
    if(ShoprocketCommon::sessionType() == 'database') {
      ShoprocketSessionDb::drop($key, $forceSave);
    }
    else {
      ShoprocketSessionNative::drop($key, $forceSave);
    }
  }
  
  public static function get($key) {
    $value = false;
    if(ShoprocketCommon::sessionType() == 'database') {
      $value = ShoprocketSessionDb::get($key);
    }
    else {
      $value = ShoprocketSessionNative::get($key);
    }
    return $value;
  }
  
  public function clear() {
    if(ShoprocketCommon::sessionType() == 'database') {
      $value = ShoprocketSessionDb::clear();
    }
    else {
      $value = ShoprocketSessionNative::clear();
    }
  }
  
  public function destroy() {
    if(ShoprocketCommon::sessionType() == 'database') {
      $value = ShoprocketSessionDb::destroy();
    }
    else {
      $value = ShoprocketSessionNative::destroy();
    }
  }
  
  public function dump() {
    if(ShoprocketCommon::sessionType() == 'database') {
      $value = ShoprocketSessionDb::dump();
    }
    else {
      $value = ShoprocketSessionNative::dump();
    }
	  return $value;
  }
  
}