<?php
/**
 * XC PRO - Stremio Addon Engine in PHP
 */

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// =============================================
// FUNÇÃO DE DEBUG (colocar no início)
// =============================================
function debug_log($message, $data = null) {
    $log_file = __DIR__ . '/xcpro_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_entry .= "\n" . print_r($data, true);
        } else {
            $log_entry .= " " . $data;
        }
    }
    
    //file_put_contents($log_file, $log_entry . "\n", FILE_APPEND);
}

// =============================================
// FUNÇÃO DE ROTEAMENTO (ANTES de qualquer lógica)
// =============================================
function route_request() {
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    debug_log("=== ROUTE_REQUEST INICIADA ===");
    debug_log("REQUEST_URI: " . $request_uri);
    debug_log("SCRIPT_NAME: " . $script_name);
    
    // Remove o caminho base
    $path = str_replace($script_name, '', $request_uri);
    $path = strtok($path, '?');
    $path = trim($path, '/');
    
    debug_log("Path processado: " . $path);
    
    // Se já tem parâmetros GET, usar eles
    if (!empty($_GET['config']) || !empty($_GET['action'])) {
        debug_log("Já tem parâmetros GET, ignorando roteamento");
        return;
    }
    
    if (empty($path)) {
        debug_log("Path vazio");
        return;
    }
    
    // Usar expressão regular para capturar o genre
    // Padrão: b64/[config]/catalog/tv/xcpro_tv/genre=QUALQUER_COISA.json
    $pattern = '#^b64/([^/]+)/catalog/([^/]+)/([^/]+)/genre=(.+)\.json$#';
    
    if (preg_match($pattern, $path, $matches)) {
        debug_log("Pattern match encontrado!");
        debug_log("Matches: " . print_r($matches, true));
        
        $_GET['config'] = $matches[1];
        $_GET['action'] = 'catalog';
        $_GET['type'] = $matches[2];
        $_GET['genre'] = urldecode($matches[4]);
        
        debug_log("Config: " . $_GET['config']);
        debug_log("Type: " . $_GET['type']);
        debug_log("Genre DECODIFICADO: " . $_GET['genre']);
        return;
    }
    
    // Padrão: b64/[config]/catalog/tv/xcpro_tv/search=ALGO.json
    $pattern2 = '#^b64/([^/]+)/catalog/([^/]+)/([^/]+)/search=(.+)\.json$#';
    if (preg_match($pattern2, $path, $matches)) {
        debug_log("Pattern search encontrado!");
        $_GET['config'] = $matches[1];
        $_GET['action'] = 'catalog';
        $_GET['type'] = $matches[2];
        $_GET['search'] = urldecode($matches[4]);
        debug_log("Search: " . $_GET['search']);
        return;
    }
    
    // Padrão: b64/[config]/catalog/tv/xcpro_tv.json
    $pattern3 = '#^b64/([^/]+)/catalog/([^/]+)/([^/]+)\.json$#';
    if (preg_match($pattern3, $path, $matches)) {
        debug_log("Pattern catalog normal encontrado!");
        $_GET['config'] = $matches[1];
        $_GET['action'] = 'catalog';
        $_GET['type'] = $matches[2];
        $_GET['id'] = $matches[3];
        debug_log("ID: " . $_GET['id']);
        return;
    }
    
    // Padrão: b64/[config]/manifest.json
    $pattern4 = '#^b64/([^/]+)/manifest\.json$#';
    if (preg_match($pattern4, $path, $matches)) {
        debug_log("Pattern manifest encontrado!");
        $_GET['config'] = $matches[1];
        return;
    }
    
    // Padrão: b64/[config]/meta/[type]/[id].json
    $pattern5 = '#^b64/([^/]+)/meta/([^/]+)/([^/]+)\.json$#';
    if (preg_match($pattern5, $path, $matches)) {
        $_GET['config'] = $matches[1];
        $_GET['action'] = 'meta';
        $_GET['type'] = $matches[2];
        // Decodificar o ID corretamente
        $id_raw = urldecode($matches[3]);
        $_GET['id'] = $id_raw;
        debug_log("Meta ID raw: " . $matches[3]);
        debug_log("Meta ID decoded: " . $id_raw);
        return;
    }
    
    // Padrão: b64/[config]/stream/[type]/[id].json
    $pattern6 = '#^b64/([^/]+)/stream/([^/]+)/([^/]+)\.json$#';
    if (preg_match($pattern6, $path, $matches)) {
        debug_log("Pattern stream encontrado!");
        $_GET['config'] = $matches[1];
        $_GET['action'] = 'stream';
        $_GET['type'] = $matches[2];
        $_GET['id'] = $matches[3];
        return;
    }
    
    // manifest.json raiz
    if ($path === 'manifest.json') {
        debug_log("Manifest raiz");
        return;
    }
    
    debug_log("NENHUM pattern encontrado para: " . $path);
}

// =============================================
// EXECUTAR ROTEAMENTO (IMPORTANTE: fazer isso ANTES de qualquer outra coisa)
// =============================================
route_request();

// =============================================
// LOG DOS PARÂMETROS FINAIS
// =============================================
debug_log("=== PARÂMETROS FINAIS GET ===");
debug_log(print_r($_GET, true));
// -------------------------------------------------------------
// Helper Functions
// -------------------------------------------------------------

function fix_b64($str) {
    $str = str_replace(array('-', '_'), array('+', '/'), $str);
    while (strlen($str) % 4) {
        $str .= '=';
    }
    return $str;
}

function decode_config($b64str) {
    try {
        $fixed = fix_b64($b64str);
        $decoded = base64_decode($fixed);
        if (!$decoded) return null;
        $parsed = json_decode($decoded, true);
        if (isset($parsed['host']) && isset($parsed['username']) && isset($parsed['password'])) {
            return $parsed;
        }
    } catch (Exception $e) {
        return null;
    }
    return null;
}

function clean_for_search($text) {
    if (!$text) return '';
    $text = mb_strtolower($text, 'UTF-8');
    
    $accents = array(
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
        'ð'=>'d', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
        'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'þ'=>'th', 'ÿ'=>'y'
    );
    $text = strtr($text, $accents);
    // Manter letras E NÚMEROS
    $text = preg_replace('/[^a-z0-9]/', '', $text);
    return $text;
}

function make_curl_request($url, $params = []) {
    if (!empty($params)) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
    }
    
    debug_log("CURL Request URL: " . $url);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    debug_log("HTTP Code: " . $httpCode);
    if ($curlError) {
        debug_log("CURL Error: " . $curlError);
    }
    debug_log("Response: " . substr($response, 0, 500)); // Primeiros 500 caracteres
    
    return [
        'body' => $response,
        'code' => $httpCode
    ];
}

function map_item($type, $item) {
    // Para séries, o ID pode estar em series_id ou id
    if ($type === 'series') {
        $id = isset($item['series_id']) ? $item['series_id'] : (isset($item['id']) ? $item['id'] : '');
    } else {
        $id = isset($item['stream_id']) ? $item['stream_id'] : (isset($item['id']) ? $item['id'] : '');
    }
    
    $name = isset($item['name']) ? $item['name'] : (isset($item['title']) ? $item['title'] : 'Sem título');
    
    // Poster/Capa
    $poster = '';
    if ($type === 'series') {
        $poster = isset($item['cover']) ? $item['cover'] : (isset($item['cover_big']) ? $item['cover_big'] : '');
    } else {
        $poster = isset($item['stream_icon']) ? $item['stream_icon'] : (isset($item['cover']) ? $item['cover'] : '');
    }
    
    // Se o poster for relativo, adiciona o host
    if (!empty($poster) && strpos($poster, 'http') !== 0 && isset($GLOBALS['host'])) {
        $poster = rtrim($GLOBALS['host'], '/') . '/' . ltrim($poster, '/');
    }
    
    return [
        'id' => "xcpro:$type:$id",
        'type' => $type,
        'name' => $name,
        'poster' => $poster,
        'posterShape' => ($type === 'tv') ? 'square' : 'poster',
        'imdbRating' => isset($item['rating']) ? (float)$item['rating'] : null,
        'year' => isset($item['year']) ? (int)$item['year'] : null,
        'description' => isset($item['plot']) ? $item['plot'] : "XC PRO " . ucfirst($type)
    ];
}

// -------------------------------------------------------------
// Controller Logic
// -------------------------------------------------------------

// 1. MANIFEST (sem config) - Retorna manifest com configurationRequired: true
if (!isset($_GET['config']) || empty($_GET['config'])) {
    echo json_encode([
        'id' => 'org.community.xcpro',
        'name' => 'XC PRO',
        'version' => '1.0.0',
        'description' => 'XC PRO Addon para Stremio - Integração com servidores Xtream Codes.',
        'logo' => 'https://i.pinimg.com/474x/55/e6/b4/55e6b4246fa6fe1ac7846b94ee7da798.jpg',
        'idPrefixes' => ['xcpro'],
        'types' => ['movie', 'series', 'tv'],
        'resources' => ['catalog', 'meta', 'stream'],
        'behaviorHints' => [
            'configurable' => true,
            'configurationRequired' => true
        ],
        'catalogs' => []
    ]);
    exit;
}

// Decodificar configuração
$config_str = $_GET['config'];
$user_config = decode_config($config_str);

if (!$user_config) {
    http_response_code(400);
    echo json_encode(['error' => 'Configuração inválida.']);
    exit;
}

$host = rtrim($user_config['host'], '/');
$username = $user_config['username'];
$password = $user_config['password'];
$f4mProxy = isset($user_config['f4mProxy']) ? rtrim($user_config['f4mProxy'], '/') : '';

// Tornar host global para uso na função map_item
$GLOBALS['host'] = $host;

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 2. MANIFEST ACTION (com config)
if (empty($action)) {
    // Coletar categorias
    $movie_cats_res = make_curl_request("$host/player_api.php", [
        'username' => $username,
        'password' => $password,
        'action' => 'get_vod_categories'
    ]);
    $movie_cats = json_decode($movie_cats_res['body'], true) ?: [];
    $movie_genres = [];
    if (is_array($movie_cats)) {
        foreach ($movie_cats as $cat) {
            if (isset($cat['category_name'])) {
                $movie_genres[] = $cat['category_name'];
            }
        }
    }

    $series_cats_res = make_curl_request("$host/player_api.php", [
        'username' => $username,
        'password' => $password,
        'action' => 'get_series_categories'
    ]);
    $series_cats = json_decode($series_cats_res['body'], true) ?: [];
    $series_genres = [];
    if (is_array($series_cats)) {
        foreach ($series_cats as $cat) {
            if (isset($cat['category_name'])) {
                $series_genres[] = $cat['category_name'];
            }
        }
    }

    $live_cats_res = make_curl_request("$host/player_api.php", [
        'username' => $username,
        'password' => $password,
        'action' => 'get_live_categories'
    ]);
    $live_cats = json_decode($live_cats_res['body'], true) ?: [];
    $live_genres = [];
    if (is_array($live_cats)) {
        foreach ($live_cats as $cat) {
            if (isset($cat['category_name'])) {
                $live_genres[] = $cat['category_name'];
            }
        }
    }

    echo json_encode([
        'id' => 'org.community.xcpro',
        'name' => 'XC PRO',
        'version' => '1.0.0',
        'description' => 'XC PRO Addon para Stremio - Integração com servidores Xtream Codes.',
        'logo' => 'https://i.pinimg.com/474x/55/e6/b4/55e6b4246fa6fe1ac7846b94ee7da798.jpg',
        'idPrefixes' => ['xcpro'],
        'types' => ['movie', 'series', 'tv'],
        'resources' => ['catalog', 'meta', 'stream'],
        'behaviorHints' => [
            'configurable' => true,
            'configurationRequired' => false
        ],
        'catalogs' => [
            [
                'id' => 'xcpro_movie',
                'type' => 'movie',
                'name' => 'XC PRO - Filmes',
                'extra' => [
                    ['name' => 'genre', 'isRequired' => false, 'options' => $movie_genres],
                    ['name' => 'search', 'isRequired' => false]
                ]
            ],
            [
                'id' => 'xcpro_series',
                'type' => 'series',
                'name' => 'XC PRO - Séries',
                'extra' => [
                    ['name' => 'genre', 'isRequired' => false, 'options' => $series_genres],
                    ['name' => 'search', 'isRequired' => false]
                ]
            ],
            [
                'id' => 'xcpro_tv',
                'type' => 'tv',
                'name' => 'XC PRO - Canais de TV',
                'extra' => [
                    ['name' => 'genre', 'isRequired' => false, 'options' => $live_genres],
                    ['name' => 'search', 'isRequired' => false]
                ]
            ]
        ]
    ]);
    exit;
}

// 3. CATALOG ACTION
if ($action === 'catalog') {
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $genre = isset($_GET['genre']) ? $_GET['genre'] : '';
    
    // Decodificar completamente o genre (para casos como %E2%9C%A8 que é um emoji)
    $genre = urldecode($genre);
    $genre = rawurldecode($genre);

    // LOG: Registrar o genre recebido
    debug_log("=== CATALOG REQUEST ===");
    debug_log("Type: $type");
    debug_log("Genre recebido da URL: " . $genre);
    debug_log("Genre original (GET): " . ($_GET['genre'] ?? 'null'));    
    
    $stream_action = ($type === 'movie') ? 'get_vod_streams' : (($type === 'series') ? 'get_series' : 'get_live_streams');
    $category_action = ($type === 'movie') ? 'get_vod_categories' : (($type === 'series') ? 'get_series_categories' : 'get_live_categories');
    
    $metas = [];

    if (!empty($search)) {
        $res = make_curl_request("$host/player_api.php", [
            'username' => $username,
            'password' => $password,
            'action' => $stream_action
        ]);
        $items = json_decode($res['body'], true) ?: [];
        $cleaned_query = clean_for_search($search);
        
        $count = 0;
        foreach ($items as $item) {
            if ($count >= 65) break; 
            $name = isset($item['name']) ? $item['name'] : (isset($item['title']) ? $item['title'] : '');
            if (strpos(clean_for_search($name), $cleaned_query) !== false) {
                $metas[] = map_item($type, $item);
                $count++;
            }
        }
    } 
    else if (!empty($genre)) {
        $cats_res = make_curl_request("$host/player_api.php", [
            'username' => $username,
            'password' => $password,
            'action' => $category_action
        ]);
        $cats = json_decode($cats_res['body'], true) ?: [];
        $cat_id = null;
        
        if (is_array($cats)) {
            // LOG: Listar todas as categorias disponíveis
            $all_categories = [];
            foreach ($cats as $cat) {
                $cat_name = isset($cat['category_name']) ? $cat['category_name'] : '';
                $all_categories[] = [
                    'id' => $cat['category_id'] ?? 'N/A',
                    'name' => $cat_name
                ];
            }
            debug_log("Todas as categorias disponíveis:", $all_categories);            
            foreach ($cats as $cat) {
                // funcao  para comparar genre
                $cat_name = isset($cat['category_name']) ? $cat['category_name'] : '';
                
                // Comparação exata com o nome original (já decodificado)
                if ($cat_name === $genre) {
                    $cat_id = $cat['category_id'];
                    break;
                }
                // Comparação sem case sensitive
                if (strtolower($cat_name) === strtolower($genre)) {
                    $cat_id = $cat['category_id'];
                    break;
                }
                // Comparação ignorando acentos
                if (clean_for_search($cat_name) === clean_for_search($genre)) {
                    $cat_id = $cat['category_id'];
                    break;
                }
            }
        }

        if ($cat_id !== null) {
            $streams_res = make_curl_request("$host/player_api.php", [
                'username' => $username,
                'password' => $password,
                'action' => $stream_action,
                'category_id' => $cat_id
            ]);
            $items = json_decode($streams_res['body'], true) ?: [];
            if (is_array($items)) {
                foreach ($items as $item) {
                    $metas[] = map_item($type, $item);
                }
            }
        }
    } 
    else {
        $cats_res = make_curl_request("$host/player_api.php", [
            'username' => $username,
            'password' => $password,
            'action' => $category_action
        ]);
        $cats = json_decode($cats_res['body'], true) ?: [];
        $first_cat = null;
        if (is_array($cats) && count($cats) > 0) {
            $first_cat = $cats[0]['category_id'];
        }

        if ($first_cat !== null) {
            $streams_res = make_curl_request("$host/player_api.php", [
                'username' => $username,
                'password' => $password,
                'action' => $stream_action,
                'category_id' => $first_cat
            ]);
            $items = json_decode($streams_res['body'], true) ?: [];
            if (is_array($items)) {
                foreach ($items as $item) {
                    $metas[] = map_item($type, $item);
                }
            }
        }
    }

    echo json_encode(['metas' => $metas]);
    exit;
}

// 4. META ACTION
if ($action === 'meta') {   
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    
    debug_log("=== META ACTION ===");
    debug_log("Type: " . $type);
    debug_log("ID original (GET): " . $id);
    
    // Decodificar URL encoding
    $id = urldecode($id);
    debug_log("ID após urldecode: " . $id);
    
    // Extrair o ID numérico
    $matchedId = '';
    
    if (preg_match('/xcpro:(?:movie|series|tv):(\d+)/', $id, $matches)) {
        $matchedId = $matches[1];
        debug_log("ID via regex: " . $matchedId);
    }
    
    if (empty($matchedId)) {
        $matchedId = preg_replace('/[^0-9]/', '', $id);
        debug_log("ID via limpeza: " . $matchedId);
    }
    
    debug_log("Matched ID final: " . $matchedId);
    
    if (empty($matchedId)) {
        echo json_encode(['meta' => null]);
        exit;
    }
    
    // =============================================
    // PROCESSAR FILMES (MOVIE)
    // =============================================
    if ($type === 'movie') {
        debug_log("Processando FILME - ID: " . $matchedId);
        
        $params = [
            'username' => $username,
            'password' => $password,
            'action' => 'get_vod_info',
            'vod_id' => $matchedId
        ];
        
        $res = make_curl_request("$host/player_api.php", $params);
        $detail = json_decode($res['body'], true) ?: [];
        
        debug_log("Resposta da API do filme: " . print_r($detail, true));
        
        if (!isset($detail['info']) || empty($detail['info'])) {
            echo json_encode(['meta' => null]);
            exit;
        }
        
        $info = $detail['info'];
        
        // Poster
        $poster = '';
        if (!empty($info['cover_big'])) {
            $poster = $info['cover_big'];
        } elseif (!empty($info['cover'])) {
            $poster = $info['cover'];
        } elseif (!empty($info['movie_image'])) {
            $poster = $info['movie_image'];
        }
        
        // Background
        $background = $poster;
        if (!empty($info['backdrop_path']) && is_array($info['backdrop_path']) && !empty($info['backdrop_path'][0])) {
            $background = $info['backdrop_path'][0];
        } elseif (!empty($info['background'])) {
            $background = $info['background'];
        }
        
        // Ano
        $year = null;
        if (!empty($info['year'])) {
            $year = (int)$info['year'];
        } elseif (!empty($info['releasedate'])) {
            $year = (int)substr($info['releasedate'], 0, 4);
        } elseif (!empty($info['releaseDate'])) {
            $year = (int)substr($info['releaseDate'], 0, 4);
        }
        
        // Rating
        $rating = null;
        if (!empty($info['rating']) && is_numeric($info['rating'])) {
            $rating = (float)$info['rating'];
        } elseif (!empty($info['rating_5based']) && is_numeric($info['rating_5based'])) {
            $rating = (float)$info['rating_5based'];
        }
        
        // Gêneros
        $genres = [];
        if (!empty($info['genre'])) {
            $genres = array_map('trim', explode(',', $info['genre']));
        }
        
        // Nome
        $name = isset($info['name']) ? $info['name'] : (isset($info['title']) ? $info['title'] : 'Filme');
        
        // Descrição
        $description = isset($info['plot']) ? $info['plot'] : (isset($info['description']) ? $info['description'] : '');
        
        $meta = [
            'id' => "xcpro:movie:$matchedId",
            'type' => 'movie',
            'name' => $name,
            'description' => $description,
            'poster' => $poster,
            'background' => $background,
            'posterShape' => 'poster',
            'releaseInfo' => (string)$year,
            'year' => $year,
            'imdbRating' => $rating,
            'genres' => $genres
        ];
        
        debug_log("Meta do filme criado - Nome: " . $name);
        
        // Remover campos null ou vazios
        $meta = array_filter($meta, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        echo json_encode(['meta' => $meta], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // =============================================
    // PROCESSAR SÉRIES (SERIES)
    // =============================================
    if ($type === 'series') {
        debug_log("Processando SÉRIE - ID: " . $matchedId);
        
        $params = [
            'username' => $username,
            'password' => $password,
            'action' => 'get_series_info',
            'series_id' => $matchedId
        ];
        
        $res = make_curl_request("$host/player_api.php", $params);
        $detail = json_decode($res['body'], true) ?: [];

        if (!isset($detail['info']) || empty($detail['info'])) {
            echo json_encode(['meta' => null]);
            exit;
        }
        
        $info = $detail['info'];
        
        // Poster
        $poster = '';
        if (!empty($info['cover'])) {
            $poster = $info['cover'];
        } elseif (!empty($info['cover_big'])) {
            $poster = $info['cover_big'];
        }
        
        // Background
        $background = $poster;
        if (!empty($info['backdrop_path']) && is_array($info['backdrop_path']) && !empty($info['backdrop_path'][0])) {
            $background = $info['backdrop_path'][0];
        }
        
        // Ano
        $year = null;
        if (!empty($info['year'])) {
            $year = (int)$info['year'];
        } elseif (!empty($info['releaseDate'])) {
            $year = (int)substr($info['releaseDate'], 0, 4);
        } elseif (!empty($info['release_date'])) {
            $year = (int)substr($info['release_date'], 0, 4);
        }
        
        // Rating
        $rating = null;
        if (!empty($info['rating']) && is_numeric($info['rating'])) {
            $rating = (float)$info['rating'];
        }
        
        // Gêneros
        $genres = [];
        if (!empty($info['genre'])) {
            $genres = array_map('trim', explode(',', $info['genre']));
        }
        
        // Nome
        $name = isset($info['name']) ? $info['name'] : (isset($info['title']) ? $info['title'] : 'Série');
        
        // Descrição
        $description = isset($info['plot']) ? $info['plot'] : (isset($info['description']) ? $info['description'] : '');
        
        // Construir VIDEOS
        $videos = [];
        $episodesObj = isset($detail['episodes']) ? $detail['episodes'] : [];
        
        foreach ($episodesObj as $season_num => $episodes) {
            if (!is_array($episodes)) continue;
            
            $season = (int)$season_num;
            
            foreach ($episodes as $ep) {
                $episode_num = isset($ep['episode_num']) ? (int)$ep['episode_num'] : 0;
                
                $ep_title = isset($ep['title']) ? $ep['title'] : "Episode $episode_num";
                
                $thumbnail = '';
                if (isset($ep['info']['movie_image']) && !empty($ep['info']['movie_image'])) {
                    $thumbnail = $ep['info']['movie_image'];
                } elseif (isset($ep['info']['cover_big']) && !empty($ep['info']['cover_big'])) {
                    $thumbnail = $ep['info']['cover_big'];
                } elseif (isset($ep['info']['cover']) && !empty($ep['info']['cover'])) {
                    $thumbnail = $ep['info']['cover'];
                }
                
                $overview = isset($ep['info']['plot']) ? $ep['info']['plot'] : '';
                
                $videos[] = [
                    'id' => "xcpro:series:$matchedId:$season:$episode_num",
                    'title' => $ep_title,
                    'season' => $season,
                    'episode' => $episode_num,
                    'thumbnail' => $thumbnail,
                    'overview' => $overview
                ];
            }
        }
        
        // Ordenar vídeos
        usort($videos, function($a, $b) {
            if ($a['season'] !== $b['season']) return $a['season'] - $b['season'];
            return $a['episode'] - $b['episode'];
        });
        
        $meta = [
            'id' => "xcpro:series:$matchedId",
            'type' => 'series',
            'name' => $name,
            'description' => $description,
            'poster' => $poster,
            'background' => $background,
            'posterShape' => 'poster',
            'releaseInfo' => (string)$year,
            'year' => $year,
            'imdbRating' => $rating,
            'genres' => $genres,
            'videos' => $videos
        ];
        
        debug_log("Meta da série criado - Nome: " . $name);
        debug_log("Total de vídeos: " . count($videos));
        
        // Remover campos null ou vazios
        $meta = array_filter($meta, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        // Garantir que videos não seja vazio
        if (empty($meta['videos'])) {
            $meta['videos'] = [];
        }
        
        echo json_encode(['meta' => $meta], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // =============================================
    // PROCESSAR CANAIS (TV)
    // =============================================
    if ($type === 'tv') {
        debug_log("Processando CANAL - ID: " . $matchedId);
        
        $params = [
            'username' => $username,
            'password' => $password,
            'action' => 'get_live_streams'
        ];
        
        $res = make_curl_request("$host/player_api.php", $params);
        $streams = json_decode($res['body'], true) ?: [];
        
        $channel_info = null;
        foreach ($streams as $stream) {
            if (strval($stream['stream_id']) === strval($matchedId)) {
                $channel_info = $stream;
                break;
            }
        }
        
        if (!$channel_info) {
            echo json_encode(['meta' => null]);
            exit;
        }
        
        $poster = isset($channel_info['stream_icon']) ? $channel_info['stream_icon'] : '';
        
        $meta = [
            'id' => "xcpro:tv:$matchedId",
            'type' => 'tv',
            'name' => isset($channel_info['name']) ? $channel_info['name'] : 'Canal',
            'description' => 'XC PRO Canal ao Vivo',
            'poster' => $poster,
            'posterShape' => 'square'
        ];
        
        // Remover campos null ou vazios
        $meta = array_filter($meta, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        echo json_encode(['meta' => $meta], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // Se chegou aqui, tipo não suportado
    echo json_encode(['meta' => null]);
    exit;
}

// 5. STREAM ACTION
if ($action === 'stream') {
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    
    debug_log("=== STREAM ACTION ===");
    debug_log("Type: " . $type);
    debug_log("ID original: " . $id);
    
    // Decodificar URL encoding
    $id = urldecode($id);
    debug_log("ID após urldecode: " . $id);
    
    $parts = explode(':', $id);
    debug_log("Parts após explode: " . print_r($parts, true));
    
    // Extrair o ID da série
    $matchedId = '';
    if (isset($parts[2])) {
        $matchedId = $parts[2];
        debug_log("Matched ID from parts[2]: " . $matchedId);
    }
    
    // Se o ID veio como xcpro%3Aseries%3A337932, limpar
    if (empty($matchedId) || !is_numeric($matchedId)) {
        if (preg_match('/xcpro:(?:movie|series|tv):(\d+)/', $id, $matches)) {
            $matchedId = $matches[1];
            debug_log("ID via regex: " . $matchedId);
        }
    }
    
    // Se ainda não tem, tentar extrair números
    if (empty($matchedId)) {
        $matchedId = preg_replace('/[^0-9]/', '', $id);
        debug_log("ID via limpeza: " . $matchedId);
    }
    
    // Extrair season e episode
    $season = isset($parts[3]) ? $parts[3] : '';
    $episode = isset($parts[4]) ? $parts[4] : '';
    
    debug_log("Final - Series ID: $matchedId, Season: $season, Episode: $episode");
    
    if (empty($matchedId)) {
        echo json_encode(['streams' => []]);
        exit;
    }

    $streamUrl = '';

    if ($type === 'movie') {
        $streamUrl = "$host/movie/$username/$password/$matchedId.mp4";
    } else if ($type === 'tv') {
        $streamUrl = "$host/live/$username/$password/$matchedId.m3u8";
    } else if ($type === 'series') {
        $season = isset($parts[3]) ? $parts[3] : '';
        $episode = isset($parts[4]) ? $parts[4] : '';
        
        $res = make_curl_request("$host/player_api.php", [
            'username' => $username,
            'password' => $password,
            'action' => 'get_series_info',
            'series_id' => $matchedId
        ]);
        $detail = json_decode($res['body'], true) ?: [];
        $episodesObj = isset($detail['episodes']) ? $detail['episodes'] : [];
        $foundEpisodeId = '';

        foreach ($episodesObj as $seasonNum => $eps) {
            if (strval($seasonNum) === strval($season) && is_array($eps)) {
                foreach ($eps as $ep) {
                    if (strval($ep['episode_num']) === strval($episode)) {
                        $foundEpisodeId = $ep['id'];
                        break 2;
                    }
                }
            }
        }

        if (!empty($foundEpisodeId)) {
            $streamUrl = "$host/series/$username/$password/$foundEpisodeId.mp4";
        } else {
            $streamUrl = "$host/series/$username/$password/$matchedId.mp4";
        }
    }

    $streams = [];
    if (!empty($streamUrl)) {
        $title = ($type === 'tv') ? 'Canais ao Vivo (Direto)' : (($type === 'series') ? 'Série (Direto)' : 'Filme (Direto)');
        
        if (!empty($f4mProxy)) {
            $title .= ' (Via F4M Proxy)';
            $streamUrl = "$f4mProxy/?url=" . urlencode($streamUrl);
        }

        $streams[] = [
            'name' => 'XC PRO',
            'title' => $title,
            'url' => $streamUrl
        ];
    }

    echo json_encode(['streams' => $streams]);
    exit;
}

// Se chegou aqui, algo deu errado
http_response_code(404);
echo json_encode(['error' => 'Rota não encontrada']);