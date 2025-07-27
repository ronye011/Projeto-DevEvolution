<?php
    namespace App\controllers;
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    use App\database\UsuarioDAO;

    class Login {
        private $data = [];

        public function __construct($data) {
            $this->data = $data;
        }

        public function access() {
            if (!isset($this->data['email']) || empty(trim($this->data['email'])) || !isset($this->data['password']) || empty(trim($this->data['password']))) {
                return ['status' => 'info', 'message' => 'O campo e-mail e senha é obrigatório.'];
            } else if (!filter_var(trim($this->data['email']), FILTER_VALIDATE_EMAIL)) {
                return ['status' => 'error', 'message' => 'E-mail inválido.'];
            }

            $Database = new UsuarioDAO;
            $UpdatePassword = hash_hmac("sha256", $this->data['password'], "localhost");
            $this->data['password'] = $UpdatePassword;
            $user = $Database->findUserByEmail($this->data['email']);
            if ($user && password_verify($this->data['password'], $user['senha']) && $user['status'] === 0) {
                 // Tempo maximo da sessão
                $maxSessionTime = 30 * 60;
                session_set_cookie_params($maxSessionTime);
                
                session_start();
                $_SESSION['id_user'] = $user['id'];

                // Gera um token para permitir a sessão
                $token = bin2hex(random_bytes(32));
                $Database->salvarToken($token, $_SESSION['id_user']);
                $_SESSION['token'] = $token;
                $_SESSION['initSessionDate'] = time();
                return ['status' => 'success', 'message' => 'Sucesso'];
            } else {
                return ['status' => 'error', 'message' => 'E-mail ou senha inválido.'];
            }
        }

        public function exit() {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            if (isset($_SESSION['id_user'])) {

                // Gera outro token para invalidar a sessão
                $Database = new UsuarioDAO;
                $token = bin2hex(random_bytes(32));
                $Database->salvarToken($token, $_SESSION['id_user']);
                $_SESSION = []; // limpa a sessão
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }

                session_destroy();

                return ['status' => 'success', 'message' => 'OK!'];
            } else {
                return ['status' => 'error', 'message' => 'ERRO! Usuario não logado!'];
            }
        }

        public function valid() {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['id_user'], $_SESSION['initSessionDate'], $_SESSION['token'])) {
                $sessionStart = $_SESSION['initSessionDate'];
                $now = time();
                $time = $now - $sessionStart;

                // Tempo máximo da sessão: 30 minutos
                $maxSessionTime = 30 * 60;

                if ($time >= $maxSessionTime) {
                    http_response_code(401);
                    return [
                        'status' => 'error',
                        'message' => 'Sessão expirada.'
                    ];
                }

                $Database = new UsuarioDAO;
                $user = $Database->findUserByToken($_SESSION['token']);

                if (!$user || $user['status'] != 0) {
                    http_response_code(401);
                    return [
                        'status' => 'error',
                        'message' => 'Usuário inválido ou inativo.'
                    ];
                }


                return [
                    'status' => 'success',
                    'message' => 'Sessão válida.'
                ];
            }

            // Sessão inválida
            http_response_code(401);
            return [
                'status' => 'error',
                'message' => 'Sessão inválida.'
            ];
        }

        public function user() {
            session_start();
            if (isset($_SESSION['id_user'])) {
                $Database = new UsuarioDAO;
                $user = $Database->findUserByToken($_SESSION['token']);
                return ['status' => 'success', 'message' => $user['nome']];
            }

            // Sessão inválida
            http_response_code(401);
            return [
                'status' => 'error',
                'message' => 'Sessão expirada ou inválida.'
            ];
        }
    }
?>