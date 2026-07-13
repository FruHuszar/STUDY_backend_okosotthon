<?php

require_once __DIR__ . '/Eszkoz.php';

class Termosztat extends Eszkoz
{
    private ?float $celhomerseklet;
    private ?float $aktualisHomerseklet;
    private ?string $uzemmod;

    public function __construct(
        string $megnevezes,
        int $eszkoztipusId,
        bool $allapot = false,
        ?float $celhomerseklet = null,
        ?float $aktualisHomerseklet = null,
        ?string $uzemmod = null,
        ?string $termekszam = null,
        ?string $garanciaKezdete = null,
        ?string $garanciaVege = null,
        ?int $helyisegId = null,
        ?int $id = null
    ) {
        parent::__construct($megnevezes, $eszkoztipusId, $allapot, $termekszam, $garanciaKezdete, $garanciaVege, $helyisegId, $id);
        $this->celhomerseklet = $celhomerseklet;
        $this->aktualisHomerseklet = $aktualisHomerseklet;
        $this->uzemmod = $uzemmod;
    }

    public function getCelhomerseklet(): ?float { return $this->celhomerseklet; }
    public function setCelhomerseklet(?float $celhomerseklet): void { $this->celhomerseklet = $celhomerseklet; }

    public function getAktualisHomerseklet(): ?float { return $this->aktualisHomerseklet; }
    public function setAktualisHomerseklet(?float $aktualisHomerseklet): void { $this->aktualisHomerseklet = $aktualisHomerseklet; }

    public function getUzemmod(): ?string { return $this->uzemmod; }
    public function setUzemmod(?string $uzemmod): void { $this->uzemmod = $uzemmod; }
}