let currentPage = 1;
const itemsPerPage = 6;
let filteredServices = [];

let servicesGrid;
let serviceCards = [];
let servicesEmptyState;
let helpSearch;
let categoryFilter;
let typeFilter;
let pagination;
let prevBtn;
let nextBtn;
let paginationNumbers;

document.addEventListener('DOMContentLoaded', function () {
  initializeElements();
  setupEventListeners();
  applyFilters();
  updatePagination();
});

function initializeElements() {
  servicesGrid = document.getElementById('servicesGrid');
  helpSearch = document.getElementById('helpSearch');
  categoryFilter = document.getElementById('categoryFilter');
  typeFilter = document.getElementById('typeFilter');
  pagination = document.getElementById('helpPagination');
  prevBtn = document.getElementById('prevBtn');
  nextBtn = document.getElementById('nextBtn');
  paginationNumbers = document.getElementById('paginationNumbers');
  servicesEmptyState = document.getElementById('servicesEmptyState');
  serviceCards = Array.from(document.querySelectorAll('.service-card'));
  filteredServices = [...serviceCards];
}

function setupEventListeners() {
  if (helpSearch) {
    helpSearch.addEventListener('input', debounce(handleSearch, 300));
  }

  if (categoryFilter) {
    categoryFilter.addEventListener('change', handleFilter);
  }

  if (typeFilter) {
    typeFilter.addEventListener('change', handleFilter);
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => changePage(currentPage - 1));
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => changePage(currentPage + 1));
  }

  setupEmergencyActions();
  setupQuickActions();
  setupServiceCardListeners();

  window.showCenterDetails = function (centerId) {
    showCenterDetails(centerId);
  };

  window.closeCenterModal = function () {
    const modal = document.getElementById('centerDetailsModal');
    if (modal) modal.style.display = 'none';
  };

  window.addEventListener('click', function (event) {
    const modal = document.getElementById('centerDetailsModal');
    if (modal && event.target === modal) {
      modal.style.display = 'none';
    }
  });
}

function handleSearch() {
  currentPage = 1;
  applyFilters();
  updatePagination();
}

function handleFilter() {
  currentPage = 1;
  applyFilters();
  updatePagination();
}

function applyFilters() {
  const searchTerm = (helpSearch?.value || '').toLowerCase().trim();
  const categoryValue = categoryFilter ? categoryFilter.value : 'all';
  const typeValue = typeFilter ? typeFilter.value : 'all';

  filteredServices = serviceCards.filter((card) => {
    const cardCategory = String(card.dataset.category || '').toLowerCase();
    const cardType = String(card.dataset.type || '').toLowerCase();
    const searchText = String(card.dataset.searchText || '').toLowerCase();

    const categoryMatch = categoryValue === 'all' || cardCategory === categoryValue;
    const typeMatch = typeValue === 'all' || cardType === typeValue;
    const searchMatch = searchTerm === '' || searchText.includes(searchTerm);

    return categoryMatch && typeMatch && searchMatch;
  });

  renderServices();
}

function renderServices() {
  if (!servicesGrid) return;

  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const visibleIds = new Set(filteredServices.slice(startIndex, endIndex).map((card) => card.dataset.serviceId));

  serviceCards.forEach((card) => {
    const shouldShow = visibleIds.has(card.dataset.serviceId);
    card.style.display = shouldShow ? '' : 'none';
  });

  if (servicesEmptyState) {
    servicesEmptyState.style.display = filteredServices.length === 0 ? '' : 'none';
  }

  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
}

function setupServiceCardListeners() {
  document.querySelectorAll('.service-contact-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      handleServiceContact(this);
    });
  });

  document.querySelectorAll('.service-details-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const serviceId = parseInt(this.dataset.serviceId, 10);
      showCenterDetails(serviceId);
    });
  });
}

function handleServiceContact(button) {
  const phoneNumber = button.dataset.phone || '';
  const website = button.dataset.website || '';
  const email = button.dataset.email || '';
  const title = button.dataset.title || 'this service';

  if (phoneNumber) {
    window.location.href = `tel:${phoneNumber}`;
    return;
  }
  if (website) {
    window.open(website, '_blank');
    return;
  }
  if (email) {
    window.location.href = `mailto:${email}`;
    return;
  }

  alert(`Contacting ${title}...`);
}

function showCenterDetails(serviceId) {
  const modal = document.getElementById('centerDetailsModal');
  const modalName = document.getElementById('modalCenterName');
  const modalContent = document.getElementById('modalCenterContent');
  const template = document.getElementById(`serviceDetails-${serviceId}`);
  const card = document.querySelector(`.service-card[data-service-id="${serviceId}"]`);

  if (!modal || !modalName || !modalContent || !template || !card) return;

  modalName.textContent = card.dataset.serviceTitle || 'Center Details';
  modalContent.replaceChildren(...Array.from(template.childNodes).map((node) => node.cloneNode(true)));
  modal.style.display = 'block';
}

function updatePagination() {
  const totalPages = Math.ceil(filteredServices.length / itemsPerPage);

  if (totalPages <= 1) {
    if (pagination) pagination.style.display = 'none';
    return;
  }

  if (pagination) pagination.style.display = 'flex';
  if (prevBtn) prevBtn.disabled = currentPage === 1;
  if (nextBtn) nextBtn.disabled = currentPage === totalPages;

  if (paginationNumbers) {
    paginationNumbers.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
      pageBtn.textContent = i;
      pageBtn.addEventListener('click', () => changePage(i));
      paginationNumbers.appendChild(pageBtn);
    }
  }
}

function changePage(page) {
  const totalPages = Math.ceil(filteredServices.length / itemsPerPage);
  if (page < 1 || page > totalPages) return;

  currentPage = page;
  renderServices();
  updatePagination();

  if (servicesGrid) {
    servicesGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

function setupEmergencyActions() {
  document.querySelectorAll('.emergency-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const card = this.closest('.emergency-card');
      const title = card?.querySelector('h4')?.textContent || '';

      if (title.includes('Crisis Hotline')) {
        window.location.href = 'tel:1-800-273-8255';
      } else if (title.includes('Text Crisis')) {
        alert('Text "HOME" to 741741');
      } else {
        alert('Connecting to emergency chat...');
      }
    });
  });
}

function setupQuickActions() {
  document.querySelectorAll('.action-card .btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const actionCard = this.closest('.action-card');
      const actionTitle = actionCard?.querySelector('h4')?.textContent || '';

      switch (actionTitle) {
        case 'Schedule Session':
          window.location.href = '/user/sessions';
          break;
        case 'Join Support Group':
          window.location.href = '/user/community';
          break;
        case 'Recovery Resources':
          window.location.href = '/user/recovery';
          break;
        case 'Technical Support':
          alert('Opening technical support chat...');
          break;
        default:
          alert(`Accessing ${actionTitle}...`);
      }
    });
  });
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}
