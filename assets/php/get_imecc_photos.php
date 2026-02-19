<?php

require_once('env.php');

// Constantes para fácil manutenção
define('BASE_URL', 'https://www.ime.unicamp.br');
define('IMG_DIR', dirname(__DIR__,1) . '/img/imecc_pessoas/');
define('FORM_BUILD_ID', getenv('FORM_BUILD_ID'));
define('THEME_TOKEN', getenv('THEME_TOKEN'));

function buscar_imagem_imecc(string $termo_busca): void {
    $url_ajax = BASE_URL . "/system/ajax";

    // Configuração do Payload
    $data = [
        'ldap_yp-keyword' => $termo_busca,
        'ldap_yp-select[0]' => 'ALL',
        'form_id' => 'ldap_yp_search_form',
        'form_build_id' => FORM_BUILD_ID,
        '_triggering_element_name' => 'ldap_yp-keyword',
        'ajax_page_state[theme]' => 'imecc',
        'ajax_page_state[theme_token]' => THEME_TOKEN
    ];

    // Inicialização otimizada do cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url_ajax,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        // Desativar SSL Verify apenas se necessário (dev/debug)
        CURLOPT_SSL_VERIFYPEER => false, 
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)',
            'X-Requested-With: XMLHttpRequest',
            'Origin: ' . BASE_URL,
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
        ]
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        die("Erro na conexão cURL: $error\n");
    }

    $json = json_decode($response, true);

    // Verificação rápida se o JSON é válido
    if (!is_array($json)) {
        die("Erro: Resposta inválida do servidor (não é JSON).\n");
    }

    // Busca o HTML dentro do JSON
    $html_content = null;
    foreach ($json as $item) {
        // O operador '??' evita avisos se a chave não existir
        if (($item['command'] ?? '') === 'insert' && ($item['selector'] ?? '') === '#ldap_yp-result-wrapper') {
            $html_content = $item['data'];
            break;
        }
    }

    if (!$html_content) {
        echo "Nenhum HTML de resultado encontrado na resposta.\n";
        return;
    }

    // Parsing do HTML com DOMDocument
    $dom = new DOMDocument();
    
    // Suprime erros de HTML malformado (comum em raspagem) temporariamente
    $internalErrors = libxml_use_internal_errors(true);
    // Adiciona cabeçalho XML para forçar UTF-8 e carrega
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_use_internal_errors($internalErrors); // Restaura estado anterior

    $xpath = new DOMXPath($dom);
    
    // Busca direta pelo atributo src da imagem dentro da div .foto
    $nodes = $xpath->query('//div[contains(@class, "foto")]//img/@src');

    if ($nodes->length > 0) {
        $src = $nodes->item(0)->nodeValue;

        // Regex para extrair e validar Base64 + extensão
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $src, $matches)) {
            $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1]; // Normaliza jpeg para jpg
            $base64_data = $matches[2];
            $filename = "{$termo_busca}.{$ext}";

            if (file_put_contents(IMG_DIR . $filename, base64_decode($base64_data))) {
                echo "Sucesso: Imagem salva como '$filename'\n";
            } else {
                echo "Erro ao gravar o arquivo em disco.\n";
            }
        } else {
            echo "Aviso: Imagem encontrada, mas é um link externo: $src\n";
        }
    } else {
        echo "Usuário encontrado, mas sem foto disponível.\n";
    }
}

buscar_imagem_imecc('giuzu');

?>