document.addEventListener("DOMContentLoaded", () => {
  highlightActiveNav();

  if (
    document.getElementById("rooms-overview") ||
    document.getElementById("measurement-log-body")
  ) {
    initIndexPage();
  }

  if (document.getElementById("schedules-list")) {
    initUtemezesekPage();
  }

  if (document.getElementById("sql-run-btn")) {
    initLekerdezesekPage();
  }
});

function highlightActiveNav() {
  const current = window.location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll(".sidebar nav a").forEach((link) => {
    const href = link.getAttribute("href");
    if (href === current) link.classList.add("active");
  });
}

function statusChip(statuszkod, szoveg) {
  const kategoria =
    statuszkod >= 500
      ? "s5xx"
      : statuszkod >= 400
        ? "s4xx"
        : statuszkod >= 300
          ? "s3xx"
          : "s2xx";
  return `<span class="status-code ${kategoria}">${statuszkod || "HIBA"}</span><span class="ms-2">${szoveg}</span>`;
}

function hibaSzoveg(hiba) {
  return hiba instanceof ApiHiba
    ? hiba.message
    : `Váratlan hiba: ${hiba.message}`;
}

function eszkozErtekSzoveg(eszkoz) {
  switch (eszkoz.tipus) {
    case "lampa":
      return `${eszkoz.fenyero ?? 0}%`;
    case "termosztat": {
      const aktualis = eszkoz.aktualis_homerseklet ?? "?";
      const cel = eszkoz.celhomerseklet ?? "?";
      return `${aktualis}°C → ${cel}°C`;
    }
    case "dugalj":
      return `${eszkoz.aktualis_fogyasztas ?? 0} W`;
    default:
      return "—";
  }
}

function renderResultTable(container, sorok, oszlopok, uresUzenet) {
  if (!sorok || sorok.length === 0) {
    container.innerHTML = `<div class="result-placeholder">${uresUzenet}</div>`;
    return;
  }

  const fejlec = oszlopok.map((o) => `<th>${o.label}</th>`).join("");
  const testSorok = sorok
    .map(
      (sor) =>
        `<tr>${oszlopok.map((o) => `<td>${sor[o.key] ?? "—"}</td>`).join("")}</tr>`,
    )
    .join("");

  container.innerHTML = `
    <div class="card-soft">
      <table class="log-table">
        <thead><tr>${fejlec}</tr></thead>
        <tbody>${testSorok}</tbody>
      </table>
    </div>`;
}

function maiDatum() {
  return new Date().toISOString().slice(0, 10);
}

async function initIndexPage() {
  const roomsContainer = document.getElementById("rooms-overview");
  const logBody = document.getElementById("measurement-log-body");

  try {
    const [helyisegek, eszkozok] = await Promise.all([
      Api.getHelyisegek(),
      Api.getEszkozok(),
    ]);

    if (roomsContainer)
      renderRoomsOverview(roomsContainer, helyisegek, eszkozok);

    if (logBody) {
      const meresek = await Api.getMeresek();
      renderMeasurementLog(logBody, meresek, eszkozok, helyisegek);
    }
  } catch (hiba) {
    if (roomsContainer) {
      roomsContainer.innerHTML = `<div class="col-12"><div class="result-placeholder">${hibaSzoveg(hiba)}</div></div>`;
    }
    if (logBody) {
      logBody.innerHTML = `<tr><td colspan="4">${hibaSzoveg(hiba)}</td></tr>`;
    }
  }
}

function renderRoomsOverview(container, helyisegek, eszkozok) {
  const eszkozokHelyisegenkent = new Map();
  eszkozok.forEach((e) => {
    const lista = eszkozokHelyisegenkent.get(e.helyiseg_id) || [];
    lista.push(e);
    eszkozokHelyisegenkent.set(e.helyiseg_id, lista);
  });

  container.innerHTML = helyisegek
    .map((helyiseg) => {
      const sajatEszkozok = eszkozokHelyisegenkent.get(helyiseg.id) || [];

      const deviceRows = sajatEszkozok
        .map((eszkoz) => {
          const dotClass = eszkoz.allapot ? "on" : "off";
          const tipusClass = DEVICE_TYPE_BADGE_CLASS[eszkoz.tipus] || "wifi";
          const tipusLabel = DEVICE_TYPE_LABEL[eszkoz.tipus] || eszkoz.tipus;

          return `
            <div class="device-row">
              <span class="live-dot ${dotClass}"></span>
              <span class="device-name">${eszkoz.megnevezes}</span>
              <span class="badge-protocol ${tipusClass}">${tipusLabel}</span>
              <span class="device-value">${eszkozErtekSzoveg(eszkoz)}</span>
            </div>`;
        })
        .join("");

      return `
        <div class="col-md-6 col-xl-3 mb-4">
          <div class="room-card">
            <div class="room-name">${helyiseg.megnevezes}</div>
            <div class="room-meta">${sajatEszkozok.length} eszköz</div>
            ${deviceRows || '<div class="text-secondary small">Nincs eszköz ebben a helyiségben.</div>'}
          </div>
        </div>`;
    })
    .join("");

  const totalDevices = eszkozok.length;
  const onlineDevices = eszkozok.filter((e) => e.allapot).length;
  const totalW = eszkozok
    .filter((e) => e.tipus === "dugalj")
    .reduce((sum, e) => sum + (Number(e.aktualis_fogyasztas) || 0), 0);

  const statTotal = document.getElementById("stat-total-devices");
  const statOnline = document.getElementById("stat-online-devices");
  const statPower = document.getElementById("stat-total-power");
  if (statTotal) statTotal.textContent = totalDevices;
  if (statOnline) statOnline.textContent = onlineDevices;
  if (statPower) statPower.textContent = `${totalW.toFixed(0)} W`;
}

function renderMeasurementLog(tbody, meresek, eszkozok, helyisegek) {
  const helyisegNev = new Map(helyisegek.map((h) => [h.id, h.megnevezes]));
  const eszkozLeiras = new Map(
    eszkozok.map((e) => [
      e.id,
      `${helyisegNev.get(e.helyiseg_id) || "?"} / ${e.megnevezes}`,
    ]),
  );

  const legutobbi20 = meresek.slice(0, 20);

  if (legutobbi20.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4">Még nincs rögzített mérés.</td></tr>`;
    return;
  }

  tbody.innerHTML = legutobbi20
    .map((meres) => {
      const egyseg = MERES_TIPUS_EGYSEG[meres.meres_tipusa] || "";
      return `
        <tr>
          <td class="ts">${meres.idobelyeg ?? "—"}</td>
          <td>${eszkozLeiras.get(meres.eszkoz_id) || `#${meres.eszkoz_id}`}</td>
          <td>${MERES_TIPUS_LABEL[meres.meres_tipusa] || meres.meres_tipusa}</td>
          <td class="data-value">${meres.ertek}${egyseg}</td>
        </tr>`;
    })
    .join("");
}

const utemezesEtagek = new Map();

async function initUtemezesekPage() {
  const container = document.getElementById("schedules-list");

  try {
    const [utemezesek, eszkozok, helyisegek, napok] = await Promise.all([
      Api.getUtemezesek(),
      Api.getEszkozok(),
      Api.getHelyisegek(),
      Api.getNapok(),
    ]);

    renderSchedules(container, utemezesek, eszkozok, helyisegek, napok);
    wireIotSimulateButton();
  } catch (hiba) {
    container.innerHTML = `<div class="result-placeholder">${hibaSzoveg(hiba)}</div>`;
  }
}

function renderSchedules(container, utemezesek, eszkozok, helyisegek, napok) {
  const helyisegNev = new Map(helyisegek.map((h) => [h.id, h.megnevezes]));
  const eszkozNev = new Map(eszkozok.map((e) => [e.id, e]));
  const napNev = new Map(napok.map((n) => [n.id, n.nev]));

  if (utemezesek.length === 0) {
    container.innerHTML = `<div class="result-placeholder">Még nincs rögzített ütemezés.</div>`;
    return;
  }

  container.innerHTML = utemezesek
    .map((utemezes) => {
      const eszkoz = eszkozNev.get(utemezes.eszkoz_id);
      const eszkozLeiras = eszkoz
        ? `${helyisegNev.get(eszkoz.helyiseg_id) || "?"} / ${eszkoz.megnevezes}`
        : `#${utemezes.eszkoz_id} eszköz`;

      const planReszek = [];
      if (utemezes.napok && utemezes.napok.length > 0) {
        planReszek.push(
          utemezes.napok.map((id) => napNev.get(id) || `#${id}`).join(", "),
        );
      }
      if (utemezes.kezdo_ido && utemezes.zaro_ido) {
        planReszek.push(`${utemezes.kezdo_ido}–${utemezes.zaro_ido}`);
      }
      if (utemezes.cel_allapot !== null) {
        planReszek.push(utemezes.cel_allapot ? "BE" : "KI");
      }
      if (utemezes.cel_ertek !== null) {
        planReszek.push(`cél: ${utemezes.cel_ertek}`);
      }

      return `
        <div class="card-soft mb-3" data-schedule-id="${utemezes.id}">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
              <div class="fw-semibold">${eszkozLeiras}</div>
              <div class="text-secondary small">${planReszek.join(" · ") || "Nincs megadva részlet."}</div>
            </div>
            <button class="btn btn-sm btn-outline-dark simulate-btn" data-id="${utemezes.id}">
              Eszköz lekéri az ütemezését
            </button>
          </div>
          <div class="mt-3 small mono result-line" id="sim-result-${utemezes.id}">
            <span class="text-secondary">Utolsó lekérdezés: még nem történt.</span>
          </div>
        </div>`;
    })
    .join("");
}

function wireIotSimulateButton() {
  document.querySelectorAll(".simulate-btn").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = Number(btn.dataset.id);
      const resultEl = document.getElementById(`sim-result-${id}`);

      btn.disabled = true;
      resultEl.innerHTML = `<span class="text-secondary">Lekérdezés folyamatban…</span>`;

      try {
        const elozoEtag = utemezesEtagek.get(id) || null;
        const { statuszkod, etag } = await Api.getUtemezesEtaggel(
          id,
          elozoEtag,
        );

        if (etag) utemezesEtagek.set(id, etag);

        if (statuszkod === 200) {
          resultEl.innerHTML = statusChip(
            200,
            `Új ütemezési terv letöltve · ETag: ${etag ?? "—"}`,
          );
        } else {
          resultEl.innerHTML = statusChip(
            304,
            "Nem változott a terv legutóbbi lekérdezés óta — a cache-elt verzió érvényes.",
          );
        }
      } catch (hiba) {
        resultEl.innerHTML = statusChip(hiba.statuszkod || 0, hibaSzoveg(hiba));
      } finally {
        btn.disabled = false;
      }
    });
  });
}

async function initLekerdezesekPage() {
  renderMockMeasurementLog();
  registerMockTables();
  wireSqlConsole();
}

function registerMockTables() {
  if (typeof alasql === "undefined") return;
  alasql.tables.measurement_logs = { data: MOCK_MEASUREMENT_LOGS };
  alasql.tables.devices = { data: MOCK_DEVICES };
  alasql.tables.rooms = { data: MOCK_ROOMS };
}

function renderMockMeasurementLog() {
  const tbody = document.getElementById("mock-measurement-log-body");
  if (!tbody) return;

  const eszkozNev = new Map(MOCK_DEVICES.map((e) => [e.id, e.nev]));
  const legutobbi20 = [...MOCK_MEASUREMENT_LOGS]
    .sort((a, b) => (a.idobelyeg < b.idobelyeg ? 1 : -1))
    .slice(0, 20);

  tbody.innerHTML = legutobbi20
    .map((meres) => {
      const egyseg = MERES_TIPUS_EGYSEG[meres.meres_tipusa] || "";
      return `
        <tr>
          <td class="ts">${meres.idobelyeg}</td>
          <td>${eszkozNev.get(meres.device_id) || `#${meres.device_id}`}</td>
          <td>${MERES_TIPUS_LABEL[meres.meres_tipusa] || meres.meres_tipusa}</td>
          <td class="data-value">${meres.ertek}${egyseg}</td>
        </tr>`;
    })
    .join("");
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

    if (typeof alasql === "undefined") {
      resultBox.innerHTML = `<div class="result-placeholder">Az SQL-motor betöltése nem sikerült — ellenőrizd az internetkapcsolatot.</div>`;
      return;
    }

    try {
      const eredmeny = alasql(query);

      renderSqlResult(resultBox, eredmeny);
    } catch (hiba) {
      resultBox.innerHTML = `<div class="result-placeholder">Hibás lekérdezés: ${hiba.message}</div>`;
    }
  });
}

function renderSqlResult(container, eredmeny) {
  if (!Array.isArray(eredmeny) || eredmeny.length === 0) {
    container.innerHTML = `<div class="result-placeholder">A lekérdezés lefutott, de nem adott vissza sort.</div>`;
    return;
  }

  const oszlopok = Object.keys(eredmeny[0]).map((kulcs) => ({
    key: kulcs,
    label: kulcs,
  }));
  renderResultTable(container, eredmeny, oszlopok, "Nincs találat.");
}
