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
        ['route' => 'grid/getDataGrid', 'controller' => 'controllers\\Grid', 'method' => 'getDataGrid'],
        ['route' => 'produtos/salvar', 'controller' => 'controllers\\Produtos', 'method' => 'salvar'],
        ['route' => 'produtos/editar', 'controller' => 'controllers\\Produtos', 'method' => 'editar'],
        ['route' => 'produtos/deletar', 'controller' => 'controllers\\Produtos', 'method' => 'deletar'],
        ['route' => 'cupons/salvar', 'controller' => 'controllers\\Cupons', 'method' => 'salvar'],
        ['route' => 'cupons/editar', 'controller' => 'controllers\\Cupons', 'method' => 'editar'],
        ['route' => 'cupons/inutilizar', 'controller' => 'controllers\\Cupons', 'method' => 'inutilizar'],
        ['route' => 'usuarios/salvar', 'controller' => 'controllers\\Usuarios', 'method' => 'salvar'],
        ['route' => 'usuarios/editar', 'controller' => 'controllers\\Usuarios', 'method' => 'editar'],
        ['route' => 'usuarios/status', 'controller' => 'controllers\\Usuarios', 'method' => 'status'],
        ['route' => 'logs_compras/visualizar', 'controller' => 'controllers\\Logs', 'method' => 'visualizar'],
        ['route' => 'login/exit', 'controller' => 'controllers\\Login', 'method' => 'exit'],
        ['route' => 'compras/imprimir', 'controller' => 'controllers\\Compra', 'method' => 'comprovante']
    ];

    $parts = explode('/', $route);
    if (count($parts) !== 2) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Rota inválida.']);
        return;
    }

    $login = new Login([]);

    if($login->valid()['status'] != "success") {
        echo json_encode(['status' => 'error', 'message' => 'Usuario não logado']);
        return;
    }

    if ($route === 'compras/imprimir') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID inválido";
            exit;
        }

        $compra = new \App\controllers\Compra(['id' => $id]);
        $compra->comprovante(); // Gera o PDF e exibe
        exit;
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

    // Recebe JSON
    $data = json_decode(file_get_contents('php://input'), true);

    // Cria o objeto
    $fullController = "App\\$controllerName";
    $controller = new $fullController($data);

    // Executa o método e retorna a resposta
    try {
        $result = $controller->$methodName();
        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>