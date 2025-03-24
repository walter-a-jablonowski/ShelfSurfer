<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


try {

  $input = json_decode( file_get_contents('php://input'), true);

  $user   = Session::getUser();  // dummy Session class
  $places = Yaml::parseFile("data/$user/places.yml");
  $currentList = file_exists("data/$user/current_list.yml")
    ? Yaml::parseFile("data/$user/current_list.yml")
    : ['items' => []];

  $text = $input['text'];
  
  if( ! mb_check_encoding($text, 'UTF-8'))
    $text = mb_convert_encoding( $text, 'UTF-8');
  
  $text  = str_replace(["\r\n", "\r"], "\n", $text);
  $lines = explode("\n", $text);

  $items = [];
  $id    = 1;
  
  // Create a simplified section order map
  $sectionOrder = ["Unknown:Unknown" => 0]; // Unknown always first
  $orderIndex = 1;
  
  // Collect section order from places.yml
  foreach( $places as $vendor => $sections ) {
    if( is_array($sections) ) {
      foreach( array_keys($sections) as $section )
        $sectionOrder["$vendor:$section"] = $orderIndex++;
    }
  }
  
  foreach( $lines as $line )
  {
    $line = trim($line); 

    if( empty($line))  continue;
    
    // Skip header and footer
    if( $line === 'Einkaufsliste' || strpos($line, 'Freigegeben über') !== false)
      continue;
    
    // Remove numbering
    $line = trim( preg_replace('/^\d+\.\s*/', '', $line));
    
    if( empty($line))  continue;
    
    $match = findSection($line, $places);
    
    if( $match ) {
      $items[] = [
        'id'      => $id++,
        'text'    => $line,
        'vendor'  => $match['vendor'],
        'section' => $match['section'],
        'order'   => $sectionOrder["{$match['vendor']}:{$match['section']}"] ?? 9999,
        'checked' => false
      ];
    } else {
      // Add unmatched items to "Unknown" section
      $items[] = [
        'id'      => $id++,
        'text'    => $line,
        'vendor'  => 'Unknown', // Special vendor for unmatched items
        'section' => 'Unknown',
        'order'   => $sectionOrder["Unknown:Unknown"] ?? 9999,
        'checked' => false
      ];
    }
  }
  
  // Sort items by order
  usort($items, function($a, $b) {
    return $a['order'] <=> $b['order'];
  });
  
  $currentList['items'] = $items;
  file_put_contents("data/$user/current_list.yml", Yaml::dump($currentList));
  
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

  $item = strtolower( trim( preg_replace('/\s+/', ' ', $item)));

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

        $possibleItem = strtolower( trim( preg_replace('/\s+/', ' ', $possibleItem)));

        if( $item === $possibleItem )
        {
          return [
            'vendor'  => $vendor,
            'section' => $section
          ];
        }
      }
    }
  }
  
  return null;
}
