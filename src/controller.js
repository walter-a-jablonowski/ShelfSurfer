// was reimplemented by AI as a whole

// TASK: replace this api. request thing that the ai made by fetch or own api (see helpers)

// helper function to refresh content after saving places or headers
function updateContent()
{
  // Reload the current vendor to reflect changes

  const activeVendorTab = document.querySelector('.nav-link.active')
  if( activeVendorTab && activeVendorTab.dataset.vendor)
    controller.loadVendor(activeVendorTab.dataset.vendor)
}

class MainController
{
  constructor()
  {
    this.initializeElements()
    this.initializeModals()
    this.attachEventListeners()
    this.loadInitialVendor()
  }

  initializeElements()
  {
    // Main elements
    this.content = document.getElementById('content')
    this.importText = document.getElementById('importText')
    this.importButton = document.getElementById('importButton')
    this.importModal = document.getElementById('importModal')

    // Places editor elements
    this.editPlacesContainer = document.getElementById('editPlacesContainer')
    this.placesEditor = document.getElementById('placesTextarea')
    this.savePlacesButton = document.getElementById('savePlacesBtn')
    this.cancelPlacesButton = document.getElementById('cancelEditPlacesBtn')
    this.placesStatusMessage = document.getElementById('edit-status-message')

    // Headers editor elements
    this.editHeadersContainer = document.getElementById('editHeadersContainer')
    this.headersEditor = document.getElementById('headersTextarea')
    this.saveHeadersButton = document.getElementById('saveHeadersBtn')
    this.cancelHeadersButton = document.getElementById('cancelEditHeadersBtn')
    this.headersStatusMessage = document.getElementById('edit-headers-status-message')
  }

  initializeModals()
  {
    this.bsImportModal = new bootstrap.Modal(this.importModal, {
      keyboard: true,
      focus: true
    })
  
    this.addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'))
  }

  attachEventListeners()
  {
    // Print functionality
    document.querySelector('.dropdown-item[href="#"][data-print]')
      .addEventListener('click', e => {
        e.preventDefault()
        window.open('print.php', '_blank')
      })

    // Vendor selection
    document.querySelectorAll('[data-vendor]').forEach( el => {
      el.addEventListener('click', e => {
        e.preventDefault()
        this.loadVendor(el.dataset.vendor)
      })
    })

    // Import functionality
    if( this.importButton )
      this.importButton.addEventListener('click', () => this.handleImport())

    // Item checking
    this.content.addEventListener('change', e => this.handleItemCheck(e))

    // Add item functionality
    document.addEventListener('click', e => this.handleAddItemClick(e))
    document.getElementById('itemText').addEventListener('keyup', e => {
      if( e.key === 'Enter') {
        e.preventDefault()
        this.handleAddItem()
      }
    })

    document.getElementById('addItemButton').addEventListener('click', () => this.handleAddItem())

    // Places editing
    document.querySelector('.dropdown-item[data-edit-places]')
      .addEventListener('click', e => this.handleEditPlaces(e))
    this.savePlacesButton.addEventListener('click', () => this.handleSavePlaces())
    this.cancelPlacesButton.addEventListener('click', () => this.handleCancelPlaces())

    // Headers editing
    document.querySelector('.dropdown-item[data-edit-headers]')
      .addEventListener('click', e => this.handleEditHeaders(e))
    this.saveHeadersButton.addEventListener('click', () => this.handleSaveHeaders())
    this.cancelHeadersButton.addEventListener('click', () => this.handleCancelHeaders())
  }

  loadInitialVendor()
  {
    const firstVendorTab = document.querySelector('[data-vendor]')
    if( firstVendorTab )
      this.loadVendor(firstVendorTab.dataset.vendor)
  }

  async handleImport()
  {
    const text = this.importText.value.trim()
  
    if( ! text) {
      alert('Please enter some text to import')
      return
    }

    try {

      const result = await api.importList(text)

      if( result.success ) {
        // Update the contents of currentList without reassigning the constant
        currentList.length = 0  // clear the array
        result.items.forEach( item => currentList.push(item))  // add new items
        this.importText.value = ''
        this.bsImportModal.hide()

        const firstVendorWithItems = [...new Set( result.items.map(item => item.vendor))][0]
        if( firstVendorWithItems )
          this.loadVendor(firstVendorWithItems)
      }
    }
    catch(err) {
      console.error('Import failed:', err)
      alert('Import failed: ' + err.message)
    }
  }

  async handleItemCheck(e)
  {
    if( ! e.target.matches('.form-check-input'))  return

    const itemId   = e.target.dataset.id
    const checked  = e.target.checked
    const listItem = e.target.closest('.list-group-item')

    listItem.classList.toggle('checked', checked)

    try {
      await api.toggleItem(itemId, checked)
    }
    catch(err) {
      console.error('Toggle failed:', err)
      e.target.checked = !checked
      listItem.classList.toggle('checked', ! checked)
    }
  }

  handleAddItemClick(e)
  {
    const btn = e.target.closest('.add-item-btn')
    if( ! btn)  return

    const vendor = btn.dataset.vendor
    const section = btn.dataset.section

    document.getElementById('itemVendor').value = vendor
    document.getElementById('itemSection').value = section
    document.getElementById('itemText').value = ''
    this.addItemModal.show()
  }

  async handleAddItem()
  {
    const vendor = document.getElementById('itemVendor').value
    const section = document.getElementById('itemSection').value
    const text = document.getElementById('itemText').value.trim()

    if( ! text )  return

    try {
      const result = await api.addItem(vendor, section, text)
      if( result.success ) {
        currentList.push(result.item)
        this.addItemModal.hide()
        this.renderVendor(vendor)
      }
    }
    catch(err) {
      console.error('Failed to add item:', err)
      alert('Failed to add item. Please try again')
    }
  }

  async loadVendor(vendor)
  {
    try {
 
      const vendorItems = await api.getVendorItems(vendor)
      
      // Update the contents of currentList without reassigning the constant
      currentList.length = 0  // clear the array
      vendorItems.forEach( item => currentList.push(item))  // add new items

      document.querySelectorAll('.nav-link').forEach( el => {
        el.classList.toggle('active', el.dataset.vendor === vendor)
      })

      this.renderVendor(vendor)
    }
    catch(err) {
      console.error('Failed to load vendor:', err)
      this.content.innerHTML = `<div class="alert alert-danger">Failed to load items</div>`
    }
  }

  renderVendor(vendor)
  {
    // Get all items for this vendor
    const items = currentList.filter(item => item.vendor === vendor)
    const sections = {}

    // Get any unknown items (from any vendor)
    const unknownItems = currentList.filter( item => item.vendor === 'Unknown' && item.section === 'Unknown')
    
    // Add unknown items to sections if there are any
    if (unknownItems.length > 0)
      sections['Unknown'] = unknownItems
    
    // Add regular items to their sections
    items.forEach( item => {
      if( ! sections[item.section]) sections[item.section] = []
      sections[item.section].push(item)
    })

    // Create the HTML for all sections
    const sectionsHTML = Object.entries(sections)
      .map(([section, sectionItems], index) => {
        // Special styling for Unknown section
        if( section === 'Unknown') {
          return this.renderSection(
            section, 
            sectionItems, 
            'rgba(200, 200, 200, 0.1)',  // light grey color with transparency
            'rgba(200, 200, 200, 0.3)', 
            null,
            vendor
          )
        }
        
        const color = SECTION_COLORS[index % SECTION_COLORS.length]
        const borderColor = color.replace('0.1)', '0.3)')
        const sectionHeaderText = this.getSectionHeaderText(vendor, section)

        return this.renderSection(section, sectionItems, color, borderColor, sectionHeaderText, vendor)
      })
    
    // Sort sections to ensure Unknown is at the top
    const sortedSectionsHTML = sectionsHTML.sort((a, b) => {
      if( a.includes('Unknown'))  return -1
      if( b.includes('Unknown'))  return 1
      return 0
    })
    
    this.content.innerHTML = sortedSectionsHTML.join('')
  }

  getSectionHeaderText(vendor, section)
  {
    return (typeof headers !== 'undefined' &&
            headers?.sectionHeaders?.[vendor]?.[section]) || ''
  }

  renderSection(section, items, color, borderColor, headerText, vendor)
  {
    return `
      <div class="card section-card mb-3" style="background-color: ${color}; border-color: ${borderColor}">
        <div class="card-header d-flex justify-content-between align-items-center" style="border-bottom-color: ${borderColor}">
          <div class="d-flex align-items-center">
            <b>${section}</b>
            ${headerText ? `<small class="ms-2 text-muted">${headerText}</small>` : ''}
          </div>
          <button class="btn btn-sm add-item-btn" data-vendor="${vendor}" data-section="${section}">
            <i class="bi bi-plus-lg"></i>
          </button>
        </div>
        <div class="card-body p-0 pt-2">
          <ul class="list-group list-group-flush">
            ${items.map(item => this.renderItem(item)).join('')}
          </ul>
        </div>
      </div>
    `
  }

  renderItem(item)
  {
    return `
      <li class="list-group-item ${item.checked ? 'checked' : ''}">
        <label class="form-check-label">${item.text}</label>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" ${item.checked ? 'checked' : ''} data-id="${item.id}">
        </div>
      </li>
    `
  }

  handleEditPlaces(e)
  {
    e.preventDefault()
    ui.toggleContainerVisibility('editPlacesContainer')
    this.placesEditor.focus()
  }

  async handleSavePlaces()
  {
    try {
  
      const result = await api.savePlaces(this.placesEditor.value)
  
      if( result.success ) {
        ui.showStatusMessage(this.placesStatusMessage, 'Places file saved successfully!', 'success')
        setTimeout(() => {
          ui.toggleContainerVisibility('listContainer')
          ui.showListContainer()
          updateContent()
        }, 1000)
      }
      else
        ui.showStatusMessage( this.placesStatusMessage, result.message || 'Error saving places content', 'danger')
    }
    catch(error) {
      ui.showStatusMessage( this.placesStatusMessage, 'Error: ' + error.message, 'danger')
    }
  }

  handleCancelPlaces()
  {
    ui.toggleContainerVisibility('listContainer')
    ui.showListContainer()
  }

  handleEditHeaders(e)
  {
    e.preventDefault()
    ui.toggleContainerVisibility('editHeadersContainer')
    this.headersEditor.focus()
  }

  async handleSaveHeaders()
  {
    try {

      const result = await api.saveHeaders(this.headersEditor.value)
    
      if( result.success ) {
        ui.showStatusMessage(this.headersStatusMessage, 'Headers file saved successfully!', 'success')
        setTimeout(() => {
          ui.toggleContainerVisibility('listContainer')
          ui.showListContainer()
          updateContent()
        }, 1000)
      }
      else
        ui.showStatusMessage(this.headersStatusMessage, result.message || 'Error saving headers content', 'danger')
    }
    catch(error) {
      ui.showStatusMessage(this.headersStatusMessage, 'Error: ' + error.message, 'danger')
    }
  }

  handleCancelHeaders()
  {
    ui.toggleContainerVisibility('listContainer')
    ui.showListContainer()
  }
}
