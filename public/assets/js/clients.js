document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('clientSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterClients);
    }
});

function filterClients(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.data-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

function confirmDelete(clientId) {
    if (confirm('Tem certeza que deseja eliminar este cliente?')) {
        fetch(`/clients/delete/${clientId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        }).then(response => {
            if (response.ok) {
                window.location.href = '/clients';
            }
        });
    }
}
