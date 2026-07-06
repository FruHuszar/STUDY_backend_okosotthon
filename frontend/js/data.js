const MOCK_ROOMS = [
  {
    id: 1,
    name: "Nappali",
    devices: [
      {
        id: 101,
        name: "Mennyezeti lámpa",
        type: "lampa",
        protocol: "zigbee",
        state: "on",
        value: "72%",
      },
      {
        id: 102,
        name: "Termosztát",
        type: "termosztat",
        protocol: "wifi",
        state: "on",
        value: "21.4°C → 22°C",
      },
      {
        id: 103,
        name: "TV melletti dugalj",
        type: "dugalj",
        protocol: "wifi",
        state: "on",
        value: "184 W",
      },
    ],
  },
  {
    id: 2,
    name: "Hálószoba",
    devices: [
      {
        id: 201,
        name: "Éjjeli lámpa",
        type: "lampa",
        protocol: "zigbee",
        state: "off",
        value: "0%",
      },
      {
        id: 202,
        name: "Termosztát",
        type: "termosztat",
        protocol: "wifi",
        state: "on",
        value: "19.8°C → 18°C",
      },
    ],
  },
  {
    id: 3,
    name: "Konyha",
    devices: [
      {
        id: 301,
        name: "Munkapult lámpa",
        type: "lampa",
        protocol: "zigbee",
        state: "on",
        value: "100%",
      },
      {
        id: 302,
        name: "Kávégép dugalja",
        type: "dugalj",
        protocol: "wifi",
        state: "off",
        value: "0 W",
      },
    ],
  },
  {
    id: 4,
    name: "Garázs",
    devices: [
      {
        id: 401,
        name: "Mozgásérzékelő",
        type: "erzekelo",
        protocol: "mqtt",
        state: "on",
        value: "nincs mozgás",
      },
      {
        id: 402,
        name: "Fűtőtest dugalja",
        type: "dugalj",
        protocol: "wifi",
        state: "on",
        value: "612 W",
      },
    ],
  },
];

const MOCK_MEASUREMENT_LOG = [
  {
    ts: "2026-07-06 08:00:00",
    device: "Nappali / Termosztát",
    type: "hőmérséklet",
    value: "21.1°C",
  },
  {
    ts: "2026-07-06 08:15:00",
    device: "Nappali / Termosztát",
    type: "hőmérséklet",
    value: "21.3°C",
  },
  {
    ts: "2026-07-06 08:00:00",
    device: "Garázs / Fűtőtest dugalj",
    type: "fogyasztás",
    value: "598 W",
  },
  {
    ts: "2026-07-06 08:15:00",
    device: "Garázs / Fűtőtest dugalj",
    type: "fogyasztás",
    value: "612 W",
  },
  {
    ts: "2026-07-06 08:15:00",
    device: "Nappali / TV dugalj",
    type: "fogyasztás",
    value: "184 W",
  },
  {
    ts: "2026-07-06 08:30:00",
    device: "Hálószoba / Termosztát",
    type: "hőmérséklet",
    value: "19.8°C",
  },
];

const MOCK_SCHEDULES = [
  {
    device: "Nappali / Termosztát",
    plan: "Hétköznap 06:00–08:00 fűtés BE, cél: 22°C",
    etag: '"a1c9f2"',
    lastModified: "2026-07-05 21:12:00",
  },
  {
    device: "Hálószoba / Termosztát",
    plan: "Minden nap 22:00–06:00 fűtés BE, cél: 18°C",
    etag: '"7be410"',
    lastModified: "2026-07-01 09:44:00",
  },
  {
    device: "Konyha / Munkapult lámpa",
    plan: "Hétköznap 06:30–08:00 BE, fényerő 100%",
    etag: '"3f0a2d"',
    lastModified: "2026-06-30 18:02:00",
  },
];

const PROTOCOL_LABEL = {
  wifi: "WiFi",
  zigbee: "Zigbee",
  mqtt: "MQTT",
};
