<?php

namespace Core\Base;

class Rule{
  public $user_input_data =  [];
  public $rules = [];
  public $errors = [];
  public static function validate($user_input_data, $rules){
    self::$user_input_data = $user_input_data;
    self::$rules = $rules;

    foreach(self::$rules as $field => $rule){
      $rule_array = explode('|', $rule);
      foreach($rule_array as $rule){
        if($rule == 'required'){
          self::required($field);
        }
        if($rule == 'min'){
          self::min($field);
        }
        if($rule == 'max'){
          self::max($field);
        }
        if($rule == 'email'){
          self::email($field);
        }
        if($rule == 'url'){
           self::url($field);
         }
      }
    }
    if(count(self::$errors) > 0){
      return self::$errors;
    }
    return true;
  } 


  public static function required($field){
    if(!isset(self::$user_input_data[$field]) || empty(self::$user_input_data[$field])){
      self::$errors[$field][] = 'The ' . $field . ' field is required';
    }
  }
  public static function email($field){
    if(!filter_var(self::$user_input_data[$field], FILTER_VALIDATE_EMAIL)){
      self::$errors[$field][] = 'The ' . $field . ' field must be a valid email address';
    }
  }
  public static function url($field){
    if(!filter_var(self::$user_input_data[$field], FILTER_VALIDATE_URL)){
      self::$errors[$field][] = 'The ' . $field . ' field must be a valid URL';
    }
  }
  public static function min($field){
    $rule_array = explode(':', self::$rules[$field]);
    $min_length = $rule_array[1];
    if(strlen(self::$user_input_data[$field]) < $min_length){
      self::$errors[$field][] = 'The ' . $field . ' field must be at least ' . $min_length . ' characters';
    }
  }
  public static function max($field){
    $rule_array = explode(':', self::$rules[$field]);
    $max_length = $rule_array[1];
    if(strlen(self::$user_input_data[$field]) > $max_length){
      self::$errors[$field][] = 'The ' . $field . ' field must be at most ' . $max_length . ' characters';
    }
  }
}