<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$groups = Yaml::parseFile( __DIR__ . '/groups.yml');
$currentList = [];

if( file_exists('current_list.yml'))
  $currentList = Yaml::parseFile('current_list.yml');

require 'view.php';

?>
