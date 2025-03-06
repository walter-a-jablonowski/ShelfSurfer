<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$input = json_decode( file_get_contents('php://input'), true );

if( ! isset($input['vendor']) )
{
  http_response_code(400);
  echo json_encode( ['error' => 'Missing vendor'] );
  exit;
}

$vendor = $input['vendor'];
$yamlFile = __DIR__ . '/../current_list.yml';

// Load list
$currentList = [];

if( file_exists($yamlFile) )
{
  $data = Yaml::parseFile($yamlFile);
  $currentList = isset($data['items']) ? $data['items'] : [];
}

// Filter by vendor
$vendorItems = array_filter( $currentList, fn($item) => isset($item['vendor']) && $item['vendor'] === $vendor );

echo json_encode( array_values($vendorItems) );
