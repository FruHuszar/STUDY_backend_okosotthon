const MOCK_ROOMS = [
  { id: 1, nev: "Nappali" },
  { id: 2, nev: "Konyha" },
  { id: 3, nev: "Hálószoba" },
  { id: 4, nev: "Dolgozószoba" },
];

const MOCK_DEVICES = [
  { id: 1, room_id: 1, nev: "Nappali lámpa", tipus: "lampa" },
  { id: 2, room_id: 1, nev: "Nappali termosztát", tipus: "termosztat" },
  { id: 3, room_id: 1, nev: "TV dugalj", tipus: "dugalj" },
  { id: 4, room_id: 2, nev: "Konyhai lámpa", tipus: "lampa" },
  { id: 5, room_id: 2, nev: "Hűtő dugalj", tipus: "dugalj" },
  { id: 6, room_id: 3, nev: "Hálószoba lámpa", tipus: "lampa" },
  { id: 7, room_id: 3, nev: "Hálószoba termosztát", tipus: "termosztat" },
  { id: 8, room_id: 4, nev: "Dolgozószoba lámpa", tipus: "lampa" },
  { id: 9, room_id: 4, nev: "Monitor dugalj", tipus: "dugalj" },
];

function mockSeededRandom(mag) {
  let allapot = mag;
  return function () {
    allapot = (allapot * 9301 + 49297) % 233280;
    return allapot / 233280;
  };
}

function generateMockMeasurementLogs() {
  const veletlen = mockSeededRandom(42);
  const naplo = [];
  let id = 1;

  const napokSzama = 7;
  const most = new Date("2026-07-14T00:00:00");

  for (let napOffset = napokSzama - 1; napOffset >= 0; napOffset--) {
    const nap = new Date(most);
    nap.setDate(nap.getDate() - napOffset);

    for (let ora = 0; ora < 24; ora += 2) {
      MOCK_DEVICES.forEach((eszkoz) => {
        if (eszkoz.tipus === "lampa") return;

        const idobelyeg = new Date(nap);
        idobelyeg.setHours(ora, Math.floor(veletlen() * 60), 0, 0);

        if (eszkoz.tipus === "termosztat") {
          const ertek = Math.round((19 + veletlen() * 7) * 10) / 10;
          naplo.push({
            id: id++,
            device_id: eszkoz.id,
            meres_tipusa: "homerseklet",
            ertek,
            idobelyeg: idobelyeg.toISOString().slice(0, 19).replace("T", " "),
          });
        } else if (eszkoz.tipus === "dugalj") {
          const alapFogyasztas = eszkoz.nev.includes("Hűtő") ? 90 : 40;
          const ertek = Math.round(alapFogyasztas + veletlen() * 120);
          naplo.push({
            id: id++,
            device_id: eszkoz.id,
            meres_tipusa: "fogyasztas",
            ertek,
            idobelyeg: idobelyeg.toISOString().slice(0, 19).replace("T", " "),
          });
        }
      });
    }
  }

  return naplo;
}

const MOCK_MEASUREMENT_LOGS = generateMockMeasurementLogs();
