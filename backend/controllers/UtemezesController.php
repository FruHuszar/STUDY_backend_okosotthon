<?php

require_once __DIR__ . '/../services/UtemezesService.php';

class UtemezesController
{
    private UtemezesService $service;

    public function __construct(UtemezesService $service)
    {
        $this->service = $service;
    }

    public function osszes(): void
    {
        try {
            $utemezesek = $this->service->osszesUtemezes();
            $this->valasz(200, array_map([$this, 'utemezesTombbe'], $utemezesek));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    /**
     * Egy ütemezés lekérése ETag/If-None-Match alapú 304-mechanizmussal.
     */
    public function egy(int $id): void
    {
        try {
            $utemezes = $this->service->egyUtemezes($id);
            $etag = $this->service->etagKeszites($utemezes);

            $kliensEtag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;

            header("ETag: {$etag}");

            if ($kliensEtag !== null && trim($kliensEtag) === $etag) {
                http_response_code(304);
                return;
            }

            $this->valasz(200, $this->utemezesTombbe($utemezes));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function eszkozUtemezesei(int $eszkozId): void
    {
        try {
            $utemezesek = $this->service->eszkozUtemezesei($eszkozId);
            $this->valasz(200, array_map([$this, 'utemezesTombbe'], $utemezesek));
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

            $utemezes = $this->keresbolUtemezes($adat);

            $ujId = $this->service->letrehoz($utemezes);
            $utemezes = $this->service->egyUtemezes($ujId);

            $this->valasz(201, $this->utemezesTombbe($utemezes));
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

            $utemezes = $this->keresbolUtemezes($adat);
            $utemezes->setId($id);

            $this->service->modosit($utemezes);
            $frissitett = $this->service->egyUtemezes($id);

            $this->valasz(200, $this->utemezesTombbe($frissitett));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function torol(int $id): void
    {
        try {
            $this->service->torol($id);
            $this->valasz(200, ['uzenet' => 'Az ütemezés törölve.']);
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

    private function utemezesTombbe(Utemezes $utemezes): array
    {
        return [
            'id' => $utemezes->getId(),
            'eszkoz_id' => $utemezes->getEszkozId(),
            'kezdo_ido' => $utemezes->getKezdoIdo(),
            'zaro_ido' => $utemezes->getZaroIdo(),
            'cel_allapot' => $utemezes->getCelAllapot(),
            'cel_ertek' => $utemezes->getCelErtek(),
            'letrehozva_timestamp' => $utemezes->getLetrehozvaTimestamp(),
            'modositva_timestamp' => $utemezes->getModositvaTimestamp(),
            'napok' => $utemezes->getNapok(),
        ];
    }

    private function keresbolUtemezes(array $adat): Utemezes
    {
        $this->kotelezoMezok($adat, ['eszkoz_id']);

        $napok = isset($adat['napok']) && is_array($adat['napok'])
            ? array_map('intval', $adat['napok'])
            : [];

        return new Utemezes(
            (int) $adat['eszkoz_id'],
            $adat['kezdo_ido'] ?? null,
            $adat['zaro_ido'] ?? null,
            isset($adat['cel_allapot']) ? (bool) $adat['cel_allapot'] : null,
            isset($adat['cel_ertek']) ? (float) $adat['cel_ertek'] : null,
            $napok
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
