<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

try {
  $currentListFile = __DIR__ . '/../../current_list.yml';
  $currentList = file_exists($currentListFile)
    ? Yaml::parseFile($currentListFile)
    : ['items' => []];

  $input = json_decode(file_get_contents('php://input'), true);
  $id = $input['id'];
  $checked = $input['checked'];
  
  foreach( $currentList['items'] as &$item ) {
    if( $item['id'] == $id ) {
      $item['checked'] = $checked;
      break;
    }
  }
  
  file_put_contents( $currentListFile, Yaml::dump($currentList));
  echo json_encode(['success' => true]);
}
catch( Exception $e ) {
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}
