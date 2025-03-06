<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$places = [];

if( file_exists('data/default_user/places.yml') )
  $places = Yaml::parseFile('data/default_user/places.yml');

$currentList = [];

if( file_exists('data/default_user/current_list.yml') )
  $currentList = Yaml::parseFile('data/default_user/current_list.yml');

require 'view.php';

?>
