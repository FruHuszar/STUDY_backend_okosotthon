# Model (entitás) réteg

A modell **csak adatot hordoz**. Nincs benne SQL, nincs benne HTML, és **nincs benne validáció** sem — az mind más réteg dolga (az SQL a repository-é, a validáció a service-é).

## Öröklődés

- **`Eszkoz`** – absztrakt ős, a közös mezőkkel (id, megnevezes, allapot, termekszam, garancia, helyisegId).
- **`Lampa` / `Termosztat` / `Dugalj`** – leszármazottak (`extends Eszkoz`), mindegyik a saját extra mezőjével (fenyero / hőmérséklet + üzemmod / fogyasztás).

Minden konkrét eszköz a három típus valamelyike — „csak eszköz" önmagában nem létezik.

## Fogalmak

- **`abstract class`:** közös alap, amiből örökölni lehet, de `new`-val **nem példányosítható**. Ha valaki `new Eszkoz(...)`-t ír, a PHP hibát dob.
- **`protected` láthatóság:** a taghoz az adott osztály **ÉS a leszármazottjai** férnek hozzá (a `private` csak magának az osztálynak engedné).
- **`extends`:** a gyerek automatikusan megkapja az ős összes mezőjét és metódusát, és csak a sajátját teszi hozzá.
- **`parent::__construct(...)`:** a gyerek konstruktora meghívja az ősét a közös mezőkhöz, majd beállítja a saját extráját. A közös részt nem másoljuk le.
- **Nullable típusok (`?int`, `?float`):** egy frissen létrehozott eszköznek még nincs `id`-je vagy mért értéke, ezért ezek a mezők lehetnek `null`.
