<?php
/**
 * Router para PHP built-in server (Wasmer Edge)
 * Substitui o mod_rewrite do Apache/.htaccess
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rawurldecode($uri);

// Serve a raiz → index.php (landing page)
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    exit;
}

// Serve arquivos estáticos reais (css, js, imagens, etc.)
$file = __DIR__ . $uri;
if (file_exists($file) && !is_dir($file)) {
    return false; // deixa o servidor built-in servir
}

// Tudo o mais → addon.php (que já tem o route_request() interno)
require __DIR__ . '/addon.php';
