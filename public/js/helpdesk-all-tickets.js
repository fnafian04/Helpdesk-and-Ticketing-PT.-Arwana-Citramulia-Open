document.addEventListener("DOMContentLoaded", function () {
  // === GLOBAL CONFIG ===
  const API_URL = typeof window.API_URL !== "undefined" ? window.API_URL : ""; // Fallback

  // === HELPER: Escape HTML ===
  function escapeHtml(text) {
    if (!text) return "";
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return String(text).replace(/[&<>"']/g, (m) => map[m]);
  }

  // === DOM ELEMENTS ===
  const tableBody = document.getElementById("ticketTableBody");
  const noData = document.getElementById("noDataMessage");
  const paginationControls = document.getElementById("paginationControls");
  const searchInput = document.getElementById("searchInput");

  // === CONFIG ===
  const rowsPerPage = 15; // Match API per_page
  const authToken =
    sessionStorage.getItem("auth_token") || localStorage.getItem("auth_token");
  // Cache only for modal details
  const _detailCache = new Map();

  let currentTickets = [];
  let paginationMeta = null;
  let currentPage = 1;
  let searchQuery = "";

  // === HELPER: Fetch Data ===
  async function fetchWithAuth(url) {
    const headers = {
      Authorization: `Bearer ${authToken}`,
      Accept: "application/json",
      "Content-Type": "application/json",
    };
    try {
      const response = await fetch(url, { headers });
      if (response.status === 401) {
        // Opsional: Redirect login
        return null;
      }
      return response;
    } catch (error) {
      console.error("Fetch Error:", error);
      return null;
    }
  }

  // === 1. LOAD DATA WITH SERVER-SIDE PAGINATION ===
  async function loadTickets(page = 1) {
    currentPage = page;
    tableBody.innerHTML =
      '<tr><td colspan="6" class="loading-row"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data tiket...</td></tr>';
    if (noData) noData.style.display = "none";

    try {
      // Use server-side pagination - data already complete from API
      const searchParam = searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : "";
      const apiUrl = `${API_URL}/api/tickets?page=${page}&per_page=${rowsPerPage}${searchParam}`;
      console.log("Fetching tickets from:", apiUrl);
      const res = await fetchWithAuth(apiUrl);

      if (!res) {
        throw new Error("Network request failed (res is null)");
      }
      if (!res.ok) {
        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
      }

      const json = await res.json();
      console.log("API Response:", json);
      
      const rawData = json.data || [];
      paginationMeta = json.pagination || json.meta || null;

      // Map data - no need for additional fetching, data is complete
      currentTickets = rawData.map((t) => ({
        id: t.id,
        ticket_number: t.ticket_number || `T-${t.id}`,
        subject: t.title || t.subject || "-",
        requester: t.requester?.name || t.requester_name || "-",
        dept: t.requester?.department?.name || t.department?.name || "-",
        tech: t.assignment?.technician?.name || "-",
        tech_dept: t.assignment?.technician?.department?.name || null,
        status: t.status?.name || t.status || "Pending",
        date: t.created_at || t.date,
        raw: t,
      }));

      renderTable();
      console.log(`Successfully loaded ${currentTickets.length} tickets (page ${page})`);
    } catch (e) {
      console.error("Load Error:", e);
      tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:#d62828;"><i class="fa-solid fa-circle-exclamation"></i> <br />Gagal memuat data tiket.<br /><small style="font-size:11px; color:#999;">${escapeHtml(e.message)}</small></td></tr>`;
    }
  }
  // === 2. RENDER TABLE (Direct from API data) ===
  function renderTable() {
    tableBody.innerHTML = "";

    if (currentTickets.length === 0) {
      if (noData) noData.style.display = "block";
      paginationControls.innerHTML = "";
      return;
    }

    if (noData) noData.style.display = "none";

    // Render directly - data is already complete from API
    currentTickets.forEach((t) => {
      // Mapping Status Class
      let statusClass = "status-pending";
      const s = String(t.status).toLowerCase();
      if (s.includes("open")) statusClass = "status-open";
      else if (s.includes("assigned")) statusClass = "status-pending";
      else if (s.includes("progress")) statusClass = "status-progress";
      else if (s.includes("resolved")) statusClass = "status-resolved";
      else if (s.includes("close")) statusClass = "status-closed";
      else if (s.includes("reject")) statusClass = "status-rejected";

      // Format Tanggal
      const dateStr = new Date(t.date).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
      });

      const techDisplay = t.tech_dept ? `${t.tech} (${t.tech_dept})` : t.tech;

      const row = `
                <tr id="row-${t.id}">
                    <td><strong>${escapeHtml(t.ticket_number)}</strong></td>
                    <td>
                        <div style="font-weight:600; color:#333;">${escapeHtml(t.subject)}</div>
                        <div style="font-size:12px; color:#888;">
                            Oleh: ${escapeHtml(t.requester)}
                        </div>
                    </td>
                    <td>${escapeHtml(t.dept)}</td>
                    <td>${escapeHtml(techDisplay)}</td>
                    <td><span class="status-badge ${statusClass}">${escapeHtml(t.status)}</span></td>
                    <td style="text-align: right;">
                        <button class="btn-view" onclick="openDetailById(${t.id})" title="Lihat Detail">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
      tableBody.innerHTML += row;
    });

    renderPaginationControls();
  }

  // === 4. PAGINATION CONTROLS (Server-side) ===
  function renderPaginationControls() {
    paginationControls.innerHTML = "";
    
    if (!paginationMeta) return;

    const { current_page, last_page, total, from, to } = paginationMeta;

    const info = document.getElementById("paginationInfo");
    if (info) {
      info.innerText = `Menampilkan ${from || 0}-${to || 0} dari ${total || 0} tiket (Halaman ${current_page} dari ${last_page})`;
    }

    if (last_page <= 1) return;

    // Previous button
    const prev = createBtn(
      '<i class="fa-solid fa-chevron-left"></i> Prev',
      current_page === 1,
      () => {
        if (current_page > 1) loadTickets(current_page - 1);
      },
    );
    paginationControls.appendChild(prev);

    // Page numbers (show first, last, and pages around current)
    for (let i = 1; i <= last_page; i++) {
      if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
        const pageBtn = createBtn(
          String(i),
          false,
          () => loadTickets(i),
          i === current_page
        );
        paginationControls.appendChild(pageBtn);
      } else if (i === current_page - 3 || i === current_page + 3) {
        const dots = document.createElement("span");
        dots.innerText = "...";
        dots.style.cssText = "padding: 0 8px; color: #999;";
        paginationControls.appendChild(dots);
      }
    }

    // Next button
    const next = createBtn(
      'Next <i class="fa-solid fa-chevron-right"></i>',
      current_page === last_page,
      () => {
        if (current_page < last_page) loadTickets(current_page + 1);
      },
    );
    paginationControls.appendChild(next);
  }

  function createBtn(html, disabled, onClick, isActive = false) {
    const div = document.createElement("div");
    div.className = `page-btn ${disabled ? "disabled" : ""} ${isActive ? "active" : ""}`;
    let styles = "";
    if (disabled) styles = "opacity: 0.5; cursor: not-allowed;";
    else if (isActive) styles = "background-color: #1976d2; color: white; font-weight: 600;";
    div.style.cssText = styles;
    div.innerHTML = html;
    if (!disabled) div.onclick = onClick;
    return div;
  }

  // === 5. SEARCH (Client-side for current page, or trigger reload for server-side) ===
  if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener("input", function (e) {
      const q = e.target.value.trim();
      
      // Debounce search to avoid too many requests
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        searchQuery = q;
        loadTickets(1); // Reload from page 1 with search query
      }, 500); // Wait 500ms after user stops typing
    });
  }

  // === 6. MODAL DETAIL ===
  window.openDetailById = async function (id) {
    const modal = document.getElementById("detailModal");

    // Tampilkan modal loading
    modal.style.display = "flex";
    document.getElementById("mTimeline").innerHTML =
      '<div style="text-align:center; padding:20px;">Memuat...</div>';

    // Cek Cache Detail dulu (karena mungkin sudah di-load row tadi)
    let detail = _detailCache.get(id);

    if (!detail) {
      try {
        const res = await fetchWithAuth(`${API_URL}/api/tickets/${id}`);
        if (res && res.ok) {
          const json = await res.json();
          detail = json.data || json.ticket || json;
          _detailCache.set(id, detail);
        }
      } catch (e) {
        console.warn("Detail fetch failed", e);
      }
    }

    if (detail) {
      // Get data
      const requesterName = escapeHtml(
        detail.requester?.name || detail.requester_name || "-",
      );
      const technicianName = escapeHtml(
        (detail.assignment &&
          detail.assignment.technician &&
          detail.assignment.technician.name) ||
          detail.technician_name ||
          "-",
      );
      let deptTxt = "-";
      if (detail.requester?.department?.name)
        deptTxt = detail.requester.department.name;
      else if (detail.department?.name) deptTxt = detail.department.name;

      const currentStatus = detail.status?.name || detail.status || "-";
      const isResolved = currentStatus.toUpperCase() === "RESOLVED";

      // Update header
      document.getElementById("mId").innerText =
        `#${detail.ticket_number || detail.id}`;
      document.getElementById("mSubject").innerText = escapeHtml(
        detail.title || detail.subject || "-",
      );
      document.getElementById("mDept").innerText =
        `${deptTxt} • Status: ${currentStatus}`;

      // Render detail info
      const detailContent = document.getElementById("modalDetailContent");
      detailContent.innerHTML =
        `
        <div style="margin-bottom: 20px;">
          <h4 id="mSubject" style="font-size: 18px; font-weight: 700; color: #333;">` +
        escapeHtml(detail.title || detail.subject || "-") +
        `</h4>
          <p id="mDept" style="color: #666; font-size: 13px;">${deptTxt} • Status: ${currentStatus}</p>
        </div>
        
        <div style="margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
          <div style="margin-bottom: 12px;">
            <label style="font-size: 12px; color: #666; font-weight: 600;">Pengaju</label>
            <div style="font-size: 14px; color: #333; font-weight: 500;">${requesterName}</div>
          </div>
          <div style="margin-bottom: 12px;">
            <label style="font-size: 12px; color: #666; font-weight: 600;">Departemen</label>
            <div style="font-size: 14px; color: #333; font-weight: 500;">${deptTxt}</div>
          </div>
          <div>
            <label style="font-size: 12px; color: #666; font-weight: 600;">Teknisi Ditugaskan</label>
            <div style="font-size: 14px; color: #333; font-weight: 500;">${technicianName}</div>
          </div>
        </div>

        <div class="detail-group">
          <label class="detail-label">Riwayat Perjalanan</label>
          <div class="timeline" id="mTimeline" style="margin-top: 10px;"></div>
        </div>

        <div class="detail-group" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e9e9e9;">
          <div style="display: flex; gap: 10px;">
            <button id="rejectBtn" class="btn-reject" ${!isResolved ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ""} 
              onclick="window.rejectTicket(${detail.id})" title="${!isResolved ? "Hanya bisa reject jika status RESOLVED" : "Reject dan buat OPEN kembali"}">
              <i class="fa-solid fa-times"></i> Reject
            </button>
            <button id="closeBtn" class="btn-close" ${!isResolved ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ""}
              onclick="window.closeTicket(${detail.id})" title="${!isResolved ? "Hanya bisa close jika status RESOLVED" : "Tutup tiket"}">
              <i class="fa-solid fa-check"></i> Close
            </button>
          </div>
        </div>
      `;

      // Render Timeline
      const logs = detail.logs || detail.histories || [];
      renderTimeline(logs, currentStatus);

      // Store detail for action buttons
      window._currentTicketDetail = detail;
    }
  };

  function renderTimeline(logs, currentStatus) {
    const container = document.getElementById("mTimeline");

    let html = "";

    if (!logs || logs.length === 0) {
      html =
        '<div style="color:#999; font-style:italic; padding: 15px; text-align: center;">Belum ada riwayat detail tersedia.</div>';
    } else {
      // Sort logs by date ascending
      const sortedLogs = [...logs].sort((a, b) => {
        const dateA = new Date(a.created_at || a.date || 0).getTime();
        const dateB = new Date(b.created_at || b.date || 0).getTime();
        return dateA - dateB;
      });

      sortedLogs.forEach((l) => {
        const logStatus = (
          l.status?.name ||
          l.status ||
          l.action ||
          "Update"
        ).toUpperCase();
        const date = new Date(l.created_at || l.date);
        const dateStr = date.toLocaleDateString("id-ID", {
          weekday: "short",
          year: "numeric",
          month: "short",
          day: "numeric",
        });
        const timeStr = date.toLocaleTimeString("id-ID", {
          hour: "2-digit",
          minute: "2-digit",
        });

        // Color code for different statuses
        let statusColor = "#999";
        if (logStatus.includes("OPEN")) statusColor = "#1976d2";
        else if (logStatus.includes("ASSIGNED")) statusColor = "#f57c00";
        else if (logStatus.includes("PROGRESS")) statusColor = "#7b1fa2";
        else if (logStatus.includes("RESOLVED")) statusColor = "#388e3c";
        else if (logStatus.includes("CLOSED")) statusColor = "#424242";

        html += `
          <div class="timeline-item" style="margin-bottom: 15px; padding-left: 30px; position: relative;">
            <div style="position: absolute; left: 0; top: 3px; width: 12px; height: 12px; background-color: ${statusColor}; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px ${statusColor};\"></div>
            <div>
              <div style="font-weight: 600; color: ${statusColor}; font-size: 13px;">${logStatus}</div>
              <div style="font-size: 12px; color: #999; margin-top: 3px;">${dateStr} • ${timeStr}</div>
              <div style="margin-top: 6px; color: #666; font-size: 13px;">${escapeHtml(l.note || l.message || l.description || "")}</div>
            </div>
          </div>
        `;
      });
    }

    container.innerHTML = html;
  }

  window.closeModal = function () {
    document.getElementById("detailModal").style.display = "none";
  };

  window.rejectTicket = async function (ticketId) {
    const detail = window._currentTicketDetail;
    if (!detail) return;

    const currentStatus = detail.status?.name || detail.status || "";
    if (currentStatus.toUpperCase() !== "RESOLVED") {
      alert("Tiket hanya bisa di-reject jika status RESOLVED");
      return;
    }

    if (
      !confirm(
        `Yakin ingin reject tiket #${detail.ticket_number}? Tiket akan kembali ke status OPEN.`,
      )
    ) {
      return;
    }

    try {
      const res = await fetchWithAuth(
        `${API_URL}/api/tickets/${ticketId}/reject`,
        {
          method: "POST",
          headers: {
            Authorization: `Bearer ${authToken}`,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        },
      );

      if (res && res.ok) {
        alert("Tiket berhasil di-reject dan kembali ke status OPEN");
        closeModal();
        loadTickets();
      } else {
        alert("Gagal reject tiket");
      }
    } catch (e) {
      console.error("Reject error:", e);
      alert("Error: " + e.message);
    }
  };

  window.closeTicket = async function (ticketId) {
    const detail = window._currentTicketDetail;
    if (!detail) return;

    const currentStatus = detail.status?.name || detail.status || "";
    if (currentStatus.toUpperCase() !== "RESOLVED") {
      alert("Tiket hanya bisa di-close jika status RESOLVED");
      return;
    }

    if (!confirm(`Yakin ingin close tiket #${detail.ticket_number}?`)) {
      return;
    }

    try {
      const res = await fetchWithAuth(
        `${API_URL}/api/tickets/${ticketId}/close`,
        {
          method: "POST",
          headers: {
            Authorization: `Bearer ${authToken}`,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        },
      );

      if (res && res.ok) {
        alert("Tiket berhasil di-close");
        closeModal();
        loadTickets();
      } else {
        alert("Gagal close tiket");
      }
    } catch (e) {
      console.error("Close error:", e);
      alert("Error: " + e.message);
    }
  };

  window.onclick = function (e) {
    const modal = document.getElementById("detailModal");
    if (e.target == modal) closeModal();
  };

  // INIT
  loadTickets();
});
