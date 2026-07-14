<?php

class Felhasznalo
{
    private ?int $id;
    private string $nev;
    private string $jelszo;
    private string $email;

    public function __construct(
        string $nev,
        string $email,
        string $jelszo,
        ?int $id = null
    ) {
        $this->nev = $nev;
        $this->email = $email;
        $this->jelszo = $jelszo;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNev(): string { return $this->nev; }
    public function setNev(string $nev): void { $this->nev = $nev; }

    public function getJelszo(): string { return $this->jelszo; }
    public function setJelszo(string $jelszo): void { $this->jelszo = $jelszo; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
}
