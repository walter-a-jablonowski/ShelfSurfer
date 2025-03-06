<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$input = json_decode( file_get_contents('php://input'), true );

if( ! isset($input['vendor']) || ! isset($input['section']) || ! isset($input['text']) )
{
  http_response_code(400);
  echo json_encode( ['error' => 'Missing required fields'] );
  exit;
}

$vendor  = $input['vendor'];
$section = $input['section'];
$text    = trim($input['text']);

if( ! $text )
{
  http_response_code(400);
  echo json_encode( ['error' => 'Text cannot be empty'] );
  exit;
}

// Load current list
$yamlFile = __DIR__ . '/../current_list.yml';
$data = [
  'items' => []
];

if( file_exists($yamlFile) )
{
  $data = Yaml::parseFile($yamlFile);
  if( ! isset($data['items']) )
    $data['items'] = [];
}

// Add new item
$newItem = [
  'id'      => uniqid(),
  'text'    => $text,
  'vendor'  => $vendor,
  'section' => $section,
  'checked' => false
];

$data['items'][] = $newItem;

// Save list
file_put_contents( $yamlFile, Yaml::dump($data, 4, 2) );

// Return success with the new item for immediate display
echo json_encode([
  'success' => true,
  'item'    => $newItem
]);
