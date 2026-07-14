<?php

class Meres
{
    private ?int $id;
    private int $eszkozId;
    private ?string $idobelyeg;
    private string $meresTipusa;
    private float $ertek;

    public function __construct(
        int $eszkozId,
        string $meresTipusa,
        float $ertek,
        ?string $idobelyeg = null,
        ?int $id = null
    ) {
        $this->eszkozId = $eszkozId;
        $this->meresTipusa = $meresTipusa;
        $this->ertek = $ertek;
        $this->idobelyeg = $idobelyeg;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getEszkozId(): int { return $this->eszkozId; }
    public function setEszkozId(int $eszkozId): void { $this->eszkozId = $eszkozId; }

    public function getIdobelyeg(): ?string { return $this->idobelyeg; }
    public function setIdobelyeg(?string $idobelyeg): void { $this->idobelyeg = $idobelyeg; }

    public function getMeresTipusa(): string { return $this->meresTipusa; }
    public function setMeresTipusa(string $meresTipusa): void { $this->meresTipusa = $meresTipusa; }

    public function getErtek(): float { return $this->ertek; }
    public function setErtek(float $ertek): void { $this->ertek = $ertek; }
}
