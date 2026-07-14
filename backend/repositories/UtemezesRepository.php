<?php

require_once __DIR__ . '/../modells/Utemezes.php';

class UtemezesRepository
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

        $utemezesek = [];

        foreach ($sorok as $sor) {
            $utemezes = $this->sorbolUtemezes($sor);
            $utemezes->setNapok($this->napokBetoltese($utemezes->getId()));
            $utemezesek[] = $utemezes;
        }

        return $utemezesek;
    }

    public function lekeresIdAlapjan(int $id): ?Utemezes
    {
        $sql = $this->alapLekerdezes() . ' WHERE id = :id';

        $stmt = $this->kapcsolat->prepare($sql);
        $stmt->execute(['id' => $id]);

        $sor = $stmt->fetch();

        if ($sor === false) {
            return null;
        }

        $utemezes = $this->sorbolUtemezes($sor);
        $utemezes->setNapok($this->napokBetoltese($utemezes->getId()));

        return $utemezes;
    }

    public function lekeresEszkozAlapjan(int $eszkozId): array
    {
        $sql = $this->alapLekerdezes() . ' WHERE eszkoz_id = :eszkoz_id';

        $stmt = $this->kapcsolat->prepare($sql);
        $stmt->execute(['eszkoz_id' => $eszkozId]);

        $utemezesek = [];

        foreach ($stmt->fetchAll() as $sor) {
            $utemezes = $this->sorbolUtemezes($sor);
            $utemezes->setNapok($this->napokBetoltese($utemezes->getId()));
            $utemezesek[] = $utemezes;
        }

        return $utemezesek;
    }

    public function letrehoz(Utemezes $utemezes): int
    {
        try {
            $this->kapcsolat->beginTransaction();

            $stmt = $this->kapcsolat->prepare(
                'INSERT INTO utemezes (eszkoz_id, kezdo_ido, zaro_ido, cel_allapot, cel_ertek, letrehozva_timestamp, modositva_timestamp)
                 VALUES (:eszkoz_id, :kezdo_ido, :zaro_ido, :cel_allapot, :cel_ertek, NOW(), NOW())'
            );
            $stmt->execute([
                'eszkoz_id' => $utemezes->getEszkozId(),
                'kezdo_ido' => $utemezes->getKezdoIdo(),
                'zaro_ido' => $utemezes->getZaroIdo(),
                'cel_allapot' => $utemezes->getCelAllapot() === null ? null : (int) $utemezes->getCelAllapot(),
                'cel_ertek' => $utemezes->getCelErtek(),
            ]);

            $id = (int) $this->kapcsolat->lastInsertId();
            $utemezes->setId($id);

            $this->napokMentese($id, $utemezes->getNapok());

            $this->kapcsolat->commit();

            return $id;
        } catch (Throwable $hiba) {
            $this->kapcsolat->rollBack();
            throw $hiba;
        }
    }

    public function modosit(Utemezes $utemezes): void
    {
        try {
            $this->kapcsolat->beginTransaction();

            $stmt = $this->kapcsolat->prepare(
                'UPDATE utemezes SET eszkoz_id = :eszkoz_id, kezdo_ido = :kezdo_ido, zaro_ido = :zaro_ido,
                        cel_allapot = :cel_allapot, cel_ertek = :cel_ertek, modositva_timestamp = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'eszkoz_id' => $utemezes->getEszkozId(),
                'kezdo_ido' => $utemezes->getKezdoIdo(),
                'zaro_ido' => $utemezes->getZaroIdo(),
                'cel_allapot' => $utemezes->getCelAllapot() === null ? null : (int) $utemezes->getCelAllapot(),
                'cel_ertek' => $utemezes->getCelErtek(),
                'id' => $utemezes->getId(),
            ]);

            $torlo = $this->kapcsolat->prepare('DELETE FROM utemezes_nap WHERE utemezes_id = :utemezes_id');
            $torlo->execute(['utemezes_id' => $utemezes->getId()]);

            $this->napokMentese($utemezes->getId(), $utemezes->getNapok());

            $frissitett = $this->lekeresIdAlapjan($utemezes->getId());
            $utemezes->setModositvaTimestamp($frissitett?->getModositvaTimestamp());

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

            $stmt = $this->kapcsolat->prepare('DELETE FROM utemezes_nap WHERE utemezes_id = :id');
            $stmt->execute(['id' => $id]);

            $stmt = $this->kapcsolat->prepare('DELETE FROM utemezes WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $toroltDb = $stmt->rowCount();

            $this->kapcsolat->commit();

            return $toroltDb > 0;
        } catch (Throwable $hiba) {
            $this->kapcsolat->rollBack();
            throw $hiba;
        }
    }

    private function napokBetoltese(int $utemezesId): array
    {
        $stmt = $this->kapcsolat->prepare('SELECT nap_id FROM utemezes_nap WHERE utemezes_id = :utemezes_id ORDER BY nap_id');
        $stmt->execute(['utemezes_id' => $utemezesId]);

        return array_map(static fn (array $sor): int => (int) $sor['nap_id'], $stmt->fetchAll());
    }

    private function napokMentese(int $utemezesId, array $napIdk): void
    {
        if ($napIdk === []) {
            return;
        }

        $stmt = $this->kapcsolat->prepare('INSERT INTO utemezes_nap (utemezes_id, nap_id) VALUES (:utemezes_id, :nap_id)');

        foreach ($napIdk as $napId) {
            $stmt->execute(['utemezes_id' => $utemezesId, 'nap_id' => (int) $napId]);
        }
    }

    private function alapLekerdezes(): string
    {
        return 'SELECT id, eszkoz_id, kezdo_ido, zaro_ido, cel_allapot, cel_ertek, letrehozva_timestamp, modositva_timestamp
                FROM utemezes';
    }

    private function sorbolUtemezes(array $sor): Utemezes
    {
        return new Utemezes(
            (int) $sor['eszkoz_id'],
            $sor['kezdo_ido'],
            $sor['zaro_ido'],
            $sor['cel_allapot'] !== null ? (bool) $sor['cel_allapot'] : null,
            $sor['cel_ertek'] !== null ? (float) $sor['cel_ertek'] : null,
            [],
            (int) $sor['id'],
            $sor['letrehozva_timestamp'],
            $sor['modositva_timestamp']
        );
    }
}
