<?php

require_once __DIR__ . '/../modells/Meres.php';

class MeresRepository
{
    private PDO $kapcsolat;

    public function __construct(PDO $kapcsolat)
    {
        $this->kapcsolat = $kapcsolat;
    }

    public function osszesLekerese(): array
    {
        $stmt = $this->kapcsolat->query(
            'SELECT id, eszkoz_id, idobelyeg, meres_tipusa, ertek FROM meres ORDER BY idobelyeg DESC'
        );
        $sorok = $stmt->fetchAll();

        $meresek = [];

        foreach ($sorok as $sor) {
            $meresek[] = $this->sorbolMeres($sor);
        }

        return $meresek;
    }

    public function lekeresEszkozAlapjan(int $eszkozId): array
    {
        $stmt = $this->kapcsolat->prepare(
            'SELECT id, eszkoz_id, idobelyeg, meres_tipusa, ertek FROM meres WHERE eszkoz_id = :eszkoz_id ORDER BY idobelyeg DESC'
        );
        $stmt->execute(['eszkoz_id' => $eszkozId]);

        $meresek = [];

        foreach ($stmt->fetchAll() as $sor) {
            $meresek[] = $this->sorbolMeres($sor);
        }

        return $meresek;
    }

    public function letrehoz(Meres $meres): int
    {
        $stmt = $this->kapcsolat->prepare(
            'INSERT INTO meres (eszkoz_id, idobelyeg, meres_tipusa, ertek)
             VALUES (:eszkoz_id, COALESCE(:idobelyeg, NOW()), :meres_tipusa, :ertek)'
        );
        $stmt->execute([
            'eszkoz_id' => $meres->getEszkozId(),
            'idobelyeg' => $meres->getIdobelyeg(),
            'meres_tipusa' => $meres->getMeresTipusa(),
            'ertek' => $meres->getErtek(),
        ]);

        $id = (int) $this->kapcsolat->lastInsertId();
        $meres->setId($id);

        return $id;
    }

    /**
     * Az adott napon, helyiségenként összegzett fogyasztás.
     */
    public function napiFogyasztasHelyisegenkent(string $datum): array
    {
        $sql = "SELECT h.id AS helyiseg_id, h.megnevezes AS helyiseg_nev,
                       SUM(m.ertek) AS osszes_fogyasztas
                FROM meres m
                JOIN eszkoz e ON e.id = m.eszkoz_id
                JOIN helyiseg h ON h.id = e.helyiseg_id
                WHERE m.meres_tipusa = 'fogyasztas' AND DATE(m.idobelyeg) = :datum
                GROUP BY h.id, h.megnevezes
                ORDER BY osszes_fogyasztas DESC";

        $stmt = $this->kapcsolat->prepare($sql);
        $stmt->execute(['datum' => $datum]);

        return $stmt->fetchAll();
    }

    /**
     * Egy eszköz adott napi óránkénti átlaghőmérséklete.
     */
    public function oraiAtlagHomerseklet(int $eszkozId, string $datum): array
    {
        $sql = "SELECT HOUR(idobelyeg) AS ora, AVG(ertek) AS atlag_homerseklet
                FROM meres
                WHERE eszkoz_id = :eszkoz_id AND meres_tipusa = 'homerseklet' AND DATE(idobelyeg) = :datum
                GROUP BY HOUR(idobelyeg)
                ORDER BY ora";

        $stmt = $this->kapcsolat->prepare($sql);
        $stmt->execute(['eszkoz_id' => $eszkozId, 'datum' => $datum]);

        return $stmt->fetchAll();
    }

    /**
     * Az utolsó X nap legtöbbet fogyasztó eszközei.
     */
    public function topFogyasztoEszkozok(int $napokSzama, int $limit): array
    {
        $sql = "SELECT e.id AS eszkoz_id, e.megnevezes AS eszkoz_nev,
                       SUM(m.ertek) AS osszes_fogyasztas
                FROM meres m
                JOIN eszkoz e ON e.id = m.eszkoz_id
                WHERE m.meres_tipusa = 'fogyasztas' AND m.idobelyeg >= DATE_SUB(NOW(), INTERVAL :napok DAY)
                GROUP BY e.id, e.megnevezes
                ORDER BY osszes_fogyasztas DESC
                LIMIT :limit";

        $stmt = $this->kapcsolat->prepare($sql);
        $stmt->bindValue('napok', $napokSzama, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Azok a helyiségek, ahol az átlagfogyasztás meghaladja a küszöböt (HAVING).
     */
    public function magasAtlagfogyasztasuHelyisegek(float $kuszob): array
    {
        $sql = "SELECT h.id AS helyiseg_id, h.megnevezes AS helyiseg_nev,
                       AVG(m.ertek) AS atlag_fogyasztas
                FROM meres m
                JOIN eszkoz e ON e.id = m.eszkoz_id
                JOIN helyiseg h ON h.id = e.helyiseg_id
                WHERE m.meres_tipusa = 'fogyasztas'
                GROUP BY h.id, h.megnevezes
                HAVING AVG(m.ertek) > :kuszob
                ORDER BY atlag_fogyasztas DESC";

        $stmt = $this->kapcsolat->prepare($sql);
        $stmt->execute(['kuszob' => $kuszob]);

        return $stmt->fetchAll();
    }

    private function sorbolMeres(array $sor): Meres
    {
        return new Meres(
            (int) $sor['eszkoz_id'],
            $sor['meres_tipusa'],
            (float) $sor['ertek'],
            $sor['idobelyeg'],
            (int) $sor['id']
        );
    }
}
