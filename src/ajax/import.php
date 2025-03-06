<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

try {

  $places = Yaml::parseFile('data/default_user/places.yml');
  $currentList = file_exists('data/default_user/current_list.yml')
    ? Yaml::parseFile('data/default_user/current_list.yml')
    : ['items' => []];

  $input = json_decode(file_get_contents('php://input'), true);
  $text  = $input['text'];
  $lines = explode("\n", $text);
  $items = [];
  $id    = 1;
  
  foreach( $lines as $line)
  {
    $line = trim($line); 

    if( empty($line))  continue;
    
    // Skip header and footer
    if( $line === 'Einkaufsliste' || strpos($line, 'Freigegeben über') !== false)
      continue;
    
    // Remove numbering
    $line = preg_replace('/^\d+\.\s*/', '', $line);
    
    if( empty($line))  continue;
    
    $match = findSection($line, $places);
    
    if( $match )
      $items[] = [
        'id'      => $id++,
        'text'    => $line,
        'vendor'  => $match['vendor'],
        'section' => $match['section'],
        'checked' => false
      ];
  }
  
  $currentList['items'] = $items;
  file_put_contents('data/default_user/current_list.yml', Yaml::dump($currentList));
  
  echo json_encode([
    'success' => true,
    'items'   => $items
  ]);
}
catch( Exception $e ) {
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}


function findSection($item, $places) 
{
  if( ! is_array($places))
    return null;

  foreach( $places as $vendor => $sections )
  {
    if( ! is_array($sections) )
      continue;

    foreach( $sections as $section => $possibleItems )
    {
      if( ! is_array($possibleItems))
        continue;

      foreach( $possibleItems as $possibleItem )
      {
        if( ! is_string($possibleItem) )
          continue;

        if( stripos($item, $possibleItem) !== false )
          return [
            'vendor'  => $vendor,
            'section' => $section
          ];
      }
    }
  }

  return null;
}
