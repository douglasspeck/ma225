<?php

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("O arquivo .env não foi encontrado.");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Separa chave=valor
        // O limite '2' garante que se o valor tiver '=' (ex: base64), não quebre
        list($name, $value) = explode('=', $line, 2);
        
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

try {
    loadEnv(dirname(__DIR__, 2) . '/.env');
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}

?>