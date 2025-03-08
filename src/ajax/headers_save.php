<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';

header('Content-Type: application/json');

try {

  $jsonData = file_get_contents('php://input');
  $data = json_decode($jsonData, true);
  
  $content = $data['content'] ?? null;
  if( ! $content ) 
  {
    echo json_encode(['success' => false, 'message' => 'No content provided']);
    exit;
  }
  
  // Validate YAML

  try {
    Yaml::parse($content);
  } 
  catch( ParseException $e )  {
    echo json_encode(['success' => false, 'message' => 'Invalid YAML: ' . $e->getMessage()]);
    exit;
  }
  
  $user   = Session::getUser();
  $result = file_put_contents("data/$user/headers.yml", $content);

  if( $result === false ) 
    echo json_encode(['success' => false, 'message' => 'Error write to file']);
  else 
    echo json_encode(['success' => true]);
} 
catch( ParseException $e )  {
  echo json_encode(['success' => false, 'message' => 'YAML parse error: ' . $e->getMessage()]);
} 
catch( Exception $e )  {
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
