<?php

require_once __DIR__ . '/../repositories/NapRepository.php';

class NapController
{
    private NapRepository $repository;

    public function __construct(NapRepository $repository)
    {
        $this->repository = $repository;
    }

    public function osszes(): void
    {
        try {
            $napok = $this->repository->osszesLekerese();
            $this->valasz(200, array_map([$this, 'napTombbe'], $napok));
        } catch (Throwable $hiba) {
            $this->valasz(500, ['hiba' => 'Váratlan szerverhiba történt: ' . $hiba->getMessage()]);
        }
    }

    private function valasz(int $statuszkod, mixed $adat): void
    {
        http_response_code($statuszkod);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($adat, JSON_UNESCAPED_UNICODE);
    }

    private function napTombbe(Nap $nap): array
    {
        return [
            'id' => $nap->getId(),
            'nev' => $nap->getNev(),
        ];
    }
}
