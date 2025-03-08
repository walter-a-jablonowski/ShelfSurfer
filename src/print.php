<?php

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';

$yamlFile    = 'data/default_user/current_list.yml';
$currentList = [];

if( file_exists($yamlFile) )
{
  $data = Yaml::parseFile($yamlFile);
  $currentList = isset($data['items']) ? $data['items'] : [];
}

// Group items by vendor and section
$vendorSections = [];

foreach( $currentList as $item )
{
  // Skip items without vendor or section
  if( ! isset($item['vendor']) || ! isset($item['section']) )
    continue;
    
  $vendor = $item['vendor'];
  $section = $item['section'];
  
  if( ! isset($vendorSections[$vendor]) )
    $vendorSections[$vendor] = [];
    
  if( ! isset($vendorSections[$vendor][$section]) )
    $vendorSections[$vendor][$section] = [];
    
  $vendorSections[$vendor][$section][] = $item;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shelf Surfer - Print View</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles/print.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>
  <div class="container-fluid mt-3 mb-4 px-4">
    <button onclick="window.print()" class="btn btn-primary no-print mb-3">Print</button>
    
    <div class="print-container">
      <?php if( empty($vendorSections) ): ?>
        <div class="alert alert-warning no-print">No items found in your shopping list.</div>
      <?php else: ?>
        <?php foreach( $vendorSections as $vendor => $sections ): ?>
          <div class="print-vendor">
            <h2><?= htmlspecialchars($vendor) ?></h2>
            
            <?php foreach( $sections as $section => $items ): ?>
              <div class="print-section">
                <h3><?= htmlspecialchars($section) ?></h3>
                <ul class="print-items">
                  <?php foreach( $items as $item ): ?>
                    <li class="print-item <?= isset($item['checked']) && $item['checked'] ? 'checked' : '' ?>">
                      <?= htmlspecialchars($item['text']) ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
