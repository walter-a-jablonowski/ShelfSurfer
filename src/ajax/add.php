<?php

$input = json_decode(file_get_contents('php://input'), true);

if( ! isset($input['vendor']) || ! isset($input['section']) || ! isset($input['text']))
{
  http_response_code(400);
  echo json_encode(['error' => 'Missing required fields']);
  exit;
}

$vendor  = $input['vendor'];
$section = $input['section'];
$text    = trim($input['text']);

if( empty($text))
{
  http_response_code(400);
  echo json_encode(['error' => 'Text cannot be empty']);
  exit;
}

// Load current list
$currentList = [];
if( file_exists('../current_list.yml'))
  $currentList = Symfony\Component\Yaml\Yaml::parseFile('../current_list.yml');

// Add new item
$currentList[] = [
  'id'      => uniqid(),
  'vendor'  => $vendor,
  'section' => $section,
  'text'    => $text,
  'checked' => false
];

// Save list
file_put_contents('../current_list.yml', Symfony\Component\Yaml\Yaml::dump($currentList, 4, 2));

echo json_encode(['success' => true]);
