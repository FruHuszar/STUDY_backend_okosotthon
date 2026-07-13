<?php

require_once __DIR__ . '/Eszkoz.php';

class Dugalj extends Eszkoz
{
    private ?float $aktualisFogyasztas;

    public function __construct(
        string $megnevezes,
        int $eszkoztipusId,
        bool $allapot = false,
        ?float $aktualisFogyasztas = null,
        ?string $termekszam = null,
        ?string $garanciaKezdete = null,
        ?string $garanciaVege = null,
        ?int $helyisegId = null,
        ?int $id = null
    ) {
        parent::__construct($megnevezes, $eszkoztipusId, $allapot, $termekszam, $garanciaKezdete, $garanciaVege, $helyisegId, $id);
        $this->aktualisFogyasztas = $aktualisFogyasztas;
    }

    public function getAktualisFogyasztas(): ?float { return $this->aktualisFogyasztas; }
    public function setAktualisFogyasztas(?float $aktualisFogyasztas): void { $this->aktualisFogyasztas = $aktualisFogyasztas; }
}