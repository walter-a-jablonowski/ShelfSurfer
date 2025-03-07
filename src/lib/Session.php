<?php

/**
 * Simple session management
 */
class Session
{
  /**
   * Get current user
   */
  public static function getUser() : string
  {
    // Start session if not started
    if( session_status() === PHP_SESSION_NONE )
      session_start();
    
    // Return user from session or default
    if( isset($_SESSION['user']) )
      return $_SESSION['user'];
    
    // If walter directory exists, use walter as default user
    if( is_dir(__DIR__ . '/../data/walter') )
      return 'walter';
    
    return 'default_user';
  }
  
  /**
   * Set current user
   */
  public static function setUser( string $user ) : void
  {
    // Start session if not started
    if( session_status() === PHP_SESSION_NONE )
      session_start();
    
    $_SESSION['user'] = $user;
  }
}
