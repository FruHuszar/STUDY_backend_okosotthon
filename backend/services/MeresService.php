<?php

require_once __DIR__ . '/../repositories/MeresRepository.php';
require_once __DIR__ . '/../exceptions/ErvenytelenAdatException.php';
require_once __DIR__ . '/../exceptions/NemTalalhatoException.php';

class MeresService
{
    private const ERVENYES_TIPUSOK = ['fogyasztas', 'homerseklet'];

    private MeresRepository $repository;

    public function __construct(MeresRepository $repository)
    {
        $this->repository = $repository;
    }

    public function osszesMeres(): array
    {
        return $this->repository->osszesLekerese();
    }

    public function eszkozMeresei(int $eszkozId): array
    {
        return $this->repository->lekeresEszkozAlapjan($eszkozId);
    }

    public function rogzit(Meres $meres): int
    {
        $this->ellenoriz($meres);

        return $this->repository->letrehoz($meres);
    }

    public function napiFogyasztas(string $datum): array
    {
        return $this->repository->napiFogyasztasHelyisegenkent($datum);
    }

    public function oraiHomerseklet(int $eszkozId, string $datum): array
    {
        return $this->repository->oraiAtlagHomerseklet($eszkozId, $datum);
    }

    public function topFogyasztok(int $napokSzama, int $limit): array
    {
        return $this->repository->topFogyasztoEszkozok($napokSzama, $limit);
    }

    public function magasFogyasztasuHelyisegek(float $kuszob): array
    {
        return $this->repository->magasAtlagfogyasztasuHelyisegek($kuszob);
    }

    private function ellenoriz(Meres $meres): void
    {
        if (!in_array($meres->getMeresTipusa(), self::ERVENYES_TIPUSOK, true)) {
            throw new ErvenytelenAdatException('A mérés típusa csak ezek egyike lehet: ' . implode(', ', self::ERVENYES_TIPUSOK) . '.');
        }

        if ($meres->getMeresTipusa() === 'fogyasztas' && $meres->getErtek() < 0) {
            throw new ErvenytelenAdatException('A fogyasztás értéke nem lehet negatív.');
        }

        if ($meres->getMeresTipusa() === 'homerseklet' && ($meres->getErtek() < -50 || $meres->getErtek() > 100)) {
            throw new ErvenytelenAdatException('A hőmérséklet értéke nem reális tartományban van.');
        }
    }
}
