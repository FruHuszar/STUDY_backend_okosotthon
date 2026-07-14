<?php

require_once __DIR__ . '/../services/FelhasznaloService.php';

class FelhasznaloController
{
    private FelhasznaloService $service;

    public function __construct(FelhasznaloService $service)
    {
        $this->service = $service;
    }

    public function osszes(): void
    {
        try {
            $felhasznalok = $this->service->osszesFelhasznalo();
            $this->valasz(200, array_map([$this, 'felhasznaloTombbe'], $felhasznalok));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function egy(int $id): void
    {
        try {
            $felhasznalo = $this->service->egyFelhasznalo($id);
            $this->valasz(200, $this->felhasznaloTombbe($felhasznalo));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function letrehoz(?array $adat): void
    {
        try {
            if ($adat === null) {
                throw new ErvenytelenAdatException('Hibás vagy hiányzó JSON a kérés törzsében.');
            }

            $felhasznalo = $this->keresbolFelhasznalo($adat);

            $ujId = $this->service->letrehoz($felhasznalo);
            $felhasznalo->setId($ujId);

            $this->valasz(201, $this->felhasznaloTombbe($felhasznalo));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function modosit(int $id, ?array $adat): void
    {
        try {
            if ($adat === null) {
                throw new ErvenytelenAdatException('Hibás vagy hiányzó JSON a kérés törzsében.');
            }

            $felhasznalo = $this->keresbolFelhasznalo($adat);
            $felhasznalo->setId($id);

            $modositott = $this->service->modosit($felhasznalo);

            $this->valasz(200, $this->felhasznaloTombbe($modositott));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function torol(int $id): void
    {
        try {
            $this->service->torol($id);
            $this->valasz(200, ['uzenet' => 'A felhasználó törölve.']);
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    private function hibaValasz(Throwable $hiba): void
    {
        if ($hiba instanceof NemTalalhatoException) {
            $this->valasz(404, ['hiba' => $hiba->getMessage()]);
            return;
        }

        if ($hiba instanceof ErvenytelenAdatException) {
            $this->valasz(400, ['hiba' => $hiba->getMessage()]);
            return;
        }

        $this->valasz(500, ['hiba' => 'Váratlan szerverhiba történt: ' . $hiba->getMessage()]);
    }

    private function valasz(int $statuszkod, mixed $adat): void
    {
        http_response_code($statuszkod);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($adat, JSON_UNESCAPED_UNICODE);
    }

    private function felhasznaloTombbe(Felhasznalo $felhasznalo): array
    {
        return [
            'id' => $felhasznalo->getId(),
            'nev' => $felhasznalo->getNev(),
            'email' => $felhasznalo->getEmail(),
        ];
    }

    private function keresbolFelhasznalo(array $adat): Felhasznalo
    {
        $this->kotelezoMezok($adat, ['nev', 'email', 'jelszo']);

        return new Felhasznalo(
            (string) $adat['nev'],
            (string) $adat['email'],
            (string) $adat['jelszo']
        );
    }

    private function kotelezoMezok(array $adat, array $mezok): void
    {
        foreach ($mezok as $mezo) {
            if (!isset($adat[$mezo]) || $adat[$mezo] === '') {
                throw new ErvenytelenAdatException("Hiányzó kötelező mező: {$mezo}");
            }
        }
    }
}
