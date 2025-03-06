<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

try {

  $currentListFile = 'current_list.yml';
  $currentList = file_exists($currentListFile)
    ? Yaml::parseFile($currentListFile)
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
