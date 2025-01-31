<?php
namespace App\Middlewares;

use Core\Base\Auth;
use Core\Contracts\BaseMiddlewareContract;


class AuthMiddleware extends BaseMiddlewareContract{
  public function guard(){
      dd(Auth::user());
    if(!Auth::check()){
      return redirect('login');
    }
    return true;
  }
}