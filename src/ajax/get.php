<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


$input = json_decode( file_get_contents('php://input'), true );

if( ! isset($input['vendor']) )
{
  http_response_code(400);
  echo json_encode( ['error' => 'Missing vendor'] );
  exit;
}

$vendor = $input['vendor'];
$currentList = [];
$user   = Session::getUser();  // dummy Session class

if( file_exists("data/$user/current_list.yml"))
{
  $data = Yaml::parseFile("data/$user/current_list.yml");
  $currentList = isset($data['items']) ? $data['items'] : [];
}

$vendorItems = array_filter( $currentList, fn($item) => isset($item['vendor']) && $item['vendor'] === $vendor );

echo json_encode( array_values($vendorItems));
