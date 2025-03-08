<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


header('Content-Type: application/json');

$user = Session::getUser();

// Get JSON input
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

if( ! isset($data['content']) ) 
{
  echo json_encode(['success' => false, 'message' => 'No content provided']);
  exit;
}

$content = $data['content'];

// Validate YAML syntax
try {

  $parsed = Yaml::parse($content);
  
  // Check if the parsed content has the expected structure
  if( ! is_array($parsed) ) 
  {
    echo json_encode(['success' => false, 'message' => 'Invalid YAML format: root must be an array']);
    exit;
  }
  
  // Write to file
  $result = file_put_contents("data/$user/places.yml", $content);
  if( $result === false ) 
    echo json_encode(['success' => false, 'message' => 'Error write to file']);
  else 
    echo json_encode(['success' => true]);
} 
catch( ParseException $e ) 
{
  echo json_encode(['success' => false, 'message' => 'YAML parse error: ' . $e->getMessage()]);
  exit;
}
