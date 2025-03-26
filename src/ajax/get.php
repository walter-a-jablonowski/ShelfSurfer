<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


// Get the current list for a vendor when selected in UI

$input = json_decode( file_get_contents('php://input'), true );

if( ! isset($input['vendor']) )
{
  http_response_code(400);
  echo json_encode( ['error' => 'Missing vendor'] );
  exit;
}

$vendor = $input['vendor'];
$currentList = [];
$user   = Session::getUser();  // dummy Session class
$places = Yaml::parseFile("data/$user/places.yml");

if( file_exists("data/$user/current_list.yml"))
{
  $data = Yaml::parseFile("data/$user/current_list.yml");
  $currentList = isset($data['items']) ? $data['items'] : [];
}

// Create a simplified section order map
$sectionOrder = ["Unknown:Unknown" => 0]; // Unknown always first
$orderIndex = 1;

// Collect section order from places.yml
foreach( $places as $vendorName => $sections ) {
  if( is_array($sections) ) {
    foreach( array_keys($sections) as $section )
      $sectionOrder["$vendorName:$section"] = $orderIndex++;
  }
}

// Create structured result with all vendors and sections
$structuredResult = [];

// First, prepare the Unknown vendor with Unknown section
$unknownItems = array_filter( $currentList, fn($item) => 
  isset($item['vendor']) && $item['vendor'] === 'Unknown' && 
  isset($item['section']) && $item['section'] === 'Unknown' 
);

if( ! empty($unknownItems)) {
  $structuredResult['vendors']['Unknown'] = [
    'name' => 'Unknown',
    'sections' => [
      'Unknown' => [
        'name' => 'Unknown',
        'items' => array_values($unknownItems),
        'order' => 0
      ]
    ]
  ];
}

// Add the requested vendor with all its sections
if( isset($places[$vendor]) && is_array($places[$vendor]) )
{
  $structuredResult['vendors'][$vendor] = [
    'name' => $vendor,
    'sections' => []
  ];
  
  foreach( $places[$vendor] as $sectionName => $possibleItems ) {
    if( ! is_array($possibleItems))
      continue;
      
    // Get items for this section
    $sectionItems = array_filter( $currentList, fn($item) => 
      isset($item['vendor']) && $item['vendor'] === $vendor && 
      isset($item['section']) && $item['section'] === $sectionName 
    );
    
    // Add section with its items (or empty array)
    $structuredResult['vendors'][$vendor]['sections'][$sectionName] = [
      'name'  => $sectionName,
      'items' => array_values($sectionItems),
      'order' => $sectionOrder["$vendor:$sectionName"] ?? 9999
    ];
  }
}

echo json_encode( $structuredResult );
