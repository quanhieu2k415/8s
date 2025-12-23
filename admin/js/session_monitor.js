/**
 * Session Monitor
 * Monitors session timeout and shows warnings/notifications
 */

class SessionMonitor {
    constructor() {
        this.checkInterval = 60000; // Check every 1 minute
        this.activityThrottle = 30000; // Update activity every 30s max
        this.lastActivityUpdate = 0;
        this.warningShown = false;
        this.checking = false;

        this.init();
    }

    init() {
        console.log('[SessionMonitor] Initialized');

        // Start monitoring
        this.startMonitoring();

        // Track user activity
        this.trackActivity();

        // Initial check
        this.checkSession();
    }

    startMonitoring() {
        setInterval(() => {
            if (!this.checking) {
                this.checkSession();
            }
        }, this.checkInterval);
    }

    async checkSession() {
        this.checking = true;

        try {
            const response = await fetch('../backend_api/session_check.php');
            const data = await response.json();

            if (!data.authenticated) {
                this.showLogoutNotification();
                return;
            }

            // Show warning if < 5 minutes remaining
            if (data.timeRemaining < data.warningThreshold && !this.warningShown) {
                this.showWarning(data.timeRemaining);
            }

            // Reset warning if session extended
            if (data.timeRemaining >= data.warningThreshold && this.warningShown) {
                this.warningShown = false;
                const modal = document.getElementById('sessionWarning');
                if (modal) modal.remove();
            }

        } catch (error) {
            console.error('[SessionMonitor] Check failed:', error);
        } finally {
            this.checking = false;
        }
    }

    showWarning(timeRemaining) {
        this.warningShown = true;
        const minutes = Math.ceil(timeRemaining / 60);

        // Remove existing modal if any
        const existing = document.getElementById('sessionWarning');
        if (existing) existing.remove();

        const modal = `
            <div class="session-warning-modal" id="sessionWarning">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <div class="modal-icon warning">⚠️</div>
                    <h3>Session sắp hết hạn</h3>
                    <p>Session của bạn sẽ hết hạn sau <strong>${minutes} phút</strong>.</p>
                    <p class="modal-hint">Bạn có muốn tiếp tục làm việc?</p>
                    <div class="modal-actions">
                        <button onclick="sessionMonitor.extendSession()" class="btn-primary">
                            <span class="material-icons-outlined">refresh</span>
                            Tiếp tục làm việc
                        </button>
                        <button onclick="sessionMonitor.logout()" class="btn-secondary">
                            <span class="material-icons-outlined">logout</span>
                            Đăng xuất
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modal);
        console.log(`[SessionMonitor] Warning shown: ${minutes} minutes remaining`);
    }

    showLogoutNotification() {
        const modal = `
            <div class="session-warning-modal" id="sessionExpired">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <div class="modal-icon error">🔒</div>
                    <h3>Session đã hết hạn</h3>
                    <p>Bạn đã bị đăng xuất do không hoạt động.</p>
                    <p class="modal-hint">Vui lòng đăng nhập lại để tiếp tục.</p>
                    <div class="modal-actions">
                        <button onclick="window.location.href='index.php'" class="btn-primary">
                            <span class="material-icons-outlined">login</span>
                            Đăng nhập lại
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.innerHTML = modal;
        console.log('[SessionMonitor] Logout notification shown');
    }

    async extendSession() {
        await this.updateActivity();

        const modal = document.getElementById('sessionWarning');
        if (modal) modal.remove();

        this.warningShown = false;
        console.log('[SessionMonitor] Session extended');

        // Show success message
        this.showToast('Session đã được gia hạn', 'success');
    }

    logout() {
        window.location.href = 'logout.php';
    }

    trackActivity() {
        const events = ['mousemove', 'keydown', 'click', 'scroll'];

        events.forEach(event => {
            document.addEventListener(event, () => this.onActivity(), {
                passive: true,
                capture: false
            });
        });

        console.log('[SessionMonitor] Activity tracking enabled');
    }

    onActivity() {
        const now = Date.now();

        if (now - this.lastActivityUpdate > this.activityThrottle) {
            this.updateActivity();
            this.lastActivityUpdate = now;
        }
    }

    async updateActivity() {
        try {
            const response = await fetch('../backend_api/session_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                console.log('[SessionMonitor] Activity updated');
            }
        } catch (error) {
            console.error('[SessionMonitor] Activity update failed:', error);
        }
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `session-toast ${type}`;
        toast.innerHTML = `
            <span class="material-icons-outlined">${type === 'success' ? 'check_circle' : 'info'}</span>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize session monitor when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.sessionMonitor = new SessionMonitor();
    });
} else {
    window.sessionMonitor = new SessionMonitor();
}
