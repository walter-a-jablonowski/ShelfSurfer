<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$input = json_decode( file_get_contents('php://input'), true );

if( ! isset($input['vendor']) )
{
  http_response_code(400);
  echo json_encode( ['error' => 'Missing vendor'] );
  exit;
}

$vendor = $input['vendor'];
$currentList = [];

if( file_exists('current_list.yml'))
{
  $data = Yaml::parseFile('current_list.yml');
  $currentList = isset($data['items']) ? $data['items'] : [];
}

// Filter by vendor
$vendorItems = array_filter( $currentList, fn($item) => isset($item['vendor']) && $item['vendor'] === $vendor );

echo json_encode( array_values($vendorItems));
