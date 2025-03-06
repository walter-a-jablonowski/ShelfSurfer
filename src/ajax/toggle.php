<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

try {

  $currentList = file_exists('current_list.yml')
    ? Yaml::parseFile('current_list.yml')
    : ['items' => []];

  $input   = json_decode(file_get_contents('php://input'), true);
  $id      = $input['id'];
  $checked = $input['checked'];
  
  foreach( $currentList['items'] as &$item ) {
    if( $item['id'] == $id ) {
      $item['checked'] = $checked;
      break;
    }
  }
  
  file_put_contents('current_list.yml', Yaml::dump($currentList));
  echo json_encode(['success' => true]);
}
catch( Exception $e ) {
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}
