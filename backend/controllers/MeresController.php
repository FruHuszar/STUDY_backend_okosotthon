<?php

require_once __DIR__ . '/../services/MeresService.php';

class MeresController
{
    private MeresService $service;

    public function __construct(MeresService $service)
    {
        $this->service = $service;
    }

    public function osszes(): void
    {
        try {
            $meresek = $this->service->osszesMeres();
            $this->valasz(200, array_map([$this, 'meresTombbe'], $meresek));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function eszkozMeresei(int $eszkozId): void
    {
        try {
            $meresek = $this->service->eszkozMeresei($eszkozId);
            $this->valasz(200, array_map([$this, 'meresTombbe'], $meresek));
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

            $meres = $this->keresbolMeres($adat);

            $ujId = $this->service->rogzit($meres);
            $meres->setId($ujId);

            $this->valasz(201, $this->meresTombbe($meres));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function napiFogyasztas(): void
    {
        try {
            $datum = $_GET['datum'] ?? null;

            if ($datum === null || $datum === '') {
                throw new ErvenytelenAdatException('Hiányzó kötelező query paraméter: datum');
            }

            $this->valasz(200, $this->service->napiFogyasztas((string) $datum));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function oraiHomerseklet(int $eszkozId): void
    {
        try {
            $datum = $_GET['datum'] ?? null;

            if ($datum === null || $datum === '') {
                throw new ErvenytelenAdatException('Hiányzó kötelező query paraméter: datum');
            }

            $this->valasz(200, $this->service->oraiHomerseklet($eszkozId, (string) $datum));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function topFogyasztok(): void
    {
        try {
            $napok = isset($_GET['napok']) ? (int) $_GET['napok'] : 7;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            $this->valasz(200, $this->service->topFogyasztok($napok, $limit));
        } catch (Throwable $hiba) {
            $this->hibaValasz($hiba);
        }
    }

    public function magasFogyasztasuHelyisegek(): void
    {
        try {
            $kuszob = isset($_GET['kuszob']) ? (float) $_GET['kuszob'] : null;

            if ($kuszob === null) {
                throw new ErvenytelenAdatException('Hiányzó kötelező query paraméter: kuszob');
            }

            $this->valasz(200, $this->service->magasFogyasztasuHelyisegek($kuszob));
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

    private function meresTombbe(Meres $meres): array
    {
        return [
            'id' => $meres->getId(),
            'eszkoz_id' => $meres->getEszkozId(),
            'idobelyeg' => $meres->getIdobelyeg(),
            'meres_tipusa' => $meres->getMeresTipusa(),
            'ertek' => $meres->getErtek(),
        ];
    }

    private function keresbolMeres(array $adat): Meres
    {
        $this->kotelezoMezok($adat, ['eszkoz_id', 'meres_tipusa', 'ertek']);

        return new Meres(
            (int) $adat['eszkoz_id'],
            (string) $adat['meres_tipusa'],
            (float) $adat['ertek'],
            $adat['idobelyeg'] ?? null
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
