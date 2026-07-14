<?php

require_once __DIR__ . '/../repositories/FelhasznaloRepository.php';
require_once __DIR__ . '/../exceptions/ErvenytelenAdatException.php';
require_once __DIR__ . '/../exceptions/NemTalalhatoException.php';

class FelhasznaloService
{
    private FelhasznaloRepository $repository;

    public function __construct(FelhasznaloRepository $repository)
    {
        $this->repository = $repository;
    }

    public function osszesFelhasznalo(): array
    {
        return $this->repository->osszesLekerese();
    }

    public function egyFelhasznalo(int $id): Felhasznalo
    {
        $felhasznalo = $this->repository->lekeresIdAlapjan($id);

        if ($felhasznalo === null) {
            throw new NemTalalhatoException("Nincs felhasználó ezzel az azonosítóval: {$id}");
        }

        return $felhasznalo;
    }

    public function letrehoz(Felhasznalo $felhasznalo): int
    {
        $this->ellenoriz($felhasznalo);

        return $this->repository->letrehoz($felhasznalo);
    }

    public function modosit(Felhasznalo $felhasznalo): Felhasznalo
    {
        if ($felhasznalo->getId() === null) {
            throw new ErvenytelenAdatException('Módosításhoz kötelező az azonosító.');
        }

        $this->egyFelhasznalo($felhasznalo->getId());
        $this->ellenoriz($felhasznalo, $felhasznalo->getId());

        $this->repository->modosit($felhasznalo);

        return $felhasznalo;
    }

    public function torol(int $id): void
    {
        $this->egyFelhasznalo($id);

        $this->repository->torol($id);
    }

    private function ellenoriz(Felhasznalo $felhasznalo, ?int $sajatId = null): void
    {
        if (trim($felhasznalo->getNev()) === '') {
            throw new ErvenytelenAdatException('A név nem lehet üres.');
        }

        if (trim($felhasznalo->getJelszo()) === '') {
            throw new ErvenytelenAdatException('A jelszó nem lehet üres.');
        }

        if (!filter_var($felhasznalo->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new ErvenytelenAdatException('Érvénytelen email formátum.');
        }

        $letezo = $this->repository->lekeresEmailAlapjan($felhasznalo->getEmail());

        if ($letezo !== null && $letezo->getId() !== $sajatId) {
            throw new ErvenytelenAdatException('Ez az email cím már foglalt.');
        }
    }
}
