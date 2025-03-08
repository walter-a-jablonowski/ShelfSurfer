<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


$user = Session::getUser();  // dummy Session class

$currentList = file_exists("data/$user/current_list.yml")
  ? Yaml::parseFile("data/$user/current_list.yml")
  : ['items' => []];

echo json_encode([
  'items' => $currentList['items']
]);
