const API_BASE = "http://localhost:8000";

class ApiHiba extends Error {
  constructor(uzenet, statuszkod, valasz) {
    super(uzenet);
    this.statuszkod = statuszkod;
    this.valasz = valasz;
  }
}

async function apiFetch(utvonal, opciok = {}) {
  let valasz;

  try {
    valasz = await fetch(`${API_BASE}${utvonal}`, {
      cache: "no-store",
      headers: {
        "Content-Type": "application/json",
        ...(opciok.headers || {}),
      },
      ...opciok,
    });
  } catch (halozatiHiba) {
    throw new ApiHiba(
      `Nem sikerült elérni a backendet (${API_BASE}). Fut a szerver? (${halozatiHiba.message})`,
      0,
      null,
    );
  }

  if (valasz.status === 204) {
    return { adat: null, valasz };
  }

  const szoveg = await valasz.text();
  const adat = szoveg ? JSON.parse(szoveg) : null;

  if (!valasz.ok) {
    const uzenet = (adat && adat.hiba) || `Hiba történt (${valasz.status}).`;
    throw new ApiHiba(uzenet, valasz.status, valasz);
  }

  return { adat, valasz };
}

const Api = {
  // --- Helyiségek ---
  async getHelyisegek() {
    const { adat } = await apiFetch("/helyisegek");
    return adat;
  },

  // --- Eszközök ---
  async getEszkozok() {
    const { adat } = await apiFetch("/eszkozok");
    return adat;
  },

  // --- Mérések ---
  async getMeresek() {
    const { adat } = await apiFetch("/meresek");
    return adat;
  },

  async getNapiFogyasztas(datum) {
    const { adat } = await apiFetch(
      `/meresek/napi-fogyasztas?datum=${encodeURIComponent(datum)}`,
    );
    return adat;
  },

  async getOraiHomerseklet(eszkozId, datum) {
    const { adat } = await apiFetch(
      `/meresek/orai-homerseklet/${eszkozId}?datum=${encodeURIComponent(datum)}`,
    );
    return adat;
  },

  async getTopFogyasztok(napokSzama, limit) {
    const { adat } = await apiFetch(
      `/meresek/top-fogyasztok?napok=${napokSzama}&limit=${limit}`,
    );
    return adat;
  },

  async getMagasFogyasztasuHelyisegek(kuszob) {
    const { adat } = await apiFetch(
      `/meresek/magas-fogyasztasu-helyisegek?kuszob=${kuszob}`,
    );
    return adat;
  },

  // --- Napok (hét napjai) ---
  async getNapok() {
    const { adat } = await apiFetch("/napok");
    return adat;
  },

  // --- Ütemezések ---
  async getUtemezesek() {
    const { adat } = await apiFetch("/utemezesek");
    return adat;
  },

  async getUtemezesEtaggel(id, elozoEtag) {
    const fejlecek = {};
    if (elozoEtag) fejlecek["If-None-Match"] = elozoEtag;

    const nyersValasz = await fetch(`${API_BASE}/utemezesek/${id}`, {
      cache: "no-store",
      headers: fejlecek,
    });

    const ujEtag = nyersValasz.headers.get("ETag");

    if (nyersValasz.status === 304) {
      return { statuszkod: 304, etag: ujEtag || elozoEtag, adat: null };
    }

    const szoveg = await nyersValasz.text();
    const adat = szoveg ? JSON.parse(szoveg) : null;

    if (!nyersValasz.ok) {
      throw new ApiHiba(
        (adat && adat.hiba) || `Hiba (${nyersValasz.status})`,
        nyersValasz.status,
        nyersValasz,
      );
    }

    return { statuszkod: nyersValasz.status, etag: ujEtag, adat };
  },
};
