<?php

header('Content-Type: application/json');

try {
  
  $input  = json_decode( file_get_contents('php://input'), true);
  $action = $_SERVER['REQUEST_METHOD'] === 'POST' ? $input['action'] : $_GET['action'];
  
  $handlerFile = "ajax/{$action}.php";
  
  if( ! file_exists($handlerFile) ) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
  }
  
  require_once $handlerFile;
}
catch( Exception $e ) {
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}
