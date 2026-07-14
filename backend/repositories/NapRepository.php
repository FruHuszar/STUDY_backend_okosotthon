<?php

require_once __DIR__ . '/../modells/Nap.php';

class NapRepository
{
    private PDO $kapcsolat;

    public function __construct(PDO $kapcsolat)
    {
        $this->kapcsolat = $kapcsolat;
    }

    public function osszesLekerese(): array
    {
        $stmt = $this->kapcsolat->query('SELECT id, nev FROM nap ORDER BY id');
        $sorok = $stmt->fetchAll();

        $napok = [];

        foreach ($sorok as $sor) {
            $napok[] = $this->sorbolNap($sor);
        }

        return $napok;
    }

    public function lekeresIdAlapjan(int $id): ?Nap
    {
        $stmt = $this->kapcsolat->prepare('SELECT id, nev FROM nap WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $sor = $stmt->fetch();

        if ($sor === false) {
            return null;
        }

        return $this->sorbolNap($sor);
    }

    private function sorbolNap(array $sor): Nap
    {
        return new Nap($sor['nev'], (int) $sor['id']);
    }
}
