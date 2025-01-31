<?php

namespace Core\Base;

class Auth{

  public static function check(){
    if(isset($_SESSION['user_id'])){
      return true;
    }
     return false;
  }
   public static function login($user, $remember=false){
    Session::set('user_id', $user->id || null);
    $user_cookie_info = crypt($user->id, 10);
    if($remember){
      setcookie('x-smile-auth' ,$user_cookie_info , time() + 60 * (int)config('app', 'session-timeout'));
    }
    
    return true;
  }
  public static function user($table='users')
  {
    $user_id = $_SESSION['user_id']||null;
     if(!$user_id){
      return null;
    }
    $user = Db::query('Select * from ' . $table . ' where id=:id',[
       'id'=>$user_id
    ])->first();
    return $user;

  }
 

  public static function logout()
  {
    session_destroy();
    session_reset();
  }

  public static function guest()
  {
    if(self::check()){
      return false;
    }
    return true;
  }
  public static function attempt($attemptValue,$password, $remember = false,$attemptField='email',$table='users'){

    $user = Db::query('Select * from ' . $table . ' where ' . $attemptField . '=:' . $attemptField,[
      "$attemptField"=>$attemptValue
    ])->first();
    
    if(!$user){
      return false;
    }
    if (!$user->password || !password_verify($password, $user->password)) {
      return false;
    }
    return self::login($user,  $remember);

  }

}