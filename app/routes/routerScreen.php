<?php
    //Rotas para as telas do sistema
    require_once __DIR__ . '/../../vendor/autoload.php';
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    use App\controllers\Login;

    // Rota
    $route = isset($_GET['route']) ? $_GET['route'] : '';

    //Rotas permitidas
    $routes_allowed = [
        ['route' => 'produtos', 'controller' => 'controllers\\Screens', 'method' => 'produtos'],
        ['route' => 'cupons', 'controller' => 'controllers\\Screens', 'method' => 'cupons'],
        ['route' => 'compras', 'controller' => 'controllers\\Screens', 'method' => 'compras'],
        ['route' => 'usuarios', 'controller' => 'controllers\\Screens', 'method' => 'usuarios'],
        ['route' => 'logs_compras', 'controller' => 'controllers\\Screens', 'method' => 'logs_compras']
    ];

    $parts = explode('/', $route);
    if (count($parts) !== 1) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Rota inválida.']);
        return;
    }

    $route_valid = false;
    foreach ($routes_allowed as $allowed) {
        if ($allowed['route'] === $route) {
            $route_valid = true;
            $controllerName = $allowed['controller'];
            $methodName = $allowed['method'];
            break;
        }
    }

    if (!$route_valid) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Rota não permitida.']);
        return;
    }

    $login = new Login([]);

    if($login->valid()['status'] != "success") {
        echo json_encode(['status' => 'error', 'message' => 'Usuario não logado']);
        return;
    }

    // Cria o objeto
    $fullController = "App\\$controllerName";
    $controller = new $fullController();

    // Executa o método e retorna a resposta
    try {
        $result = $controller->$methodName();
        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>