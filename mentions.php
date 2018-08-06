<?php declare(strict_types=1);

session_start();

if (false === isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$data = new \SplFileObject('notxte.txt', 'r');
$mentions = [];
while ($data->valid()) {
    @list($date, $txt) = explode("\t", rtrim($data->current(), "\n"), 2);
    if ($date !== null && $txt !== null) {
        if (0 < \preg_match_all('%@<(?:(\S+?)\s+)?([a-z][a-z0-9+.-]*:(?://)?(.+?)/?)>%', $txt, $matches)) {
            foreach ($matches[2] as $key => $url) {
                $mentions[] = [
                    'name' => $matches[1][$key] !== '' ? $matches[1][$key] : $matches[3][$key],
                    'url' => $url,
                ];
            }
        }
    }
    $data->next();
}
$data = null;
echo json_encode($mentions);
