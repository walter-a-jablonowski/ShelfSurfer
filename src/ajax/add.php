<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


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
  echo json_encode( ['error' => "Text cann't be empty"] );
  exit;
}

// Load current list

$user = Session::getUser();  // dummy Session class

$data = [
  'items' => []
];

if( file_exists("data/$user/current_list.yml") )
{
  $data = Yaml::parseFile("data/$user/current_list.yml");
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

file_put_contents("data/$user/current_list.yml", Yaml::dump($data, 4, 2) );

// Return success with the new item for immediate display
echo json_encode([
  'success' => true,
  'item'    => $newItem
]);
