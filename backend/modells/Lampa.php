<?php

//__DIR__ jelentése: Mágikus konstans, a PHP futás közben helyettesíti be az értékét. Konkrétan: annak a fájlnak a mappája, amiben a __DIR__ szó áll, teljes (abszolút) útvonalként, záró perjel nélkül. Van pár testvére is, ugyanez az elv: __FILE__ (a fájl teljes útvonala névvel együtt), __LINE__ (hányadik sor), __CLASS__ (az osztály neve). Ezek hibakeresésnél is hasznosak.
require_once __DIR__ . '/Eszkoz.php';

class Lampa extends Eszkoz {
    private int $fenyero;

    // Nem kell az #[Override], sőt tilos mertmert: A PHP a konstruktort nem tekinti „felülírható" metódusnak (hisz eltérő paraméterszámok). Mi itt nem felülírunk, hanem sajátot definiálunk.
    public function __construct(string $megnevezes, int $eszkoztipusId, bool $allapot = false, ?int $helyisegId = null, ?int $id = null, int $fenyero = 0)
    {
        return parent::__construct($megnevezes, $eszkoztipusId, $allapot, $helyisegId, $id);
        $this->fenyero = $fenyero;
    }


    public function getFenyero(): int { return $this->fenyero; }
    public function setFenyero(int $fenyero): void { $this->fenyero = $fenyero; }
}