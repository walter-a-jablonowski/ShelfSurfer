<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shelf Surfer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
  <link href="style.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>

  <nav class="navbar navbar-expand fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <i class="bi bi-cart3"></i>
        Shelf Surfer
      </a>
      <div class="navbar-nav ms-auto">
        <a class="nav-link" href="#" title="Settings">
          <i class="bi bi-gear"></i>
        </a>
      </div>
    </div>
  </nav>

  <div class="container mt-3">
    <div id="content"></div>
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
                <a class="dropdown-item nav-link px-3" href="#" data-print>
                  <i class="bi bi-printer me-2"></i>
                  Print
                </a>
              </li>
              <?php foreach( $miscVendors as $vendor ): ?>
                <li>
                  <a class="dropdown-item nav-link px-3" href="#" data-vendor="<?= $vendor ?>">
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="addItemButton">
            Add
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="controller.js?v=<?= time() ?>"></script>
</body>
</html>
