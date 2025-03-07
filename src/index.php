<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


// TASK: can be improved

$user   = Session::getUser();  // dummy Session class
$places = [];

if( file_exists("data/$user/places.yml") )
  $places = Yaml::parseFile("data/$user/places.yml");

$headers = [];

if( file_exists("data/$user/headers.yml") )
  $headers = Yaml::parseFile("data/$user/headers.yml");

$currentList = [];

if( file_exists("data/$user/current_list.yml") )
  $currentList = Yaml::parseFile("data/$user/current_list.yml");

require 'view.php';

?>
