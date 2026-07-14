<?php

class Utemezes
{
    private ?int $id;
    private int $eszkozId;
    private ?string $kezdoIdo;
    private ?string $zaroIdo;
    private ?bool $celAllapot;
    private ?float $celErtek;
    private ?string $letrehozvaTimestamp;
    private ?string $modositvaTimestamp;
    private array $napok;

    public function __construct(
        int $eszkozId,
        ?string $kezdoIdo = null,
        ?string $zaroIdo = null,
        ?bool $celAllapot = null,
        ?float $celErtek = null,
        array $napok = [],
        ?int $id = null,
        ?string $letrehozvaTimestamp = null,
        ?string $modositvaTimestamp = null
    ) {
        $this->eszkozId = $eszkozId;
        $this->kezdoIdo = $kezdoIdo;
        $this->zaroIdo = $zaroIdo;
        $this->celAllapot = $celAllapot;
        $this->celErtek = $celErtek;
        $this->napok = $napok;
        $this->id = $id;
        $this->letrehozvaTimestamp = $letrehozvaTimestamp;
        $this->modositvaTimestamp = $modositvaTimestamp;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getEszkozId(): int { return $this->eszkozId; }
    public function setEszkozId(int $eszkozId): void { $this->eszkozId = $eszkozId; }

    public function getKezdoIdo(): ?string { return $this->kezdoIdo; }
    public function setKezdoIdo(?string $kezdoIdo): void { $this->kezdoIdo = $kezdoIdo; }

    public function getZaroIdo(): ?string { return $this->zaroIdo; }
    public function setZaroIdo(?string $zaroIdo): void { $this->zaroIdo = $zaroIdo; }

    public function getCelAllapot(): ?bool { return $this->celAllapot; }
    public function setCelAllapot(?bool $celAllapot): void { $this->celAllapot = $celAllapot; }

    public function getCelErtek(): ?float { return $this->celErtek; }
    public function setCelErtek(?float $celErtek): void { $this->celErtek = $celErtek; }

    public function getLetrehozvaTimestamp(): ?string { return $this->letrehozvaTimestamp; }
    public function setLetrehozvaTimestamp(?string $letrehozvaTimestamp): void { $this->letrehozvaTimestamp = $letrehozvaTimestamp; }

    public function getModositvaTimestamp(): ?string { return $this->modositvaTimestamp; }
    public function setModositvaTimestamp(?string $modositvaTimestamp): void { $this->modositvaTimestamp = $modositvaTimestamp; }

    public function getNapok(): array { return $this->napok; }
    public function setNapok(array $napok): void { $this->napok = $napok; }
}
