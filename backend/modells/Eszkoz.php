<?php

abstract class Eszkoz { //Az absztrakt osztly nem példányosítható. Olyan helyzetekre jó mint most: ez egy közös ős, de önmagában nem fogjuk példányosítani, és ne is lehessen

    //protected jelentése: egy láthatósági szint. A különbség a private-al szemben: Az adott osztály ÉS a leszármazottjai is hozzáférnek a protectedben.
    protected ?int $id;
    protected string $megnevezes;
    protected int $eszkoztipusId;
    protected bool $allapot;
    protected ?string $termekszam;
    protected ?string $garanciaKezdete;
    protected ?string $garanciaVege;
    protected ?int $helyisegId;

    public function __construct(
        string $megnevezes,
        int $eszkoztipusId,
        bool $allapot = false,
        ?string $termekszam = null,
        ?string $garanciaKezdete = null,
        ?string $garanciaVege = null,
        ?int $helyisegId = null,
        ?int $id = null
    )
    {
         $this->megnevezes = $megnevezes;
        $this->eszkoztipusId = $eszkoztipusId;
        $this->allapot = $allapot;
        $this->termekszam = $termekszam;
        $this->garanciaKezdete = $garanciaKezdete;
        $this->garanciaVege = $garanciaVege;
        $this->helyisegId = $helyisegId;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getMegnevezes(): string { return $this->megnevezes; }
    public function setMegnevezes(string $megnevezes): void { $this->megnevezes = $megnevezes; }

    public function getEszkoztipusId(): int { return $this->eszkoztipusId; }
    public function setEszkoztipusId(int $eszkoztipusId): void { $this->eszkoztipusId = $eszkoztipusId; }

    public function getAllapot(): bool { return $this->allapot; }
    public function setAllapot(bool $allapot): void { $this->allapot = $allapot; }

    public function getTermekszam(): ?string { return $this->termekszam; }
    public function setTermekszam(?string $termekszam): void { $this->termekszam = $termekszam; }

    public function getGaranciaKezdete(): ?string { return $this->garanciaKezdete; }
    public function setGaranciaKezdete(?string $garanciaKezdete): void { $this->garanciaKezdete = $garanciaKezdete; }

    public function getGaranciaVege(): ?string { return $this->garanciaVege; }
    public function setGaranciaVege(?string $garanciaVege): void { $this->garanciaVege = $garanciaVege; }

    public function getHelyisegId(): ?int { return $this->helyisegId; }
    public function setHelyisegId(?int $helyisegId): void { $this->helyisegId = $helyisegId; }
}