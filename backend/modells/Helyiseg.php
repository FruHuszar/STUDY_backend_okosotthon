<?php

class Helyiseg
{
    private ?int $id;
    private string $megnevezes;
    private ?float $terulet;
    private int $felhasznaloId;

    public function __construct(
        string $megnevezes,
        int $felhasznaloId,
        ?float $terulet = null,
        ?int $id = null
    ) {
        $this->megnevezes = $megnevezes;
        $this->felhasznaloId = $felhasznaloId;
        $this->terulet = $terulet;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getMegnevezes(): string { return $this->megnevezes; }
    public function setMegnevezes(string $megnevezes): void { $this->megnevezes = $megnevezes; }

    public function getTerulet(): ?float { return $this->terulet; }
    public function setTerulet(?float $terulet): void { $this->terulet = $terulet; }

    public function getFelhasznaloId(): int { return $this->felhasznaloId; }
    public function setFelhasznaloId(int $felhasznaloId): void { $this->felhasznaloId = $felhasznaloId; }
}
