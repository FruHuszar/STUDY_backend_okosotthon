# Controller réteg

## A kontroller feladata

1. Fogadja a bejövő HTTP kérést.
2. Meghívja a megfelelő service metódust.
3. A service válaszát (vagy hibáját) HTTP válasszá alakítja — helyes státuszkóddal és JSON-nal.

A controller a legfelső réteg: ő ismeri a HTTP-t (a service és lejjebb már nem).

## Státuszkódok

Háromjegyű szám, amivel a szerver válaszol a „mi lett a kérés sorsa?" kérdésre. A **első számjegy** a kategória:

- **2xx – Siker.** A kérés rendben lefutott. (200 OK, 201 Created, 204 No Content)
- **3xx – Átirányítás / „nézd máshol".** (304 Not Modified)
- **4xx – A KLIENS hibázott.** A kéréssel van baj: hibás adat, nincs jogosultság, nem létező cím. (400 Bad Request, 401 Unauthorized, 404 Not Found)
- **5xx – A SZERVER hibázott.** A kérés rendben volt, de a szerveren romlott el valami. (500 Internal Server Error)

A kulcs a **4xx vs 5xx** határ: 4xx = „te küldtél rosszat" (kliens), 5xx = „nálam romlott el" (szerver).

## Kivétel → státuszkód fordítás

A controller `hibaValasz()` metódusa fordítja a service kivételeit:

| Kivétel / eset | Státuszkód |
|---|---|
| `NemTalalhatoException` | 404 |
| `ErvenytelenAdatException` | 400 |
| Váratlan hiba (leáll a DB, nem várt kivétel) | 500 |

## Valódi működés (példák)

| Művelet | HTTP kód | Válasz |
|---|---|---|
| `osszes` | 200 | eszközök tömbje JSON-ben |
| `egy(3)` | 200 | a megtalált eszköz |
| `egy(999)` | 404 | `{"hiba":"Nincs eszköz: 999"}` |
| `torol(3)` | 200 | törlés megerősítve |
| `torol(999)` | 404 | nem található |

## Kétszintű validáció

- **Controller (400 Bad Request):** „egyáltalán értelmezhető-e a kérés?" — van-e JSON, van-e `tipus`, megvannak-e a kötelező mezők, jó-e a típusuk. Ez a **formai** ellenőrzés.
- **Service (400, `ErvenytelenAdatException`):** „az értelmezhető adat megfelel-e az üzleti szabálynak?" — pl. a fényerő 0–100 közé esik-e. Ez a **tartalmi** ellenőrzés.

Mindkettő 400-at ad a kliensnek (mindkettő a kliens hibája), de más a kérdésük. Ha a controller nem tud objektumot építeni, el sem jut a service-ig.

## Fogalmak

- **`??` (null coalescing):** „ha a bal oldal létezik és nem null → azt add vissza, különben a jobb oldalt". Az opcionális mezők (pl. `helyiseg_id`) hiányát kezeli.
- **201 Created:** sikeres POST-nál a válasz, és benne a létrehozott erőforrás a friss `id`-vel.
