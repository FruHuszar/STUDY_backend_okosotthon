<?php

require_once __DIR__ . '/../modells/Helyiseg.php';

class HelyisegRepository
{
    private PDO $kapcsolat;

    public function __construct(PDO $kapcsolat)
    {
        $this->kapcsolat = $kapcsolat;
    }

    public function osszesLekerese(): array
    {
        $stmt = $this->kapcsolat->query('SELECT id, megnevezes, terulet, felhasznalo_id FROM helyiseg');
        $sorok = $stmt->fetchAll();

        $helyisegek = [];

        foreach ($sorok as $sor) {
            $helyisegek[] = $this->sorbolHelyiseg($sor);
        }

        return $helyisegek;
    }

    public function lekeresIdAlapjan(int $id): ?Helyiseg
    {
        $stmt = $this->kapcsolat->prepare('SELECT id, megnevezes, terulet, felhasznalo_id FROM helyiseg WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $sor = $stmt->fetch();

        if ($sor === false) {
            return null;
        }

        return $this->sorbolHelyiseg($sor);
    }

    public function letrehoz(Helyiseg $helyiseg): int
    {
        $stmt = $this->kapcsolat->prepare(
            'INSERT INTO helyiseg (megnevezes, terulet, felhasznalo_id) VALUES (:megnevezes, :terulet, :felhasznalo_id)'
        );
        $stmt->execute([
            'megnevezes' => $helyiseg->getMegnevezes(),
            'terulet' => $helyiseg->getTerulet(),
            'felhasznalo_id' => $helyiseg->getFelhasznaloId(),
        ]);

        $id = (int) $this->kapcsolat->lastInsertId();
        $helyiseg->setId($id);

        return $id;
    }

    public function modosit(Helyiseg $helyiseg): void
    {
        $stmt = $this->kapcsolat->prepare(
            'UPDATE helyiseg SET megnevezes = :megnevezes, terulet = :terulet, felhasznalo_id = :felhasznalo_id WHERE id = :id'
        );
        $stmt->execute([
            'megnevezes' => $helyiseg->getMegnevezes(),
            'terulet' => $helyiseg->getTerulet(),
            'felhasznalo_id' => $helyiseg->getFelhasznaloId(),
            'id' => $helyiseg->getId(),
        ]);
    }

    public function torol(int $id): bool
    {
        $stmt = $this->kapcsolat->prepare('DELETE FROM helyiseg WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function sorbolHelyiseg(array $sor): Helyiseg
    {
        return new Helyiseg(
            $sor['megnevezes'],
            (int) $sor['felhasznalo_id'],
            $sor['terulet'] !== null ? (float) $sor['terulet'] : null,
            (int) $sor['id']
        );
    }
}
