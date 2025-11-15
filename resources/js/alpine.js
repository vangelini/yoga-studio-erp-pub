import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


document.addEventListener('alpine:init', () => {
    Alpine.data('notificationBell', () => ({
        open: false,
        loading: true,
        items: [],
        unread: 0,
        init() {
            this.fetchNotifications();
            setInterval(() => this.fetchNotifications(), 60000);
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.markVisibleAsRead();
            }
        },
        fetchNotifications() {
            fetch('/notifications/feed', { headers: { 'Accept': 'application/json' }})
                .then(response => response.json())
                .then(data => {
                    this.items = data.notifications || [];
                    this.unread = data.unread || 0;
                    this.loading = false;
                });
        },
        markVisibleAsRead() {
            this.items.filter(item => !item.is_read).forEach(item => {
                fetch(`/notifications/dispatches/${item.id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    item.is_read = true;
                    if (this.unread > 0) {
                        this.unread -= 1;
                    }
                });
            });
        },
        remove(item) {
            fetch(`/notifications/dispatches/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                    'Accept': 'application/json'
                }
            }).then(() => {
                this.items = this.items.filter(entry => entry.id !== item.id);
            });
        }
    }));
});
