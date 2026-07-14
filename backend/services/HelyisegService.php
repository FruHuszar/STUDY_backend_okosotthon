<?php

require_once __DIR__ . '/../repositories/HelyisegRepository.php';
require_once __DIR__ . '/../exceptions/ErvenytelenAdatException.php';
require_once __DIR__ . '/../exceptions/NemTalalhatoException.php';

class HelyisegService
{
    private HelyisegRepository $repository;

    public function __construct(HelyisegRepository $repository)
    {
        $this->repository = $repository;
    }

    public function osszesHelyiseg(): array
    {
        return $this->repository->osszesLekerese();
    }

    public function egyHelyiseg(int $id): Helyiseg
    {
        $helyiseg = $this->repository->lekeresIdAlapjan($id);

        if ($helyiseg === null) {
            throw new NemTalalhatoException("Nincs helyiség ezzel az azonosítóval: {$id}");
        }

        return $helyiseg;
    }

    public function letrehoz(Helyiseg $helyiseg): int
    {
        $this->ellenoriz($helyiseg);

        return $this->repository->letrehoz($helyiseg);
    }

    public function modosit(Helyiseg $helyiseg): Helyiseg
    {
        if ($helyiseg->getId() === null) {
            throw new ErvenytelenAdatException('Módosításhoz kötelező az azonosító.');
        }

        $this->egyHelyiseg($helyiseg->getId());
        $this->ellenoriz($helyiseg);

        $this->repository->modosit($helyiseg);

        return $helyiseg;
    }

    public function torol(int $id): void
    {
        $this->egyHelyiseg($id);

        $this->repository->torol($id);
    }

    private function ellenoriz(Helyiseg $helyiseg): void
    {
        if (trim($helyiseg->getMegnevezes()) === '') {
            throw new ErvenytelenAdatException('A megnevezés nem lehet üres.');
        }

        if ($helyiseg->getTerulet() !== null && $helyiseg->getTerulet() < 0) {
            throw new ErvenytelenAdatException('A terület nem lehet negatív.');
        }
    }
}
