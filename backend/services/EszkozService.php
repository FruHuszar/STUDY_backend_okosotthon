<?php

require_once __DIR__ . '/../repositories/EszkozRepository.php';
require_once __DIR__ . '/../exceptions/ErvenytelenAdatException.php';
require_once __DIR__ . '/../exceptions/NemTalalhatoException.php';

class EszkozService {
    private EszkozRepository $repository;

    public function __construct(EszkozRepository $repository){
        $this->repository = $repository;
    }

    public function osszesEszkoz(): array {
        return $this->repository->osszesLekerese();
    }

    public function egyEszkoz(int $id): Eszkoz {
        $eszkoz = $this->repository->lekeresIdAlapjan($id);

        if ($eszkoz === null){
            throw new NemTalalhatoException("Nincs eszköz ezzel az azonosítóval: {$id}");
        }

        return $eszkoz;
    }

    public function letrehoz(Eszkoz $eszkoz): int {
        $this->ellenoriz($eszkoz);

        return $this->repository->letrehoz($eszkoz);
    }

    public function modosit(Eszkoz $eszkoz): Eszkoz {
        if ($eszkoz->getId() === null) {
            throw new ErvenytelenAdatException('Módosításhoz kötelező az azonosító.');
        }

        $this->egyEszkoz($eszkoz->getId());
        $this->ellenoriz($eszkoz);

        $this->repository->modosit($eszkoz);

        return $eszkoz;
    }

    public function torol(int $id): void {
        $this->egyEszkoz($id);

        $this->repository->torol($id);
    }

    private function ellenoriz(Eszkoz $eszkoz): void {
        if (trim($eszkoz->getMegnevezes()) === ''){
            throw new ErvenytelenAdatException('A megnevezés nem lehet üres.');
        }

        if ($eszkoz instanceof Lampa){
            $this->ellenorizLampa($eszkoz);
            return; //early return: mivel a típusok kizárják egymást, felesleges megnézni a többi ágat
        }

        if($eszkoz instanceof Termosztat) {
            $this->ellenorizTermosztat($eszkoz);
            return; //lehetett volna elseif is,mindegy.
        }
    }

    private function ellenorizLampa(Lampa $lampa): void {
        $fenyero = $lampa->getFenyero();

        if ($fenyero !== null && ($fenyero < 0 || $fenyero > 100)) {
            throw new ErvenytelenAdatException('A fényerő csak 0 és 100 között lehet.');
        }
    }

    private function ellenorizTermosztat(Termosztat $termosztat): void
    {
        $ervenyesUzemmodok = ['futes', 'hutes', 'ki'];

        if (!in_array($termosztat->getUzemmod(), $ervenyesUzemmodok, true)) {
            throw new ErvenytelenAdatException('Az üzemmód csak ezek egyike lehet: futes, hutes, ki.');
        }
    }
}