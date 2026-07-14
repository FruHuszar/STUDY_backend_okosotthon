<?php

require_once __DIR__ . '/../services/EszkozService.php';

class EszkozController{
    private EszkozService $service;

    public function __construct(EszkozService $service)
    {
        $this->service = $service;
    }

    public function osszes(): void {
        try {
            $eszkozok = $this->service->osszesEszkoz();
            $this->valasz(200, array_map([$this, 'eszkozTombbe'], $eszkozok));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    } 

    public function egy(int $id): void {
        try {
            $eszkoz = $this->service->egyEszkoz($id);
            $this->valasz(200, $this->eszkozTombbe($eszkoz));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function torol(int $id): void
    {
        try {
            $this->service->torol($id);
            $this->valasz(200, ['uzenet' => 'Az eszköz törölve.']);
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

    private function eszkozTombbe(Eszkoz $eszkoz): array {
        $adat = [
            'id' => $eszkoz->getId(),
            'megnevezes' => $eszkoz->getMegnevezes(),
            'eszkoztipus_id' => $eszkoz->getEszkoztipusId(),
            'allapot' => $eszkoz->getAllapot(),
            'helyiseg_id' => $eszkoz->getHelyisegId(),
        ];

        if ($eszkoz instanceof Lampa) {
            $adat['tipus'] = 'lampa';
            $adat['fenyero'] = $eszkoz->getFenyero();
        } elseif ($eszkoz instanceof Termosztat) {
            $adat['tipus'] = 'termosztat';
            $adat['celhomerseklet'] = $eszkoz->getCelhomerseklet();
            $adat['aktualis_homerseklet'] = $eszkoz->getAktualisHomerseklet();
            $adat['uzemmod'] = $eszkoz->getUzemmod();
        } elseif ($eszkoz instanceof Dugalj) {
            $adat['tipus'] = 'dugalj';
            $adat['aktualis_fogyasztas'] = $eszkoz->getAktualisFogyasztas();
        }

        return $adat;
    }

    //letrehoz:
    public function letrehoz(?array $adat): void {
        try {
            if ($adat === null) {
                throw new ErvenytelenAdatException('Hibás vagy hiányzó JSON a kérés törzsében.');
            }

            $eszkoz = $this->keresbolEszkoz($adat);

            $ujId = $this->service->letrehoz($eszkoz);
            $eszkoz->setId($ujId);

            $this->valasz(201, $this->eszkozTombbe($eszkoz));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    private function keresbolEszkoz(array $adat): Eszkoz
    {
        $this->kotelezoMezok($adat, ['tipus', 'megnevezes', 'eszkoztipus_id']);

        $megnevezes = (string) $adat['megnevezes'];
        $eszkoztipusId = (int) $adat['eszkoztipus_id'];
        $allapot = (bool) ($adat['allapot'] ?? false);
        $termekszam = $adat['termekszam'] ?? null;
        $garanciaKezdete = $adat['garancia_kezdete'] ?? null;
        $garanciaVege = $adat['garancia_vege'] ?? null;
        $helyisegId = isset($adat['helyiseg_id']) ? (int) $adat['helyiseg_id'] : null;

        switch ($adat['tipus']) {
            case 'lampa':
                return new Lampa(
                    $megnevezes, $eszkoztipusId, $allapot,
                    isset($adat['fenyero']) ? (int) $adat['fenyero'] : null,
                    $termekszam, $garanciaKezdete, $garanciaVege, $helyisegId
                );
            case 'termosztat':
                return new Termosztat(
                    $megnevezes, $eszkoztipusId, $allapot,
                    isset($adat['celhomerseklet']) ? (float) $adat['celhomerseklet'] : null,
                    isset($adat['aktualis_homerseklet']) ? (float) $adat['aktualis_homerseklet'] : null,
                    $adat['uzemmod'] ?? null,
                    $termekszam, $garanciaKezdete, $garanciaVege, $helyisegId
                );
            case 'dugalj':
                return new Dugalj(
                    $megnevezes, $eszkoztipusId, $allapot,
                    isset($adat['aktualis_fogyasztas']) ? (float) $adat['aktualis_fogyasztas'] : null,
                    $termekszam, $garanciaKezdete, $garanciaVege, $helyisegId
                );
            default:
                throw new ErvenytelenAdatException("Ismeretlen vagy hiányzó típus: {$adat['tipus']}");
        }
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

