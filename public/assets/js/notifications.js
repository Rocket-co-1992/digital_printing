class NotificationHandler {
    constructor() {
        this.socket = new WebSocket('ws://' + window.location.host + '/ws');
        this.initializeListeners();
    }

    initializeListeners() {
        this.socket.onmessage = (event) => {
            const notification = JSON.parse(event.data);
            this.showNotification(notification);
        };
    }

    showNotification(data) {
        const notification = document.createElement('div');
        notification.className = `notification ${data.type}`;
        notification.innerHTML = `
            <h4>${data.message}</h4>
            <p>${data.data.details || ''}</p>
        `;
        
        document.getElementById('notifications-container').appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
}

const notificationHandler = new NotificationHandler();
