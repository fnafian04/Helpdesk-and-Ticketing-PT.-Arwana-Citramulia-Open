{{-- Switch Role Button & Modal --}}
{{-- Include this partial in sidebar layouts, above the logout button --}}

<button type="button" class="btn-switch-role" id="btnSwitchRole" style="display: none;" onclick="openSwitchRoleModal()">
    <i class="fa-solid fa-repeat"></i> <span id="switchRoleLabel">Ganti Role</span>
</button>

{{-- Switch Role Modal --}}
<div class="switch-role-overlay" id="switchRoleModal">
    <div class="switch-role-modal">
        <div class="switch-role-header">
            <h3><i class="fa-solid fa-repeat"></i> Ganti Role</h3>
            <p>Pilih role yang ingin Anda gunakan</p>
        </div>
        <div class="switch-role-body" id="switchRoleOptions">
            {{-- Role options injected by JS --}}
        </div>
        <div class="switch-role-footer">
            <button type="button" class="btn-switch-cancel" onclick="closeSwitchRoleModal()">
                <i class="fa-solid fa-xmark"></i> Batal
            </button>
        </div>
    </div>
</div>

<style>
    /* Switch Role Button */
    .btn-switch-role {
        width: 100%;
        padding: 12px;
        background: #e8f5e9;
        color: #2e7d32;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        text-align: left;
        padding-left: 20px;
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-family: inherit;
        margin-bottom: 8px;
    }
    .btn-switch-role:hover {
        background: #2e7d32;
        color: white;
    }

    /* Switch Role Modal */
    .switch-role-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 10000;
        justify-content: center;
        align-items: center;
    }
    .switch-role-overlay.active { display: flex; }

    .switch-role-modal {
        background: white;
        border-radius: 16px;
        width: 400px;
        max-width: 92vw;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        overflow: hidden;
        animation: switchSlideUp 0.3s ease;
    }
    @keyframes switchSlideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .switch-role-header {
        background: linear-gradient(135deg, #2e7d32, #66bb6a);
        color: white;
        padding: 20px 24px 16px;
        text-align: center;
    }
    .switch-role-header h3 { margin: 0 0 4px; font-size: 17px; font-weight: 700; }
    .switch-role-header p { margin: 0; font-size: 12px; opacity: 0.9; }

    .switch-role-body {
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .switch-role-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 2px solid #eee;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .switch-role-card:hover {
        border-color: #2e7d32;
        background: #f1f8f1;
        transform: translateX(4px);
    }
    .switch-role-card.current-role {
        border-color: #2e7d32;
        background: #e8f5e9;
    }
    .switch-role-card.current-role::after {
        content: 'AKTIF';
        position: absolute;
        right: 12px;
        font-size: 10px;
        font-weight: 700;
        color: #2e7d32;
        background: #c8e6c9;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .switch-role-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .switch-role-info { flex: 1; }
    .switch-role-info strong { font-size: 13px; color: #333; display: block; }
    .switch-role-info small { font-size: 11px; color: #999; }

    .switch-role-footer {
        padding: 10px 20px 16px;
        text-align: center;
    }
    .btn-switch-cancel {
        background: none;
        border: 1px solid #ddd;
        padding: 7px 20px;
        border-radius: 8px;
        color: #666;
        cursor: pointer;
        font-family: inherit;
        font-size: 13px;
        transition: 0.2s;
    }
    .btn-switch-cancel:hover { background: #f5f5f5; border-color: #bbb; }
</style>

<script>
    /**
     * Switch Role functionality
     */
    const SwitchRoleManager = {
        roleConfig: {
            'master-admin': { icon: 'fa-shield-halved', label: 'Master Admin', color: '#d62828' },
            'helpdesk': { icon: 'fa-headset', label: 'Helpdesk', color: '#2196F3' },
            'technician': { icon: 'fa-screwdriver-wrench', label: 'Teknisi', color: '#FF9800' },
            'requester': { icon: 'fa-user', label: 'Requester', color: '#4CAF50' }
        },

        init() {
            const allRoles = TokenManager.getAllRoles();
            const activeRole = TokenManager.getActiveRole();
            const btn = document.getElementById('btnSwitchRole');
            const label = document.getElementById('switchRoleLabel');

            if (allRoles.length > 1 && btn) {
                btn.style.display = 'flex';
                const config = this.roleConfig[activeRole] || {};
                label.textContent = `Ganti Role (${config.label || activeRole})`;
            }
        },

        renderOptions() {
            const container = document.getElementById('switchRoleOptions');
            const allRoles = TokenManager.getAllRoles();
            const activeRole = TokenManager.getActiveRole();

            if (!container) return;
            container.innerHTML = '';

            allRoles.forEach(role => {
                const config = this.roleConfig[role] || { icon: 'fa-user', label: role, color: '#666' };
                const isCurrent = role === activeRole;
                const card = document.createElement('div');
                card.className = `switch-role-card ${isCurrent ? 'current-role' : ''}`;
                card.innerHTML = `
                    <div class="switch-role-icon" style="background: ${config.color}20; color: ${config.color};">
                        <i class="fa-solid ${config.icon}"></i>
                    </div>
                    <div class="switch-role-info">
                        <strong>${config.label}</strong>
                    </div>
                `;

                if (!isCurrent) {
                    card.addEventListener('click', () => this.doSwitch(role));
                } else {
                    card.style.cursor = 'default';
                }

                container.appendChild(card);
            });
        },

        async doSwitch(newRole) {
            const cards = document.querySelectorAll('.switch-role-card');
            cards.forEach(c => { c.style.pointerEvents = 'none'; c.style.opacity = '0.5'; });

            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Mengganti role...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            }

            const success = await TokenManager.switchRole(newRole);

            if (success) {
                if (typeof Swal !== 'undefined') {
                    const config = this.roleConfig[newRole] || {};
                    await Swal.fire({
                        icon: 'success',
                        title: 'Role Berhasil Diubah!',
                        text: `Beralih ke ${config.label || newRole}`,
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
                // Redirect to new dashboard
                window.location.href = TokenManager.getDashboardUrlForRole(newRole);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat mengganti role.' });
                }
                cards.forEach(c => { c.style.pointerEvents = 'auto'; c.style.opacity = '1'; });
            }
        }
    };

    function openSwitchRoleModal() {
        SwitchRoleManager.renderOptions();
        document.getElementById('switchRoleModal').classList.add('active');
    }

    function closeSwitchRoleModal() {
        document.getElementById('switchRoleModal').classList.remove('active');
    }

    // Init on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        SwitchRoleManager.init();
    });
</script>
