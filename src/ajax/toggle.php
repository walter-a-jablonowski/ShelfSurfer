<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


$user = Session::getUser();  // dummy Session class

$currentList = file_exists("data/$user/current_list.yml")
  ? Yaml::parseFile("data/$user/current_list.yml")
  : ['items' => []];

$input   = json_decode( file_get_contents('php://input'), true);
$id      = $input['id'];
$checked = $input['checked'];

foreach( $currentList['items'] as &$item ) {
  if( $item['id'] == $id ) {
    $item['checked'] = $checked;
    break;
  }
}

file_put_contents("data/$user/current_list.yml", Yaml::dump($currentList));
echo json_encode(['success' => true]);
