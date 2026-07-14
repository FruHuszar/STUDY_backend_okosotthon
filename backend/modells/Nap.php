<?php

class Nap
{
    private ?int $id;
    private string $nev;

    public function __construct(string $nev, ?int $id = null)
    {
        $this->nev = $nev;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNev(): string { return $this->nev; }
    public function setNev(string $nev): void { $this->nev = $nev; }
}
