<?php 

namespace App\Controllers\Auth;

use Core\Base\Auth;
use Core\Base\Rule;

class AuthController{
  public function login(){
    return view('auth/login');
  }
  public function register(){
    return view('auth/register');
  }

  public function authenticate()

  {
    $errors=Rule::validate(request(), [
      'email'=>"min:8",
      "password"=>'min:8'
    ]);
    if($errors){
      redirect();
    }
    $loginIn=Auth::attempt(request('email'),request('password'),request('remember-me'));
    if($loginIn==false){
      return redirect('/login');
    }
    return redirect('admin/dashboard');
  }
}