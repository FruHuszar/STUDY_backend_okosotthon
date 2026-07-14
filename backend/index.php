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
    $id = isset($szegmensek[1]) ? (int) $szegmensek[1] : null;

    if ($eroforras !== 'eszkozok') {
        valaszol(404, ['hiba' => 'Ismeretlen útvonal.']);
        return;
    }

    $akcio = akcioMeghatarozasa($metodus, $id);

    if ($akcio === null) {
        valaszol(405, ['hiba' => 'A metódus nem engedélyezett ezen az útvonalon.']);
        return;
    }

    Env::load(__DIR__ . '/.env');

    $kapcsolat = (new Database())->getConnection();
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
} catch (Throwable $hiba) {
    valaszol(500, [
        'hiba' => $hiba->getMessage(),
        'fajl' => $hiba->getFile(),
        'sor'  => $hiba->getLine(),
        'trace'=> $hiba->getTraceAsString()
    ]);
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