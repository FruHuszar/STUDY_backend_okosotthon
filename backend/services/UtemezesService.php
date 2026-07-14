<?php

require_once __DIR__ . '/../repositories/UtemezesRepository.php';
require_once __DIR__ . '/../exceptions/ErvenytelenAdatException.php';
require_once __DIR__ . '/../exceptions/NemTalalhatoException.php';

class UtemezesService
{
    private UtemezesRepository $repository;

    public function __construct(UtemezesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function osszesUtemezes(): array
    {
        return $this->repository->osszesLekerese();
    }

    public function egyUtemezes(int $id): Utemezes
    {
        $utemezes = $this->repository->lekeresIdAlapjan($id);

        if ($utemezes === null) {
            throw new NemTalalhatoException("Nincs ütemezés ezzel az azonosítóval: {$id}");
        }

        return $utemezes;
    }

    public function eszkozUtemezesei(int $eszkozId): array
    {
        return $this->repository->lekeresEszkozAlapjan($eszkozId);
    }

    public function letrehoz(Utemezes $utemezes): int
    {
        $this->ellenoriz($utemezes);

        return $this->repository->letrehoz($utemezes);
    }

    public function modosit(Utemezes $utemezes): Utemezes
    {
        if ($utemezes->getId() === null) {
            throw new ErvenytelenAdatException('Módosításhoz kötelező az azonosító.');
        }

        $this->egyUtemezes($utemezes->getId());
        $this->ellenoriz($utemezes);

        $this->repository->modosit($utemezes);

        return $utemezes;
    }

    public function torol(int $id): void
    {
        $this->egyUtemezes($id);

        $this->repository->torol($id);
    }

    /**
     * A modositvaTimestamp-ból képzett ETag – ez a 304-mechanizmus alapja.
     */
    public function etagKeszites(Utemezes $utemezes): string
    {
        return '"' . md5($utemezes->getId() . '|' . $utemezes->getModositvaTimestamp()) . '"';
    }

    private function ellenoriz(Utemezes $utemezes): void
    {
        if ($utemezes->getEszkozId() <= 0) {
            throw new ErvenytelenAdatException('Érvénytelen eszköz azonosító.');
        }

        if ($utemezes->getKezdoIdo() !== null && $utemezes->getZaroIdo() !== null
            && $utemezes->getKezdoIdo() >= $utemezes->getZaroIdo()) {
            throw new ErvenytelenAdatException('A kezdő időnek korábbinak kell lennie a záró időnél.');
        }

        if ($utemezes->getCelErtek() !== null && $utemezes->getCelErtek() < 0) {
            throw new ErvenytelenAdatException('A cél érték nem lehet negatív.');
        }

        if ($utemezes->getCelAllapot() === null && $utemezes->getCelErtek() === null) {
            throw new ErvenytelenAdatException('Legalább a cél állapotot vagy a cél értéket meg kell adni.');
        }
    }
}
