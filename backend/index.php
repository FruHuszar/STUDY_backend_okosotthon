<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

spl_autoload_register(function (string $osztaly): void {
    foreach (['config', 'modells', 'repositories', 'services', 'controllers', 'exceptions'] as $mappa) {
        $utvonal = __DIR__ . '/' . $mappa . '/' . $osztaly . '.php';

        if (is_readable($utvonal)) {
            require_once $utvonal;
            return;
        }
    }
});

try {
    $metodus = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $szegmensek = utvonalSzegmensek();
    $eroforras = $szegmensek[0] ?? '';

    Env::load(__DIR__ . '/.env');
    $kapcsolat = (new Database())->getConnection();

    switch ($eroforras) {
        case 'eszkozok':
            eszkozokUtvonal($metodus, $szegmensek, $kapcsolat);
            break;

        case 'helyisegek':
            helyisegekUtvonal($metodus, $szegmensek, $kapcsolat);
            break;

        case 'felhasznalok':
            felhasznalokUtvonal($metodus, $szegmensek, $kapcsolat);
            break;

        case 'meresek':
            meresekUtvonal($metodus, $szegmensek, $kapcsolat);
            break;

        case 'utemezesek':
            utemezesekUtvonal($metodus, $szegmensek, $kapcsolat);
            break;

        case 'napok':
            napokUtvonal($metodus, $szegmensek, $kapcsolat);
            break;

        default:
            valaszol(404, ['hiba' => 'Ismeretlen útvonal.']);
    }
} catch (Throwable $hiba) {
    valaszol(500, [
        'hiba' => $hiba->getMessage(),
        'fajl' => $hiba->getFile(),
        'sor'  => $hiba->getLine(),
        'trace'=> $hiba->getTraceAsString()
    ]);
}

// ---------------------------------------------------------------------
// Eszközök: GET (összes), GET/{id}, POST, DELETE/{id}
// ---------------------------------------------------------------------
function eszkozokUtvonal(string $metodus, array $szegmensek, PDO $kapcsolat): void
{
    $id = isset($szegmensek[1]) ? (int) $szegmensek[1] : null;

    $akcio = akcioMeghatarozasa($metodus, $id);

    if ($akcio === null) {
        valaszol(405, ['hiba' => 'A metódus nem engedélyezett ezen az útvonalon.']);
        return;
    }

    $controller = new EszkozController(new EszkozService(new EszkozRepository($kapcsolat)));

    switch ($akcio) {
        case 'osszes':
            $controller->osszes();
            break;
        case 'egy':
            $controller->egy($id);
            break;
        case 'letrehoz':
            $controller->letrehoz(jsonTorzs());
            break;
        case 'torol':
            $controller->torol($id);
            break;
    }
}

// ---------------------------------------------------------------------
// Helyiségek: GET (összes), GET/{id}, POST, PUT/{id}, DELETE/{id}
// ---------------------------------------------------------------------
function helyisegekUtvonal(string $metodus, array $szegmensek, PDO $kapcsolat): void
{
    $id = isset($szegmensek[1]) ? (int) $szegmensek[1] : null;

    $akcio = akcioMeghatarozasaCrud($metodus, $id);

    if ($akcio === null) {
        valaszol(405, ['hiba' => 'A metódus nem engedélyezett ezen az útvonalon.']);
        return;
    }

    $controller = new HelyisegController(new HelyisegService(new HelyisegRepository($kapcsolat)));

    switch ($akcio) {
        case 'osszes':
            $controller->osszes();
            break;
        case 'egy':
            $controller->egy($id);
            break;
        case 'letrehoz':
            $controller->letrehoz(jsonTorzs());
            break;
        case 'modosit':
            $controller->modosit($id, jsonTorzs());
            break;
        case 'torol':
            $controller->torol($id);
            break;
    }
}

// ---------------------------------------------------------------------
// Felhasználók: GET (összes), GET/{id}, POST, PUT/{id}, DELETE/{id}
// ---------------------------------------------------------------------
function felhasznalokUtvonal(string $metodus, array $szegmensek, PDO $kapcsolat): void
{
    $id = isset($szegmensek[1]) ? (int) $szegmensek[1] : null;

    $akcio = akcioMeghatarozasaCrud($metodus, $id);

    if ($akcio === null) {
        valaszol(405, ['hiba' => 'A metódus nem engedélyezett ezen az útvonalon.']);
        return;
    }

    $controller = new FelhasznaloController(new FelhasznaloService(new FelhasznaloRepository($kapcsolat)));

    switch ($akcio) {
        case 'osszes':
            $controller->osszes();
            break;
        case 'egy':
            $controller->egy($id);
            break;
        case 'letrehoz':
            $controller->letrehoz(jsonTorzs());
            break;
        case 'modosit':
            $controller->modosit($id, jsonTorzs());
            break;
        case 'torol':
            $controller->torol($id);
            break;
    }
}

// ---------------------------------------------------------------------
// Mérések:
//   GET   meresek                                  -> összes
//   GET   meresek/eszkoz/{eszkozId}                 -> egy eszköz mérései
//   POST  meresek                                  -> létrehozás
//   GET   meresek/napi-fogyasztas?datum=YYYY-MM-DD   -> napi fogyasztás helyiségenként
//   GET   meresek/orai-homerseklet/{eszkozId}?datum=YYYY-MM-DD -> órai átlaghőmérséklet
//   GET   meresek/top-fogyasztok?napok=7&limit=10    -> top fogyasztó eszközök
//   GET   meresek/magas-fogyasztasu-helyisegek?kuszob=... -> HAVING-es lekérdezés
// ---------------------------------------------------------------------
function meresekUtvonal(string $metodus, array $szegmensek, PDO $kapcsolat): void
{
    $controller = new MeresController(new MeresService(new MeresRepository($kapcsolat)));
    $masodikSzegmens = $szegmensek[1] ?? null;

    if ($metodus === 'GET' && $masodikSzegmens === null) {
        $controller->osszes();
        return;
    }

    if ($metodus === 'POST' && $masodikSzegmens === null) {
        $controller->letrehoz(jsonTorzs());
        return;
    }

    if ($metodus === 'GET' && $masodikSzegmens === 'eszkoz' && isset($szegmensek[2])) {
        $controller->eszkozMeresei((int) $szegmensek[2]);
        return;
    }

    if ($metodus === 'GET' && $masodikSzegmens === 'napi-fogyasztas') {
        $controller->napiFogyasztas();
        return;
    }

    if ($metodus === 'GET' && $masodikSzegmens === 'orai-homerseklet' && isset($szegmensek[2])) {
        $controller->oraiHomerseklet((int) $szegmensek[2]);
        return;
    }

    if ($metodus === 'GET' && $masodikSzegmens === 'top-fogyasztok') {
        $controller->topFogyasztok();
        return;
    }

    if ($metodus === 'GET' && $masodikSzegmens === 'magas-fogyasztasu-helyisegek') {
        $controller->magasFogyasztasuHelyisegek();
        return;
    }

    valaszol(404, ['hiba' => 'Ismeretlen útvonal vagy nem engedélyezett metódus.']);
}

// ---------------------------------------------------------------------
// Ütemezések:
//   GET    utemezesek                       -> összes
//   GET    utemezesek/{id}                   -> egy (If-None-Match / ETag / 304)
//   GET    utemezesek/eszkoz/{eszkozId}       -> egy eszköz ütemezései
//   POST   utemezesek                        -> létrehozás
//   PUT    utemezesek/{id}                    -> módosítás
//   DELETE utemezesek/{id}                    -> törlés
// ---------------------------------------------------------------------
function utemezesekUtvonal(string $metodus, array $szegmensek, PDO $kapcsolat): void
{
    $controller = new UtemezesController(new UtemezesService(new UtemezesRepository($kapcsolat)));
    $masodikSzegmens = $szegmensek[1] ?? null;

    if ($metodus === 'GET' && $masodikSzegmens === null) {
        $controller->osszes();
        return;
    }

    if ($metodus === 'POST' && $masodikSzegmens === null) {
        $controller->letrehoz(jsonTorzs());
        return;
    }

    if ($metodus === 'GET' && $masodikSzegmens === 'eszkoz' && isset($szegmensek[2])) {
        $controller->eszkozUtemezesei((int) $szegmensek[2]);
        return;
    }

    if ($masodikSzegmens !== null && ctype_digit($masodikSzegmens)) {
        $id = (int) $masodikSzegmens;

        if ($metodus === 'GET') {
            $controller->egy($id);
            return;
        }

        if ($metodus === 'PUT' || $metodus === 'PATCH') {
            $controller->modosit($id, jsonTorzs());
            return;
        }

        if ($metodus === 'DELETE') {
            $controller->torol($id);
            return;
        }
    }

    valaszol(404, ['hiba' => 'Ismeretlen útvonal vagy nem engedélyezett metódus.']);
}

// ---------------------------------------------------------------------
// Napok: GET (a hét napjainak listája, csak olvasás)
// ---------------------------------------------------------------------
function napokUtvonal(string $metodus, array $szegmensek, PDO $kapcsolat): void
{
    if ($metodus !== 'GET') {
        valaszol(405, ['hiba' => 'A metódus nem engedélyezett ezen az útvonalon.']);
        return;
    }

    $controller = new NapController(new NapRepository($kapcsolat));
    $controller->osszes();
}

function utvonalSzegmensek(): array
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $alap = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

    if ($alap !== '/' && $alap !== '' && str_starts_with($uri, $alap)) {
        $uri = substr($uri, strlen($alap));
    }

    $uri = trim($uri, '/');

    return $uri === '' ? [] : explode('/', $uri);
}

function akcioMeghatarozasa(string $metodus, ?int $id): ?string
{
    if ($metodus === 'GET' && $id === null) {
        return 'osszes';
    }

    if ($metodus === 'GET') {
        return 'egy';
    }

    if ($metodus === 'POST' && $id === null) {
        return 'letrehoz';
    }

    if ($metodus === 'DELETE' && $id !== null) {
        return 'torol';
    }

    return null;
}

/**
 * Ugyanaz mint akcioMeghatarozasa, kiegészítve a PUT/PATCH -> 'modosit' esettel
 * (ezt használják a teljes CRUD-ot nyújtó erőforrások: helyisegek, felhasznalok).
 */
function akcioMeghatarozasaCrud(string $metodus, ?int $id): ?string
{
    if (($metodus === 'PUT' || $metodus === 'PATCH') && $id !== null) {
        return 'modosit';
    }

    return akcioMeghatarozasa($metodus, $id);
}

function jsonTorzs(): ?array
{
    $adat = json_decode(file_get_contents('php://input'), true);

    return is_array($adat) ? $adat : null;
}

function valaszol(int $kod, array $adat): void
{
    http_response_code($kod);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE);
}
