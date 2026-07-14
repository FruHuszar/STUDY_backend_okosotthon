# Repository réteg

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

```
beginTransaction()
    INSERT eszkoz
    INSERT lampa
mindkettő ment     → commit()
bármelyik hibázott → rollBack()
```

**Kapcsolódó:**

- **lastInsertId()** → az első INSERT után visszaadja az új sor **`id`-jét**,
  amit a második INSERT-nél az altábla `eszkoz_id` mezőjébe kell írni.
- **Adatintegritás:** a tranzakció óvja meg az adatbázist attól, hogy szabályt
  sértő (nem létező-nek szánt) állapotba kerüljön.
- **Törlési sorrend:** előbb az altábla (`lampa`/`termosztat`/`dugalj`), csak utána az
  `eszkoz` — mert az idegen kulcs a gyerektábláról mutat a szülőre.
- **OSZTV:** a **COMMIT** a sikeres tranzakció lezárása (nem „CLOSE").

## Repository pattern

- **repository = „raktár/tároló"**: kódréteg, ami úgy viselkedik, **mintha
  objektumgyűjtemény lenne** — elkéred tőle az objektumot, ő elhozza.
- **Elrejti**, hogy a háttérben adatbázis van → objektumokban gondolkodsz, nem SQL-ben.
- Az összes **SQL kizárólag itt** él (SRP).
- ⚠️ A git `commit`/`repository` **csak szóegyezés**, más terület — ne keverd.

## Három fő kérdés

**1. Hogyan kapja a kapcsolatot? → kívülről, a konstruktorban (dependency injection).**
A repository nem hoz létre magának `new Database()`-t, hanem kész PDO-kapcsolatot kap a
konstruktorán át. Miért jobb: (a) mindenki **ugyanazt az egy** kapcsolatot használja;
(b) a repository-t nem érdekli, **hogyan** épül a kapcsolat (SRP); (c) tesztelésnél
beadható helyette egy **ál-kapcsolat (mock)**.

**2. Hogyan tesszük be biztonságosan a felhasználói `id`-t? → prepared statement helyőrzővel.**
Az `id`-t **soha** nem írjuk az SQL szövegébe (`"WHERE id = $id"` ← támadható). Helyette
helyőrző (`WHERE id = :id`), és az értéket külön adjuk át. Az adatbázis a helyőrző értékét
mindig sima adatként kezeli, sosem futtatható parancsként → nincs SQL-injection.
(Beállítva: `EMULATE_PREPARES => false`.)

**3. Honnan tudja, melyik osztályt gyártsa? → egy „megkülönböztető" mezőből (factory).**
Az adat két táblában van: a közös rész az `eszkoz`-ben, a típusfüggő rész a
`lampa`/`termosztat`/`dugalj`-ban. Az `eszkoztipus` tábla adja a típus nevét; ez alapján a
repository egy elágazással dönt: `"lampa"` → `Lampa`, `"termosztat"` → `Termosztat`,
`"dugalj"` → `Dugalj`. Ezt **Factory Method**-nak hívják (a KKK is említi).
