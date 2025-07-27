<?php
    //Decidi por fazer a rota de login separada para pode tratar melhor o que é feito com informações com authenticação e sem authenticação
    require_once __DIR__ . '/../../vendor/autoload.php';
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    use App;

    // Rota
    $route = isset($_GET['route']) ? $_GET['route'] : '';

    //Rotas permitidas
    $routes_allowed = [
        ['route' => 'Login/Access', 'controller' => 'controllers\\Login', 'method' => 'access'],
        ['route' => 'Login/Valid', 'controller' => 'controllers\\Login', 'method' => 'valid'],
        ['route' => 'Login/NameUser', 'controller' => 'controllers\\Login', 'method' => 'user'],
        ['route' => 'Produtos/getProdutos', 'controller' => 'controllers\\Produtos', 'method' => 'getProdutos'],
        ['route' => 'Produtos/editar', 'controller' => 'controllers\\Produtos', 'method' => 'editarCentral'],
        ['route' => 'Cupons/getCupom', 'controller' => 'controllers\\Cupons', 'method' => 'getCupom'],
        ['route' => 'Compra/finalizar', 'controller' => 'controllers\\Compra', 'method' => 'closeCompra']
    ];

    // Na minha arquitura as requisições são separadas por tela e função, logo se a aplicação receber mais de dois argumentos o sistema identifica como rota invalida
    $parts = explode('/', $route);
    if (count($parts) !== 2) {
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

    // Recebe JSON
    $data = json_decode(file_get_contents('php://input'), true);

    // Cria o objeto
    $fullController = "App\\$controllerName";
    $controller = new $fullController($data);

    // Executa o método e retorna a resposta
    try {
        $result = $controller->$methodName();
        echo json_encode($result);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>