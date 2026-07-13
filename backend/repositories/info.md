## Tranzakciók (transaction)

**Probléma:** egy eszköz mentése két táblát érint (`eszkoz` + altábla, pl. `lampa`),
azaz **két külön INSERT**. Ha az első sikerül, de a második elhasal, **félkész
(inkonzisztens) adat** marad → csonka eszköz, amivel nem lehet dolgozni.

**Megoldás:** a **tranzakció** több SQL-műveletet fog össze egy csomagba, aminek
szabálya: **vagy mind sikerül, vagy egyik sem**. Ezt a tulajdonságot **atomiságnak
(atomicity)** hívják → oszthatatlan, nincs félút.

**A három parancs (PDO-ban):**

- **BEGIN** (`beginTransaction()`) → a műveletek gyűjtésének kezdete, még nincs rögzítés
- **COMMIT** (`commit()`) → **minden ment, most rögzítsd véglegesen** az összeset
- **ROLLBACK** (`rollBack()`) → **hiba volt, dobj el mindent**, mintha el sem kezdtük volna

**Menete:**
beginTransaction()
INSERT eszkoz
INSERT lampa
mindkettő ment → commit()
bármelyik hibázott → rollBack()

**Kapcsolódó:**

- **lastInsertId()** → az első INSERT után visszaadja az új sor **`id`-jét**,
  amit a második INSERT-nél az altábla `eszkoz_id` mezőjébe kell írni.
- **Adatintegritás:** a tranzakció óvja meg az adatbázist attól, hogy szabályt
  sértő (nem létező-nek szánt) állapotba kerüljön.
- OSZTV: a **COMMIT** a sikeres tranzakció lezárása (nem „CLOSE").

## Repository pattern

- **repository = "raktár/tároló"**: kódréteg, ami úgy viselkedik, **mintha
  objektumgyűjtemény lenne** — elkéred tőle az objektumot, ő elhozza.
- **Elrejti**, hogy a háttérben adatbázis van → objektumokban gondolkodsz, nem SQL-ben.
- Az összes **SQL kizárólag itt** él (SRP).

1. A repository-nak szüksége van erre a kapcsolatra, hogy SQL-t futtasson. Hogyan kapja a kapcsolatot? → kívülről, a konstruktorban. A repository nem hoz létre magának new Database()-t. Kap egy kész PDO-kapcsolatot a konstruktorán keresztül — ez a dependency injection. Miért jobb ez? Három okból: (a) mindenki ugyanazt az egy kapcsolatot használja, nem nyit mindenki sajátot; (b) a repository nem tudja és nem is érdekli, hogyan épül a kapcsolat (SRP); (c) tesztelésnél beadhatsz helyette egy ál-kapcsolatot (mock) — ez a 7. lépésed, és pont ez tette a próbatesztet nehézzé.
2. Ha a repository egy eszközt keres id alapján, és az id a felhasználótól jön (pl. /eszkozok/5), hogyan tennéd bele biztonságosan az SQL-be? → prepared statement helyőrzővel. Az id-t soha nem írjuk bele az SQL szövegébe ("WHERE id = $id" ← ez a támadható forma). Helyette egy helyőrzőt teszünk (WHERE id = :id), és az értéket külön adjuk át. Az adatbázis a helyőrző értékét mindig sima adatként kezeli, sosem futtatható parancsként — így nem lehet SQL-injectiont csinálni. Ezt már beállítottuk: EMULATE_PREPARES => false.
3. Amikor egy eszközt kiolvasol, az adata két táblában van: a közös rész az eszkoz táblában, a típusfüggő rész (fényerő / hőmérséklet / fogyasztás) a lampa/termosztat/dugalj táblában. A repository visszakap egy sort, benne pl. eszkoztipus_id = 1. Honnan fogja tudni, hogy melyik model-osztályt (Lampa? Termosztat? Dugalj?) kell létrehoznia az adatból? → egy „megkülönböztető" mezőből. Az eszkoztipus táblában ott a típus neve. Ha a lekérdezés visszaadja ezt (pl. "lampa"), a repository egy elágazással eldönti: "lampa" → Lampa, "termosztat" → Termosztat, "dugalj" → Dugalj. Ezt a mintát factory-nak (gyártó) hívják — a KKK is említi „Factory Method"-ként. Egy külön kis metódus a nyers sorból legyártja a helyes típusú objektumot.
