document.addEventListener('DOMContentLoaded', () => {

  console.log('Initializing controller...')
  
  const content      = document.getElementById('content')
  const importText   = document.getElementById('importText')
  const importButton = document.getElementById('importButton')
  const importModal  = document.getElementById('importModal')
  
  console.log('Elements found:', {
    content:      !!content,
    importText:   !!importText,
    importButton: !!importButton,
    importModal:  !!importModal
  })

  // Get edit places elements
  const editContainer = document.getElementById('editPlacesContainer')
  const placesEditor  = document.getElementById('placesTextarea')
  const saveButton    = document.getElementById('savePlacesBtn')
  const cancelButton  = document.getElementById('cancelEditPlacesBtn')
  const statusMessage = document.getElementById('edit-status-message')
  
  console.log('Elements found:', {
    content:      !!content,
    importText:   !!importText,
    importButton: !!importButton,
    importModal:  !!importModal,
    editContainer: !!editContainer,
    placesEditor:  !!placesEditor,
    saveButton:    !!saveButton,
    cancelButton:  !!cancelButton
  })

  // Initialize modal
  const bsImportModal = new bootstrap.Modal(importModal, {
    keyboard: true,
    focus: true
  })

  // Add item modal
  const addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'))

  // Handle print functionality
  document.querySelector('.dropdown-item[href="#"][data-print]').addEventListener('click', e => {
    e.preventDefault()
    window.open('print.php', '_blank')
  })

  // Background colors for sections (light, translucent colors)
  const sectionColors = [
    'rgba(233, 84, 32, 0.1)',   // Ubuntu orange
    'rgba(41, 128, 185, 0.1)',  // Soft blue
    'rgba(39, 174, 96, 0.1)',   // Soft green
    'rgba(142, 68, 173, 0.1)',  // Soft purple
    'rgba(211, 84, 0, 0.1)',    // Soft orange
    'rgba(22, 160, 133, 0.1)',  // Soft teal
    'rgba(192, 57, 43, 0.1)',   // Soft red
    'rgba(44, 62, 80, 0.1)'     // Soft navy
  ]
  
  let currentVendor = null
  let currentList   = []

  // Load initial vendor
  const firstVendorTab = document.querySelector('[data-vendor]')
  console.log('First vendor tab:', firstVendorTab?.dataset?.vendor || 'none found')

  if( firstVendorTab )
    loadVendor(firstVendorTab.dataset.vendor)

  // Handle vendor selection
  document.querySelectorAll('[data-vendor]').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault()
      loadVendor(el.dataset.vendor)
    })
  })

  // Handle import button click
  if( importButton ) {
    console.log('Adding import button click handler')
    importButton.addEventListener('click', async () => {
      await handleImport()
    })
  }

  async function handleImport()
  {
    console.log('Import button clicked')
    const text = importText.value.trim()
    
    if( ! text) {
      alert('Please enter some text to import')
      return
    }

    try {

      console.log('Sending import request...')
      const response = await fetch('ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'import',
          text
        })
      })

      const result = await response.json()
      console.log('Import response:', result)
      
      if( ! response.ok)
        throw new Error(result.error || 'Import failed')
      
      if(result.success) {
        currentList = result.items
        importText.value = ''
        bsImportModal.hide()
        
        // Get first vendor that has items
        const firstVendorWithItems = [...new Set(result.items.map(item => item.vendor))][0]
        if(firstVendorWithItems) {
          loadVendor(firstVendorWithItems)
        }
      }
    }
    catch(err) {
      console.error('Import failed:', err)
      alert('Import failed: ' + err.message)
    }
  }

  // Handle item checking
  content.addEventListener('change', async e => {

    if( ! e.target.matches('.form-check-input'))  return
    
    const itemId  = e.target.dataset.id
    const checked = e.target.checked
    
    e.target.closest('.list-group-item').classList.toggle('checked', checked)

    try {

      const response = await fetch('ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'toggle',
          id: itemId,
          checked
        })
      })

      if( ! response.ok) throw new Error('Toggle failed')
    }
    catch(err) {
      console.error('Toggle failed:', err)
      // Revert UI state on error
      e.target.checked = !checked
      e.target.closest('.list-group-item').classList.toggle('checked', !checked)
    }
  })

  // Add item button click handler using event delegation
  document.addEventListener('click', e => {
    const btn = e.target.closest('.add-item-btn')
    if( ! btn ) return
    
    const vendor  = btn.dataset.vendor
    const section = btn.dataset.section
    
    document.getElementById('itemVendor').value  = vendor
    document.getElementById('itemSection').value = section
    document.getElementById('itemText').value    = ''
    addItemModal.show()
  })

  // Add item handlers
  async function handleAddItem()
  {
    try {
      const vendor  = document.getElementById('itemVendor').value
      const section = document.getElementById('itemSection').value
      const text    = document.getElementById('itemText').value.trim()
      
      if( ! text ) return
      
      const response = await fetch('ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'add',
          vendor,
          section,
          text
        })
      })
      
      const result = await response.json()
      
      if( ! response.ok ) 
        throw new Error(result.error || 'Failed to add item')
      
      if( result.success ) {
        currentList.push(result.item)
        addItemModal.hide()
        renderVendor(vendor)
      }
    }
    catch( err ) {
      console.error('Failed to add item:', err)
      alert('Failed to add item. Please try again.')
    }
  }

  // Handle Enter key in input
  document.getElementById('itemText').addEventListener('keyup', e => {
    if( e.key === 'Enter' ) {
      e.preventDefault()
      handleAddItem()
    }
  })

  // Add item form submit
  document.getElementById('addItemButton').addEventListener('click', handleAddItem)

  async function loadVendor( vendor )
  {
    try 
    {
      console.log('Loading vendor:', vendor)
      const response = await fetch('ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'get',
          vendor
        })
      })
      
      if( ! response.ok ) throw new Error('Failed to load vendor')
      
      currentList = await response.json()
      console.log('Loaded items:', currentList)
      
      // Update active state
      document.querySelectorAll('.nav-link').forEach( el => {
        el.classList.toggle('active', el.dataset.vendor === vendor)
      })
      
      renderVendor(vendor)
    }
    catch( err ) 
    {
      console.error('Failed to load vendor:', err)
      content.innerHTML = `<div class="alert alert-danger">Failed to load items</div>`
    }
  }

  function renderVendor( vendor )
  {
    console.log('Rendering vendor:', vendor)
    const items = currentList.filter( item => item.vendor === vendor )
    console.log('Filtered items:', items)
    
    const sections = {}
    
    // Group items by section
    items.forEach( item => {
      if( ! sections[item.section] ) sections[item.section] = []
      sections[item.section].push(item)
    })

    // Render sections
    content.innerHTML = Object.entries(sections)
      .map( ([section, items], index) => {
        const color = sectionColors[index % sectionColors.length]
        const borderColor = color.replace('0.1)', '0.3)')
        
        // Check if section header exists for this vendor and section
        let sectionHeaderText = ''
        if( typeof headers !== 'undefined' && 
            headers && 
            headers.sectionHeaders && 
            headers.sectionHeaders[vendor] && 
            headers.sectionHeaders[vendor][section] ) {
          sectionHeaderText = headers.sectionHeaders[vendor][section]
        }
        
        return `
          <div class="card section-card mb-3" style="background-color: ${color}; border-color: ${borderColor}">
            <div class="card-header d-flex justify-content-between align-items-center" style="border-bottom-color: ${borderColor}">
              <div class="d-flex align-items-center">
                <b>${section}</b>
                ${sectionHeaderText ? `<small class="ms-2 text-muted">${sectionHeaderText}</small>` : ''}
              </div>
              <button class="btn btn-sm add-item-btn" data-vendor="${vendor}" data-section="${section}">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
            <div class="card-body p-0 pt-2">
              <ul class="list-group list-group-flush">
                ${items.map( item => `
                  <li class="list-group-item ${item.checked ? 'checked' : ''}">
                    <label class="form-check-label">${item.text}</label>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" ${item.checked ? 'checked' : ''} data-id="${item.id}">
                    </div>
                  </li>
                `).join('')}
              </ul>
            </div>
          </div>
        `
      }).join('')
  }

  // Handle edit places functionality
  document.querySelector('.dropdown-item[data-edit-places]').addEventListener('click', e => {
    e.preventDefault()
    
    // Hide all containers except edit container
    const mainContainers = document.querySelectorAll('.page')
    mainContainers.forEach(container => {
      if( container.id !== 'editPlacesContainer' )
        container.style.setProperty('display', 'none', 'important')
    })
    
    // Show edit container (display: flex is already set in HTML)
    editContainer.style.setProperty('display', 'flex', 'important')
    
    // Focus the editor for immediate typing
    placesEditor.focus()
  })

  // Save places content
  saveButton.addEventListener('click', () => {
    const yamlContent = placesEditor.value
    
    fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ 
        action: 'places_save',
        content: yamlContent 
      })
    })
      .then(response => response.json())
      .then(data => {

        if( data.success )
        {
          showStatusMessage('Places file saved successfully!', 'success')
          // Reload page after 1 second to reflect changes
          setTimeout(() => {
            window.location.reload()
          }, 1000)
        }
        else
          showStatusMessage( data.message || 'Error saving places content', 'danger')
      })
      .catch(error => {
        showStatusMessage('Error: ' + error.message, 'danger')
      })
  })
  
  // Cancel editing
  cancelButton.addEventListener('click', () => {

    // Hide edit container
    editContainer.style.setProperty('display', 'none', 'important')
    
    // Show the list container using its ID
    document.getElementById('listContainer').style.setProperty('display', 'block', 'important')
  })
  
  // Helper function to show status messages
  function showStatusMessage( message, type )
  {
    statusMessage.textContent = message
    statusMessage.className = `alert alert-${type} mb-3`
    
    // Remove d-none class if present
    statusMessage.classList.remove('d-none')
    
    // Auto-hide success messages after 3 seconds
    if( type === 'success' )
    {
      setTimeout(() => {
        statusMessage.classList.add('d-none')
      }, 3000)
    }
  }
})
