<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$groups = [];

if( file_exists('groups.yml') )
  $groups = Yaml::parseFile('groups.yml');

$currentList = [];

if( file_exists('current_list.yml') )
  $currentList = Yaml::parseFile('current_list.yml');

require 'view.php';

?>
