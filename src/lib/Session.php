<?php

// Dummy Sesion

class Session
{
  public static function getUser() : string
  {
    if( is_dir('data/walter') )
      return 'walter';
    
    return 'default_user';
  }
}
