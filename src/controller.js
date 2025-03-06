document.addEventListener('DOMContentLoaded', () => {

  console.log('Initializing controller...')
  
  const content      = document.getElementById('content')
  const importText   = document.getElementById('importText')
  const importButton = document.getElementById('importButton')
  const importModal  = document.getElementById('importModal')
  
  console.log('Elements found:', {
    content: !!content,
    importText: !!importText,
    importButton: !!importButton,
    importModal: !!importModal
  })

  // Initialize modal
  const bsImportModal = new bootstrap.Modal(importModal, {
    keyboard: true,
    focus: true
  })
  
  // Background colors for sections
  const sectionColors = [
    '#f8f9fa', '#e9ecef', '#dee2e6', '#ced4da',
    '#adb5bd', '#6c757d', '#495057', '#343a40'
  ]
  
  let currentVendor = null
  let currentList = []

  // Load initial vendor
  const firstVendorTab = document.querySelector('[data-vendor]')
  if(firstVendorTab) {
    loadVendor(firstVendorTab.dataset.vendor)
  }

  // Handle vendor selection
  document.querySelectorAll('[data-vendor]').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault()
      loadVendor(el.dataset.vendor)
    })
  })

  // Handle import button click
  if(importButton) {
    console.log('Adding import button click handler')
    importButton.addEventListener('click', handleImport)
  }

  async function handleImport() {
    console.log('Import button clicked')
    const text = importText.value.trim()
    if(!text) {
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
      
      if(!response.ok) {
        throw new Error(result.error || 'Import failed')
      }
      
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
    } catch(err) {
      console.error('Import failed:', err)
      alert('Import failed: ' + err.message)
    }
  }

  // Handle item checking
  content.addEventListener('change', async e => {
    if(!e.target.matches('.form-check-input')) return
    
    const itemId = e.target.dataset.id
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

      if(!response.ok) throw new Error('Toggle failed')
    } catch(err) {
      console.error('Toggle failed:', err)
      // Revert UI state on error
      e.target.checked = !checked
      e.target.closest('.list-group-item').classList.toggle('checked', !checked)
    }
  })

  async function loadVendor(vendor) {
    try {
      const response = await fetch(`ajax.php?action=list&vendor=${encodeURIComponent(vendor)}`)
      if( ! response.ok) throw new Error('Failed to load vendor')
      
      const result = await response.json()
      currentList = result.items
      currentVendor = vendor
      
      // Update active state
      document.querySelectorAll('.tab-item').forEach(el => {
        el.classList.toggle('active', el.dataset.vendor === vendor)
      })
      document.querySelectorAll('.dropdown-item').forEach(el => {
        el.classList.toggle('active', el.dataset.vendor === vendor)
      })
      
      renderVendor(vendor)
    } catch(err) {
      console.error('Failed to load vendor:', err)
      alert('Failed to load vendor. Please try again.')
    }
  }

  function renderVendor(vendor) {
    const items = currentList.filter(item => item.vendor === vendor)
    const sections = {}
    
    // Group items by section
    items.forEach(item => {
      if(!sections[item.section]) {
        sections[item.section] = []
      }
      sections[item.section].push(item)
    })

    // Render sections
    content.innerHTML = Object.entries(sections)
      .map(([section, items], i) => `
        <div class="card section-card" style="background-color: ${sectionColors[i % sectionColors.length]}">
          <div class="card-header">
            ${section}
          </div>
          <ul class="list-group list-group-flush">
            ${items.map(item => `
              <li class="list-group-item${item.checked ? ' checked' : ''}" style="background-color: ${sectionColors[i % sectionColors.length]}">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" data-id="${item.id}"${item.checked ? ' checked' : ''}>
                  <label class="form-check-label">${item.text}</label>
                </div>
              </li>
            `).join('')}
          </ul>
        </div>
      `)
      .join('')
  }
})
