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
        transition: 0.18s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-family: inherit;
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn-switch-role:hover {
        background: #2e7d32;
        color: white;
    }

    /* Switch Role Modal (desktop centered) */
    /* Overlay made opaque + blurred so page items behind the modal are not visible */
    .switch-role-overlay {
        display: none;
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        z-index: 10000;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .switch-role-overlay.active {
        display: flex;
    }

    .switch-role-modal {
        background: white;
        border-radius: 16px;
        width: 420px;
        max-width: 92vw;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
        overflow: hidden;
        animation: switchSlideUp 0.28s ease;
        z-index: 10001;
        /* keep modal visually above overlay */
    }

    @keyframes switchSlideUp {
        from {
            transform: translateY(18px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .switch-role-header {
        background: linear-gradient(135deg, #2e7d32, #66bb6a);
        color: white;
        padding: 18px 20px 14px;
        text-align: center;
    }

    .switch-role-header h3 {
        margin: 0 0 4px;
        font-size: 17px;
        font-weight: 700;
    }

    .switch-role-header p {
        margin: 0;
        font-size: 12px;
        opacity: 0.95;
    }

    /* Default: stacked list for very small screens */
    .switch-role-body {
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .switch-role-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 2px solid #eee;
        border-radius: 10px;
        cursor: pointer;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        position: relative;
        background: white;
    }

    .switch-role-card:active {
        transform: translateY(1px);
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

    .switch-role-info {
        flex: 1;
        min-width: 0;
    }

    .switch-role-info strong {
        font-size: 13px;
        color: #333;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .switch-role-info small {
        font-size: 11px;
        color: #999;
    }

    .switch-role-footer {
        padding: 10px 16px 18px;
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
        transition: 0.16s;
    }

    .btn-switch-cancel:hover {
        background: #f5f5f5;
        border-color: #bbb;
    }

    /* ------------------ Responsive behavior ------------------ */
    /* Tablet / small desktop: show role options as 2-column grid */
    @media (min-width: 900px) {

        /* Tablet and up: 2-column / grid layout */
        .switch-role-body {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 18px 20px;
        }

        .switch-role-card {
            padding: 14px;
            min-height: 64px;
        }
    }

    /* Larger screens: allow more flexible columns */
    @media (min-width: 1200px) {
        .switch-role-modal {
            width: 540px;
        }

        .switch-role-body {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
        }
    }

    /* Mobile: full-screen stacked list (table-like) */
    @media (max-width: 575px) {
        .switch-role-overlay {
            align-items: center;
            padding: 0;
        }

        .switch-role-modal {
            width: 100%;
            max-width: 100%;
            height: 100vh;
            border-radius: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: none;
        }

        .switch-role-header {
            padding: 16px;
            position: sticky;
            top: 0;
            z-index: 3;
            text-align: left;
        }

        .switch-role-header h3 {
            font-size: 16px;
        }

        .switch-role-body {
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            flex: 1;
            background: #fff;
        }

        /* Table-like stacked rows: full-width cards with subtle separators */
        .switch-role-card {
            border-radius: 0;
            border-left: none;
            border-right: none;
            border-top: 1px solid #f1f1f1;
            border-bottom: none;
            padding: 16px 12px;
            gap: 12px;
            min-height: 60px;
            background: transparent;
        }

        .switch-role-card+.switch-role-card {
            border-top: 1px solid #eee;
        }

        .switch-role-card .switch-role-info {
            padding-left: 4px;
        }

        .switch-role-icon {
            width: 48px;
            height: 48px;
            font-size: 18px;
            border-radius: 8px;
        }

        .switch-role-info strong {
            font-size: 15px;
        }

        .switch-role-card.current-role::after {
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
        }

        .switch-role-footer {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 12px;
            border-top: 1px solid #eee;
            z-index: 3;
        }

        .btn-switch-cancel {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
        }

        .btn-switch-role {
            font-size: 13px;
            padding: 10px 12px;
            padding-left: 16px;
        }
    }
</style>

<script>
    /**
     * Switch Role functionality
     */
    const SwitchRoleManager = {
        roleConfig: {
            'master-admin': {
                icon: 'fa-shield-halved',
                label: 'Master Admin',
                color: '#d62828'
            },
            'helpdesk': {
                icon: 'fa-headset',
                label: 'Helpdesk',
                color: '#2196F3'
            },
            'technician': {
                icon: 'fa-screwdriver-wrench',
                label: 'Teknisi',
                color: '#FF9800'
            },
            'requester': {
                icon: 'fa-user',
                label: 'Requester',
                color: '#4CAF50'
            }
        },

        init() {
            const allRoles = TokenManager.getAllRoles();
            const activeRole = TokenManager.getActiveRole();
            const btn = document.getElementById('btnSwitchRole');
            const mobileBtn = document.getElementById('mobileSwitchRoleBtn');
            const label = document.getElementById('switchRoleLabel');

            const shouldShow = allRoles && allRoles.length > 1;

            if (btn) {
                btn.style.display = shouldShow ? 'flex' : 'none';
            }

            // show the mobile header shortcut only on small screens
            if (mobileBtn) {
                const isMobile = window.matchMedia('(max-width: 992px)').matches;
                mobileBtn.style.display = shouldShow && isMobile ? 'flex' : 'none';

                // update visibility on resize
                window.addEventListener('resize', () => {
                    const nowMobile = window.matchMedia('(max-width: 992px)').matches;
                    mobileBtn.style.display = shouldShow && nowMobile ? 'flex' : 'none';
                });
            }

            if (shouldShow && label) {
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
                const config = this.roleConfig[role] || {
                    icon: 'fa-user',
                    label: role,
                    color: '#666'
                };
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
            cards.forEach(c => {
                c.style.pointerEvents = 'none';
                c.style.opacity = '0.5';
            });

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Mengganti role...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak dapat mengganti role.'
                    });
                }
                cards.forEach(c => {
                    c.style.pointerEvents = 'auto';
                    c.style.opacity = '1';
                });
            }
        }
    };

    function openSwitchRoleModal() {
        SwitchRoleManager.renderOptions();
        const modalEl = document.getElementById('switchRoleModal');
        modalEl.classList.add('active');

        // Lock background scroll (helpful when modal fills viewport on small devices)
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';

        // focus first interactive card for keyboard users
        setTimeout(() => {
            const firstInteractive = document.querySelector(
                '#switchRoleOptions .switch-role-card:not(.current-role)');
            const fallback = document.querySelector('#switchRoleOptions .switch-role-card');
            const toFocus = firstInteractive || fallback;
            if (toFocus) {
                toFocus.tabIndex = 0;
                toFocus.focus();
            }
        }, 50);
    }

    function closeSwitchRoleModal() {
        document.getElementById('switchRoleModal').classList.remove('active');
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }

    // Init on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        SwitchRoleManager.init();
    });
</script>
