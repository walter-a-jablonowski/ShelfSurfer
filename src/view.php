<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shelf Surfer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
  <link href="styles/app.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>

  <nav class="navbar navbar-expand fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <i class="bi bi-cart3"></i>
        Shelf Surfer
      </a>
      <div class="navbar-nav ms-auto">

        <!-- TASK: List info (in bu version, unimplemented) -->
<!--
        <a class="nav-link" href="#" title="Info">
          <i class="bi bi-info-circle"></i>
        </a>
-->
        <a class="nav-link" href="#" title="Settings">
          <i class="bi bi-gear"></i>
        </a>
      </div>
    </div>
  </nav>

  <!-- List -->

  <div id="listContainer" class="container page mt-3">

    <!-- List info (in list version) -->
    <?php if( isset($headers['listHeader']) && ! empty($headers['listHeader'])): ?>
      <div class="card mb-3">
        <div id="listHeaderCard" class="card-header ps-3 pe-4 d-flex justify-content-between align-items-center">
          <span class="d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i> Info
          </span>
          <button class="btn btn-sm p-0" type="button" data-bs-toggle="collapse" data-bs-target="#listHeaderContent" aria-expanded="true" aria-controls="listHeaderContent">
            <i class="bi bi-chevron-down" style="color: #bbb !important;"></i>
          </button>
        </div>
        <div id="listHeaderContent" class="collapse" aria-labelledby="listHeaderCard">
          <div class="card-body">
            <pre class="mb-0"><?= htmlspecialchars($headers['listHeader']) ?></pre>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Main content area - grocery list -->
    <div id="content"></div>
  </div>
    
  <!-- Edit places -->
  
  <div id="editPlacesContainer" class="container page mt-3 d-flex flex-column" style="display: none !important; height: calc(100vh - 130px);">
    <div id="edit-status-message" class="alert d-none"></div>
    
    <textarea id="placesTextarea" class="form-control border-0 flex-grow-1" style="font-family: monospace; resize: none; height: calc(100vh - 200px);"><?= htmlspecialchars($placesTxt) ?></textarea>
    
    <div class="d-flex justify-content-end mt-2 mb-1">
      <button id="cancelEditPlacesBtn" class="btn btn-secondary me-2">Back</button>
      <button id="savePlacesBtn" class="btn btn-primary" style="background-color: #e95420 !important; border-color: #e95420 !important;">Save</button>
    </div>
  </div>
  
  <!-- Edit headers -->
  
  <div id="editHeadersContainer" class="container page mt-3 d-flex flex-column" style="display: none !important; height: calc(100vh - 130px);">
    <div id="edit-headers-status-message" class="alert d-none"></div>
    
    <textarea id="headersTextarea" class="form-control border-0 flex-grow-1" style="font-family: monospace; resize: none; height: calc(100vh - 200px);"><?= htmlspecialchars($headersTxt) ?></textarea>
    
    <div class="d-flex justify-content-end mt-2 mb-1">
      <button id="cancelEditHeadersBtn" class="btn btn-secondary me-2">Back</button>
      <button id="saveHeadersBtn" class="btn btn-primary" style="background-color: #e95420 !important; border-color: #e95420 !important;">Save</button>
    </div>
  </div>

  <div class="tab-bar">
    <div class="container-fluid">
      <div class="row h-100">
        <?php

        $vendors = array_keys( $places );
        $mainVendors = array_slice( $vendors, 0, 2 );
        $miscVendors = array_slice( $vendors, 2 );
        
        foreach( $mainVendors as $i => $vendor ): ?>
          <div class="col text-center">
            <a href="#" class="nav-link tab-item d-flex flex-column align-items-center justify-content-center pt-1" data-vendor="<?= $vendor ?>">
              <i class="bi bi-shop"></i>
              <?= $vendor ?>
            </a>
          </div>
        <?php endforeach; ?>

        <div class="col text-center">
          <div class="dropup">
            <a href="#" class="tab-item d-flex flex-column align-items-center justify-content-center pt-1" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots"></i>
              More
            </a>
            <ul class="dropdown-menu dropup-vendors py-2">
              <li>
                <a class="dropdown-item nav-link py-2 px-3" href="#" data-edit-places>
                  <i class="bi bi-pencil-square me-2"></i>
                  Edit places
                </a>
              </li>
              <li>
                <a class="dropdown-item nav-link py-2 px-3" href="#" data-edit-headers>
                  <i class="bi bi-pencil-square me-2"></i>
                  Edit headers
                </a>
              </li>
              <li>
                <a class="dropdown-item nav-link py-2 px-3" href="#" data-print>
                  <i class="bi bi-printer me-2"></i>
                  Print
                </a>
              </li>
              <?php foreach( $miscVendors as $vendor ): ?>
                <li>
                  <a class="dropdown-item nav-link py-2 px-3" href="#" data-vendor="<?= $vendor ?>">
                    <i class="bi bi-shop me-2"></i>
                    <?= $vendor ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <div class="col text-center">
          <button type="button" class="tab-item btn btn-link p-0 border-0 d-flex flex-column align-items-center justify-content-center pt-1" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-cloud-upload"></i>
            Import
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="importModalLabel">Import Shopping List</h5>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="importText" class="form-label">Paste alexa shopping list here</label>
            <textarea class="form-control" id="importText" rows="10"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="importButton">Import</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Item Modal -->
  <div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Item</h5>
        </div>
        <div class="modal-body">
          <input id="itemVendor"  type="hidden">
          <input id="itemSection" type="hidden">
          <input id="itemText"    type="text" class="form-control" autofocus>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
          <button type="button" class="btn btn-primary" id="addItemButton">
            Add
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Pass PHP variables to JavaScript
    const currentList = <?= json_encode(isset($currentList['items']) ? $currentList['items'] : []) ?>;
    const places = <?= json_encode($places) ?>;
    const headers = <?= json_encode($headers) ?>;
  </script>
  <script src="controller.js?v=<?= time() ?>"></script>
</body>
</html>
