document.addEventListener("DOMContentLoaded", () => {
  highlightActiveNav();
  renderRoomsOverview();
  renderMeasurementLog();
  renderSchedules();
  wireSqlConsole();
  wireIotSimulateButton();
});

function highlightActiveNav() {
  const current = window.location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll(".sidebar nav a").forEach((link) => {
    const href = link.getAttribute("href");
    if (href === current) link.classList.add("active");
  });
}

function renderRoomsOverview() {
  const container = document.getElementById("rooms-overview");
  if (!container) return;

  container.innerHTML = MOCK_ROOMS.map((room) => {
    const deviceRows = room.devices
      .map((d) => {
        const dotClass = d.state === "on" ? "on" : "off";
        const protoClass = d.protocol;
        const protoLabel = PROTOCOL_LABEL[d.protocol] || d.protocol;
        return `
          <div class="device-row">
            <span class="live-dot ${dotClass}"></span>
            <span class="device-name">${d.name}</span>
            <span class="badge-protocol ${protoClass}">${protoLabel}</span>
            <span class="device-value">${d.value}</span>
          </div>`;
      })
      .join("");

    return `
      <div class="col-md-6 col-xl-3 mb-4">
        <div class="room-card">
          <div class="room-name">${room.name}</div>
          <div class="room-meta">${room.devices.length} eszköz</div>
          ${deviceRows}
        </div>
      </div>`;
  }).join("");

  const totalDevices = MOCK_ROOMS.reduce((sum, r) => sum + r.devices.length, 0);
  const onlineDevices = MOCK_ROOMS.reduce(
    (sum, r) => sum + r.devices.filter((d) => d.state === "on").length,
    0,
  );

  const totalW = MOCK_ROOMS.reduce((sum, r) => {
    return (
      sum +
      r.devices.reduce((s, d) => {
        const match = /([\d.]+)\s*W/.exec(d.value);
        return s + (match ? parseFloat(match[1]) : 0);
      }, 0)
    );
  }, 0);

  const statTotal = document.getElementById("stat-total-devices");
  const statOnline = document.getElementById("stat-online-devices");
  const statPower = document.getElementById("stat-total-power");
  if (statTotal) statTotal.textContent = totalDevices;
  if (statOnline) statOnline.textContent = onlineDevices;
  if (statPower) statPower.textContent = `${totalW.toFixed(0)} W`;
}

function renderMeasurementLog() {
  const tbody = document.getElementById("measurement-log-body");
  if (!tbody) return;

  tbody.innerHTML = MOCK_MEASUREMENT_LOG.map(
    (row) => `
      <tr>
        <td class="ts">${row.ts}</td>
        <td>${row.device}</td>
        <td>${row.type}</td>
        <td class="data-value">${row.value}</td>
      </tr>`,
  ).join("");
}

function renderSchedules() {
  const container = document.getElementById("schedules-list");
  if (!container) return;

  container.innerHTML = MOCK_SCHEDULES.map(
    (s, i) => `
      <div class="card-soft mb-3" data-schedule-index="${i}">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <div class="fw-semibold">${s.device}</div>
            <div class="text-secondary small">${s.plan}</div>
          </div>
          <button class="btn btn-sm btn-outline-dark simulate-btn" data-index="${i}">
            Eszköz lekéri az ütemezését
          </button>
        </div>
        <div class="mt-3 small mono result-line" id="sim-result-${i}">
          <span class="text-secondary">Utolsó lekérdezés: még nem történt.</span>
        </div>
      </div>`,
  ).join("");
}

function wireIotSimulateButton() {
  document.querySelectorAll(".simulate-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const idx = Number(btn.dataset.index);
      const schedule = MOCK_SCHEDULES[idx];
      const resultEl = document.getElementById(`sim-result-${idx}`);

      const alreadyFetched = btn.dataset.fetched === "true";

      if (!alreadyFetched) {
        resultEl.innerHTML = `
          <span class="status-code s2xx">200 OK</span>
          <span class="ms-2">Új ütemezési terv letöltve · ETag: ${schedule.etag}</span>`;
        btn.dataset.fetched = "true";
      } else {
        resultEl.innerHTML = `
          <span class="status-code s3xx">304 Not Modified</span>
          <span class="ms-2">Nem változott a terv legutóbbi lekérdezés óta — a cache-elt verzió érvényes.</span>`;
      }
    });
  });
}

function wireSqlConsole() {
  const runBtn = document.getElementById("sql-run-btn");
  if (!runBtn) return;

  const textarea = document.getElementById("sql-input");
  const resultBox = document.getElementById("sql-result");

  runBtn.addEventListener("click", () => {
    const query = textarea.value.trim();

    if (!query) {
      resultBox.innerHTML = `<div class="result-placeholder">Írj be egy lekérdezést a futtatáshoz.</div>`;
      return;
    }

    resultBox.innerHTML = `
      <div class="result-placeholder">
        Ez a konzol egyelőre csak a felületet mutatja be.<br>
        A backend elkészülése után (4–6. lépés) ide fog kerülni a valós
        lekérdezés-végrehajtás és az eredménytábla.
      </div>`;
  });
}
