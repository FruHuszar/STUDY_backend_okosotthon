<?php

require_once __DIR__ . '/../services/HelyisegService.php';

class HelyisegController
{
    private HelyisegService $service;

    public function __construct(HelyisegService $service)
    {
        $this->service = $service;
    }

    public function osszes(): void
    {
        try {
            $helyisegek = $this->service->osszesHelyiseg();
            $this->valasz(200, array_map([$this, 'helyisegTombbe'], $helyisegek));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function egy(int $id): void
    {
        try {
            $helyiseg = $this->service->egyHelyiseg($id);
            $this->valasz(200, $this->helyisegTombbe($helyiseg));
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

            $helyiseg = $this->keresbolHelyiseg($adat);

            $ujId = $this->service->letrehoz($helyiseg);
            $helyiseg->setId($ujId);

            $this->valasz(201, $this->helyisegTombbe($helyiseg));
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

            $helyiseg = $this->keresbolHelyiseg($adat);
            $helyiseg->setId($id);

            $modositott = $this->service->modosit($helyiseg);

            $this->valasz(200, $this->helyisegTombbe($modositott));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function torol(int $id): void
    {
        try {
            $this->service->torol($id);
            $this->valasz(200, ['uzenet' => 'A helyiség törölve.']);
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

    private function helyisegTombbe(Helyiseg $helyiseg): array
    {
        return [
            'id' => $helyiseg->getId(),
            'megnevezes' => $helyiseg->getMegnevezes(),
            'terulet' => $helyiseg->getTerulet(),
            'felhasznalo_id' => $helyiseg->getFelhasznaloId(),
        ];
    }

    private function keresbolHelyiseg(array $adat): Helyiseg
    {
        $this->kotelezoMezok($adat, ['megnevezes', 'felhasznalo_id']);

        $megnevezes = (string) $adat['megnevezes'];
        $felhasznaloId = (int) $adat['felhasznalo_id'];
        $terulet = isset($adat['terulet']) ? (float) $adat['terulet'] : null;

        return new Helyiseg($megnevezes, $felhasznaloId, $terulet);
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
