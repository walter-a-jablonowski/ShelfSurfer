<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

try {

  $currentList = file_exists('data/default_user/current_list.yml')
    ? Yaml::parseFile('data/default_user/current_list.yml')
    : ['items' => []];

  $vendor = $_GET['vendor'];
  echo json_encode([
    'items' => $currentList['items']
  ]);
}
catch( Exception $e ) {
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}
