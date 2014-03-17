<?php
class ShoprocketSessionNative {
  
  public static function setMaxLifetime($minutes) {
    
  }
  
  public static function touch() {
    
  }
  
  public static function set($key, $value, $forceSave=false) {
    $_SESSION['shoprocket'][$key] = $value;
  }
  
  public static function drop($key, $forceSave=false) {
    unset($_SESSION['shoprocket'][$key]);
  }
  
  public static function get($key) {
    $value = false;
    if(!isset($_SESSION)) { session_start(); }
    if(isset($_SESSION['shoprocket'][$key])) {
      $value = $_SESSION['shoprocket'][$key];
    }
    return $value;
  }
  
  public function clear() {
    $_SESSION['shoprocket'] = null;
  }
  
  public function destroy() {
    unset($_SESSION['shoprocket']);
  }
  
  public function dump() {
    $out = "Shoprocket Native Session Dump:\n\n";
    $out .= print_r($_SESSION['shoprocket']);
    return $out;
  }

}