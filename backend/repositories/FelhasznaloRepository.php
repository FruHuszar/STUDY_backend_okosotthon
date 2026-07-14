<?php

require_once __DIR__ . '/../modells/Felhasznalo.php';

class FelhasznaloRepository
{
    private PDO $kapcsolat;

    public function __construct(PDO $kapcsolat)
    {
        $this->kapcsolat = $kapcsolat;
    }

    public function osszesLekerese(): array
    {
        $stmt = $this->kapcsolat->query('SELECT id, nev, email, jelszo FROM felhasznalo');
        $sorok = $stmt->fetchAll();

        $felhasznalok = [];

        foreach ($sorok as $sor) {
            $felhasznalok[] = $this->sorbolFelhasznalo($sor);
        }

        return $felhasznalok;
    }

    public function lekeresIdAlapjan(int $id): ?Felhasznalo
    {
        $stmt = $this->kapcsolat->prepare('SELECT id, nev, email, jelszo FROM felhasznalo WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $sor = $stmt->fetch();

        if ($sor === false) {
            return null;
        }

        return $this->sorbolFelhasznalo($sor);
    }

    public function lekeresEmailAlapjan(string $email): ?Felhasznalo
    {
        $stmt = $this->kapcsolat->prepare('SELECT id, nev, email, jelszo FROM felhasznalo WHERE email = :email');
        $stmt->execute(['email' => $email]);

        $sor = $stmt->fetch();

        if ($sor === false) {
            return null;
        }

        return $this->sorbolFelhasznalo($sor);
    }

    public function letrehoz(Felhasznalo $felhasznalo): int
    {
        $stmt = $this->kapcsolat->prepare(
            'INSERT INTO felhasznalo (nev, email, jelszo) VALUES (:nev, :email, :jelszo)'
        );
        $stmt->execute([
            'nev' => $felhasznalo->getNev(),
            'email' => $felhasznalo->getEmail(),
            'jelszo' => $felhasznalo->getJelszo(),
        ]);

        $id = (int) $this->kapcsolat->lastInsertId();
        $felhasznalo->setId($id);

        return $id;
    }

    public function modosit(Felhasznalo $felhasznalo): void
    {
        $stmt = $this->kapcsolat->prepare(
            'UPDATE felhasznalo SET nev = :nev, email = :email, jelszo = :jelszo WHERE id = :id'
        );
        $stmt->execute([
            'nev' => $felhasznalo->getNev(),
            'email' => $felhasznalo->getEmail(),
            'jelszo' => $felhasznalo->getJelszo(),
            'id' => $felhasznalo->getId(),
        ]);
    }

    public function torol(int $id): bool
    {
        $stmt = $this->kapcsolat->prepare('DELETE FROM felhasznalo WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function sorbolFelhasznalo(array $sor): Felhasznalo
    {
        return new Felhasznalo(
            $sor['nev'],
            $sor['email'],
            $sor['jelszo'],
            (int) $sor['id']
        );
    }
}
