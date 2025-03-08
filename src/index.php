<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


// TASK: can be improved

$user   = Session::getUser();  // dummy Session class
$placesTxt = '';
$places = [];

if( file_exists("data/$user/places.yml") )
{
  $placesTxt = file_get_contents("data/$user/places.yml");
  $places = Yaml::parse( $placesTxt );
}

$headersTxt = '';
$headers = [];

if( file_exists("data/$user/headers.yml") )
{
  $headersTxt = file_get_contents("data/$user/headers.yml");
  $headers = Yaml::parse( $headersTxt );
}

$currentList = [];

if( file_exists("data/$user/current_list.yml") )
  $currentList = Yaml::parseFile("data/$user/current_list.yml");

require 'view.php';

?>
