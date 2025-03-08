
// made by AI, mighr be replaces with own tools

// API functions

const api = {
  async makeRequest(action, data) {
    const response = await fetch('ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, ...data })
    })
    
    const result = await response.json()
    if (!response.ok) throw new Error(result.error || `${action} failed`)
    return result
  },

  async importList(text) {
    return this.makeRequest('import', { text })
  },

  async toggleItem(id, checked) {
    return this.makeRequest('toggle', { id, checked })
  },

  async addItem(vendor, section, text) {
    return this.makeRequest('add', { vendor, section, text })
  },

  async getVendorItems(vendor) {
    return this.makeRequest('get', { vendor })
  },

  async savePlaces(content) {
    return this.makeRequest('places_save', { content })
  },

  async saveHeaders(content) {
    return this.makeRequest('headers_save', { content })
  }
}


// UI Helper functions

const ui = {
  showStatusMessage(element, message, type, autohide = true) {
    element.textContent = message
    element.className = `alert alert-${type} mb-3`
    element.classList.remove('d-none')
    
    if (autohide && type === 'success') {
      setTimeout(() => element.classList.add('d-none'), 3000)
    }
  },

  toggleContainerVisibility(showContainerId) {
    document.querySelectorAll('.page').forEach(container => {
      container.style.setProperty('display', 
        container.id === showContainerId ? 'flex' : 'none', 
        'important'
      )
    })
  },

  showListContainer() {
    document.getElementById('listContainer').style.setProperty('display', 'block', 'important')
  }
}
