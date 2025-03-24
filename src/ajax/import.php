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
  
  // Create a section order map to preserve the original order from places.yml
  $sectionOrder = [];
  $orderIndex = 1;     // start from 1 to leave 0 for Unknown
  
  // Add Unknown section as the first one (index 0)
  $sectionOrder["Unknown:Unknown"] = 0;
  
  foreach( $places as $vendor => $sections )
  {
    if( ! is_array($sections) )
      continue;
      
    foreach( array_keys($sections) as $section )
      $sectionOrder["$vendor:$section"] = $orderIndex++;
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

  // Add debug logging
  // $logFile = fopen('import_debug.log', 'a');
  // fwrite($logFile, "\n===== Processing item: '$item' =====\n");
  // fclose($logFile);

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

        // Debug log
        // $logFile = fopen('import_debug.log', 'a');
        // fwrite($logFile, "Comparing: '$item' with '$possibleItem'\n");
        // fclose($logFile);

        // Try exact match first
        if( $item === $possibleItem )
        {
          // $logFile = fopen('import_debug.log', 'a');
          // fwrite($logFile, "EXACT MATCH FOUND! Vendor: $vendor, Section: $section\n");
          // fclose($logFile);
          
          return [
            'vendor'  => $vendor,
            'section' => $section
          ];
        }
        
        // Try partial matching - check if one contains the other
        if( strpos($item, $possibleItem) !== false || strpos($possibleItem, $item) !== false )
        {
          // $logFile = fopen('import_debug.log', 'a');
          // fwrite($logFile, "PARTIAL MATCH FOUND! Vendor: $vendor, Section: $section\n");
          // fclose($logFile);
          
          return [
            'vendor'  => $vendor,
            'section' => $section
          ];
        }
      }
    }
  }

  // Log when no match is found
  // $logFile = fopen('import_debug.log', 'a');
  // fwrite($logFile, "NO MATCH FOUND - Adding to Unknown\n");
  // fclose($logFile);
  
  return null;
}
