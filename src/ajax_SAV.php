<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

header('Content-Type: application/json');

function findSection($item, $groups) 
{
  if( ! is_array($groups) || ! isset($groups['vendors']) || ! is_array($groups['vendors']))
    return null;

  foreach( $groups['vendors'] as $vendor => $sections )
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

try {

  $groups = Yaml::parseFile(__DIR__ . '/groups.yml');
  $currentListFile = __DIR__ . '/current_list.yml';
  $currentList     = file_exists($currentListFile)
    ? Yaml::parseFile($currentListFile)
    : ['items' => []];

  $input  = json_decode( file_get_contents('php://input'), true);
  $action = $_SERVER['REQUEST_METHOD'] === 'POST' ?  $input['action'] : $_GET['action'];

  switch( $action )
  {
    case 'import':
    
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
        
        $match = findSection($line, $groups);
        
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
      file_put_contents( $currentListFile, Yaml::dump($currentList));
      
      echo json_encode([
        'success' => true,
        'items' => $items
      ]);
      break;
      
    case 'list':

      $vendor = $_GET['vendor'];
      echo json_encode([
        'items' => $currentList['items']
      ]);
      break;
      
    case 'toggle':

      $id      = $input['id'];
      $checked = $input['checked'];
      
      foreach($currentList['items'] as &$item) {
        
        if( $item['id'] == $id) {
          $item['checked'] = $checked;
          break;
        }
      }
      
      file_put_contents( $currentListFile, Yaml::dump($currentList));
      echo json_encode(['success' => true]);
      break;
      
    default:
      http_response_code(400);
      echo json_encode(['error' => 'Invalid action']);
  }
}
catch( Exception $e ) {
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}
