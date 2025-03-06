<?php

header('Content-Type: application/json');

try 
{
  $input = json_decode( file_get_contents('php://input'), true );
  
  if( ! isset($input['action']) )
  {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action']);
    exit;
  }
  
  $handler = "ajax/{$input['action']}.php";
  
  if( ! file_exists($handler) )
  {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
  }
  
  require $handler;
}
catch( Exception $e ) 
{
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
