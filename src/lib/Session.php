<?php

// Dummy Session class

class Session
{
  public static function getUser() : string
  {
    if( is_dir('data/walter'))
      return 'walter';
    else
      return 'default_user';
  }
}
