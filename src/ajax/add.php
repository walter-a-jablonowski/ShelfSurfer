<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
require_once 'lib/Session.php';


// Add an manually added foor

$input = json_decode( file_get_contents('php://input'), true );

if( ! isset($input['vendor']) || ! isset($input['section']) || ! isset($input['text']) )
{
  http_response_code(400);
  echo json_encode( ['error' => 'Missing required fields'] );
  exit;
}

$vendor  = $input['vendor'];
$section = $input['section'];
$text    = trim($input['text']);

if( ! $text )
{
  http_response_code(400);
  echo json_encode( ['error' => "Text cann't be empty"] );
  exit;
}

// Load current list

$user = Session::getUser();  // dummy Session class
$places = Yaml::parseFile("data/$user/places.yml");

$data = [
  'items' => []
];

if( file_exists("data/$user/current_list.yml") )
{
  $data = Yaml::parseFile("data/$user/current_list.yml");
  if( ! isset($data['items']) )
    $data['items'] = [];
}

// Create a simplified section order map
$sectionOrder = ["Unknown:Unknown" => 0]; // Unknown always first
$orderIndex = 1;

// Collect section order from places.yml
foreach( $places as $vendorName => $sections ) {
  if( is_array($sections) ) {
    foreach( array_keys($sections) as $sectionName )
      $sectionOrder["$vendorName:$sectionName"] = $orderIndex++;
  }
}

// Add new item
$newItem = [
  'id'      => uniqid(),
  'text'    => $text,
  'vendor'  => $vendor,
  'section' => $section,
  'order'   => $sectionOrder["$vendor:$section"] ?? 9999,
  'checked' => false
];

$data['items'][] = $newItem;

file_put_contents("data/$user/current_list.yml", Yaml::dump($data, 4, 2) );

// Create structured result with all vendors and sections
$structuredResult = [];

// First, prepare the Unknown vendor with Unknown section if needed
$unknownItems = array_filter($data['items'], function($item) {
  return $item['vendor'] === 'Unknown' && $item['section'] === 'Unknown';
});

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
if( isset($places[$vendor]) && is_array($places[$vendor]) ) {
  $structuredResult['vendors'][$vendor] = [
    'name' => $vendor,
    'sections' => []
  ];
  
  foreach( $places[$vendor] as $sectionName => $possibleItems ) {
    if( ! is_array($possibleItems))
      continue;
      
    // Get items for this section
    $sectionItems = array_filter( $data['items'], function($item) use ($vendor, $sectionName) {
      return $item['vendor'] === $vendor && $item['section'] === $sectionName;
    });
    
    // Add section with its items (or empty array)
    $structuredResult['vendors'][$vendor]['sections'][$sectionName] = [
      'name' => $sectionName,
      'items' => array_values($sectionItems),
      'order' => $sectionOrder["$vendor:$sectionName"] ?? 9999
    ];
  }
}

// Return success with the structured data for immediate display
echo json_encode([
  'success' => true,
  'structured' => $structuredResult
]);
