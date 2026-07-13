<?php

require_once __DIR__ . '/../models/Lampa.php';
require_once __DIR__ . '/../models/Termosztat.php';
require_once __DIR__ . '/../models/Dugalj.php';

class EszkozRepository
{
    private PDO $kapcsolat;

    public function __construct(PDO $kapcsolat)
    {
        $this->kapcsolat = $kapcsolat;
    }

    public function osszesLekerese(): array
    {
        $stmt = $this->kapcsolat->query($this->alapLekerdezes());
        $sorok = $stmt->fetchAll();

        $eszkozok = [];

        foreach ($sorok as $sor) {
            $eszkozok[] = $this->sorbolEszkoz($sor);
        }

        return $eszkozok;
    }

    public function lekeresIdAlapjan(int $id): ?Eszkoz
    {
        $sql = $this->alapLekerdezes() . ' WHERE e.id = :id';

        $stmt = $this->kapcsolat->prepare($sql);
        $stmt->execute(['id' => $id]);

        $sor = $stmt->fetch();

        if ($sor === false) {
            return null;
        }

        return $this->sorbolEszkoz($sor);
    }

    public function letrehoz(Eszkoz $eszkoz): int
    {
        try {
            $this->kapcsolat->beginTransaction();

            $stmt = $this->kapcsolat->prepare(
                'INSERT INTO eszkoz (megnevezes, eszkoztipus_id, allapot, termekszam, garancia_kezdete, garancia_vege, helyiseg_id)
                 VALUES (:megnevezes, :eszkoztipus_id, :allapot, :termekszam, :garancia_kezdete, :garancia_vege, :helyiseg_id)'
            );
            $stmt->execute([
                'megnevezes' => $eszkoz->getMegnevezes(),
                'eszkoztipus_id' => $eszkoz->getEszkoztipusId(),
                'allapot' => (int) $eszkoz->getAllapot(),
                'termekszam' => $eszkoz->getTermekszam(),
                'garancia_kezdete' => $eszkoz->getGaranciaKezdete(),
                'garancia_vege' => $eszkoz->getGaranciaVege(),
                'helyiseg_id' => $eszkoz->getHelyisegId(),
            ]);

            $id = (int) $this->kapcsolat->lastInsertId();
            $eszkoz->setId($id);

            $this->altablaBeszuras($eszkoz);

            $this->kapcsolat->commit();

            return $id;
        } catch (Throwable $hiba) {
            $this->kapcsolat->rollBack();
            throw $hiba;
        }
    }

    public function modosit(Eszkoz $eszkoz): void
    {
        try {
            $this->kapcsolat->beginTransaction();

            $stmt = $this->kapcsolat->prepare(
                'UPDATE eszkoz SET megnevezes = :megnevezes, eszkoztipus_id = :eszkoztipus_id, allapot = :allapot,
                        termekszam = :termekszam, garancia_kezdete = :garancia_kezdete, garancia_vege = :garancia_vege,
                        helyiseg_id = :helyiseg_id
                 WHERE id = :id'
            );
            $stmt->execute([
                'megnevezes' => $eszkoz->getMegnevezes(),
                'eszkoztipus_id' => $eszkoz->getEszkoztipusId(),
                'allapot' => (int) $eszkoz->getAllapot(),
                'termekszam' => $eszkoz->getTermekszam(),
                'garancia_kezdete' => $eszkoz->getGaranciaKezdete(),
                'garancia_vege' => $eszkoz->getGaranciaVege(),
                'helyiseg_id' => $eszkoz->getHelyisegId(),
                'id' => $eszkoz->getId(),
            ]);

            $this->altablaModositas($eszkoz);

            $this->kapcsolat->commit();
        } catch (Throwable $hiba) {
            $this->kapcsolat->rollBack();
            throw $hiba;
        }
    }

    public function torol(int $id): bool
    {
        try {
            $this->kapcsolat->beginTransaction();

            foreach (['lampa', 'termosztat', 'dugalj'] as $altabla) {
                $stmt = $this->kapcsolat->prepare("DELETE FROM {$altabla} WHERE eszkoz_id = :id");
                $stmt->execute(['id' => $id]);
            }

            $stmt = $this->kapcsolat->prepare('DELETE FROM eszkoz WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $toroltDb = $stmt->rowCount();

            $this->kapcsolat->commit();

            return $toroltDb > 0;
        } catch (Throwable $hiba) {
            $this->kapcsolat->rollBack();
            throw $hiba;
        }
    }

    private function altablaBeszuras(Eszkoz $eszkoz): void
    {
        if ($eszkoz instanceof Lampa) {
            $stmt = $this->kapcsolat->prepare('INSERT INTO lampa (eszkoz_id, fenyero) VALUES (:eszkoz_id, :fenyero)');
            $stmt->execute(['eszkoz_id' => $eszkoz->getId(), 'fenyero' => $eszkoz->getFenyero()]);
            return;
        }

        if ($eszkoz instanceof Termosztat) {
            $stmt = $this->kapcsolat->prepare(
                'INSERT INTO termosztat (eszkoz_id, celhomerseklet, aktualis_homerseklet, uzemmod)
                 VALUES (:eszkoz_id, :celhomerseklet, :aktualis_homerseklet, :uzemmod)'
            );
            $stmt->execute([
                'eszkoz_id' => $eszkoz->getId(),
                'celhomerseklet' => $eszkoz->getCelhomerseklet(),
                'aktualis_homerseklet' => $eszkoz->getAktualisHomerseklet(),
                'uzemmod' => $eszkoz->getUzemmod(),
            ]);
            return;
        }

        if ($eszkoz instanceof Dugalj) {
            $stmt = $this->kapcsolat->prepare('INSERT INTO dugalj (eszkoz_id, aktualis_fogyasztas) VALUES (:eszkoz_id, :aktualis_fogyasztas)');
            $stmt->execute(['eszkoz_id' => $eszkoz->getId(), 'aktualis_fogyasztas' => $eszkoz->getAktualisFogyasztas()]);
            return;
        }

        throw new RuntimeException('Ismeretlen eszköztípus beszúráskor.');
    }

    private function altablaModositas(Eszkoz $eszkoz): void
    {
        if ($eszkoz instanceof Lampa) {
            $stmt = $this->kapcsolat->prepare('UPDATE lampa SET fenyero = :fenyero WHERE eszkoz_id = :eszkoz_id');
            $stmt->execute(['fenyero' => $eszkoz->getFenyero(), 'eszkoz_id' => $eszkoz->getId()]);
            return;
        }

        if ($eszkoz instanceof Termosztat) {
            $stmt = $this->kapcsolat->prepare(
                'UPDATE termosztat SET celhomerseklet = :celhomerseklet, aktualis_homerseklet = :aktualis_homerseklet, uzemmod = :uzemmod
                 WHERE eszkoz_id = :eszkoz_id'
            );
            $stmt->execute([
                'celhomerseklet' => $eszkoz->getCelhomerseklet(),
                'aktualis_homerseklet' => $eszkoz->getAktualisHomerseklet(),
                'uzemmod' => $eszkoz->getUzemmod(),
                'eszkoz_id' => $eszkoz->getId(),
            ]);
            return;
        }

        if ($eszkoz instanceof Dugalj) {
            $stmt = $this->kapcsolat->prepare('UPDATE dugalj SET aktualis_fogyasztas = :aktualis_fogyasztas WHERE eszkoz_id = :eszkoz_id');
            $stmt->execute(['aktualis_fogyasztas' => $eszkoz->getAktualisFogyasztas(), 'eszkoz_id' => $eszkoz->getId()]);
            return;
        }

        throw new RuntimeException('Ismeretlen eszköztípus módosításkor.');
    }

    private function alapLekerdezes(): string
    {
        return 'SELECT e.id, e.megnevezes, e.eszkoztipus_id, e.allapot,
                       e.termekszam, e.garancia_kezdete, e.garancia_vege, e.helyiseg_id,
                       t.megnevezes AS tipus_nev,
                       l.fenyero,
                       tm.celhomerseklet, tm.aktualis_homerseklet, tm.uzemmod,
                       d.aktualis_fogyasztas
                FROM eszkoz e
                JOIN eszkoztipus t ON e.eszkoztipus_id = t.id
                LEFT JOIN lampa l ON l.eszkoz_id = e.id
                LEFT JOIN termosztat tm ON tm.eszkoz_id = e.id
                LEFT JOIN dugalj d ON d.eszkoz_id = e.id';
    }

    private function sorbolEszkoz(array $sor): Eszkoz
    {
        $megnevezes = $sor['megnevezes'];
        $eszkoztipusId = (int) $sor['eszkoztipus_id'];
        $allapot = (bool) $sor['allapot'];
        $termekszam = $sor['termekszam'];
        $garanciaKezdete = $sor['garancia_kezdete'];
        $garanciaVege = $sor['garancia_vege'];
        $helyisegId = $sor['helyiseg_id'] !== null ? (int) $sor['helyiseg_id'] : null;
        $id = (int) $sor['id'];

        switch ($sor['tipus_nev']) {
            case 'lampa':
                return new Lampa(
                    $megnevezes,
                    $eszkoztipusId,
                    $allapot,
                    $sor['fenyero'] !== null ? (int) $sor['fenyero'] : null,
                    $termekszam,
                    $garanciaKezdete,
                    $garanciaVege,
                    $helyisegId,
                    $id
                );
            case 'termosztat':
                return new Termosztat(
                    $megnevezes,
                    $eszkoztipusId,
                    $allapot,
                    $sor['celhomerseklet'] !== null ? (float) $sor['celhomerseklet'] : null,
                    $sor['aktualis_homerseklet'] !== null ? (float) $sor['aktualis_homerseklet'] : null,
                    $sor['uzemmod'],
                    $termekszam,
                    $garanciaKezdete,
                    $garanciaVege,
                    $helyisegId,
                    $id
                );
            case 'dugalj':
                return new Dugalj(
                    $megnevezes,
                    $eszkoztipusId,
                    $allapot,
                    $sor['aktualis_fogyasztas'] !== null ? (float) $sor['aktualis_fogyasztas'] : null,
                    $termekszam,
                    $garanciaKezdete,
                    $garanciaVege,
                    $helyisegId,
                    $id
                );
            default:
                throw new RuntimeException("Ismeretlen eszköztípus: {$sor['tipus_nev']}");
        }
    }
}