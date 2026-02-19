<?php
/**
 * Load all JSON offer metadata from /ofertas
 * and expose them globally.
 */

// Global container
$GLOBALS['OFERTAS'] = [];

$dir = dirname(__DIR__, 2) . '/ofertas';

// Check if directory exists
if (is_dir($dir)) {

    $files = glob($dir . '/*.json');

    foreach ($files as $file) {
        // Extract offer name (filename without extension)
        $oferta = pathinfo($file, PATHINFO_FILENAME);

        // Read file contents
        $json = file_get_contents($file);
        if ($json === false) continue;

        // Decode JSON
        $data = json_decode($json, true);

        // Skip invalid JSON
        if (json_last_error() !== JSON_ERROR_NONE) continue;

        // Store in global object
        $GLOBALS['OFERTAS'][$oferta] = $data;
    }
}

function oferta(string $query, $default = null) {
    if (!isset($GLOBALS['OFERTAS']) || $query === '') {
        return $default;
    }

    $current = $GLOBALS['OFERTAS'];
    $parts = explode('.', $query);

    foreach ($parts as $part) {
        // Extrai chave base + possíveis índices [n]
        if (!preg_match('/^([^\[]+)((?:\[\d+\])*)$/', $part, $matches)) {
            return $default;
        }

        $key = $matches[1];
        $indexes = $matches[2];

        // Acessa chave base
        if (!is_array($current) || !array_key_exists($key, $current)) {
            return $default;
        }

        $current = $current[$key];

        // Processa índices [n] se existirem
        if ($indexes) {
            preg_match_all('/\[(\d+)\]/', $indexes, $idxMatches);

            foreach ($idxMatches[1] as $i) {
                if (!is_array($current) || !array_key_exists((int)$i, $current)) {
                    return $default;
                }
                $current = $current[(int)$i];
            }
        }
    }

    return $current;
}

if (!empty($GLOBALS['OFERTAS'])) {

    // Pega só IDs válidos (YYYY ou YYYY-algo)
    $ids = array_filter(
        array_keys($GLOBALS['OFERTAS']),
        fn($id) => preg_match('/^\d{4}($|-)/', $id)
    );

    if (!empty($ids)) {

        usort($ids, function ($a, $b) {
            // Extrai ano
            preg_match('/^(\d{4})/', $a, $ma);
            preg_match('/^(\d{4})/', $b, $mb);

            $yearA = (int)$ma[1];
            $yearB = (int)$mb[1];

            // Ano mais recente primeiro
            if ($yearA !== $yearB) {
                return $yearB <=> $yearA;
            }

            // Mesmo ano → ordenação natural do resto
            return strnatcmp($b, $a);
        });

        $latest = $ids[0];

        $GLOBALS['OFERTAS']['current'] = $GLOBALS['OFERTAS'][$latest];
        $GLOBALS['OFERTAS']['current_id'] = $latest;
    }
}

?>