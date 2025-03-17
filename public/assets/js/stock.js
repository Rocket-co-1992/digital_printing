function addMovement(itemId, type) {
    const quantity = prompt(`Quantidade para ${type === 'in' ? 'entrada' : 'saída'}:`);
    if (quantity === null || quantity === '') return;

    const notes = prompt('Observações:');
    
    const formData = new FormData();
    formData.append('item_id', itemId);
    formData.append('type', type);
    formData.append('quantity', quantity);
    formData.append('notes', notes);

    fetch('/stock/movement', {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
}

function checkLowStock() {
    const lowStockItems = document.querySelectorAll('.low-stock');
    if (lowStockItems.length > 0) {
        const message = `Atenção: ${lowStockItems.length} item(s) com estoque baixo!`;
        const notification = document.createElement('div');
        notification.className = 'stock-alert';
        notification.textContent = message;
        document.body.appendChild(notification);
    }
}

document.addEventListener('DOMContentLoaded', checkLowStock);
