<?php
    namespace App\database;

    use App\database\Querys;

    class UsuarioDAO extends Querys {
        protected string $table = "usuarios";
        protected array $columns = [
            'nome' => '',
            'email' => '',
            'senha' => '',
            'status' => 0
        ];

        public function __construct() {
        }

        public function salvarToken($token, $id) {
            $this->id = (int) $id;

            $this->columns = [
                'token' => (string) $token,
            ];

            return $this->update();
        }

        public function findUserByToken($token) {
            $query = "SELECT * FROM {$this->table} WHERE token = ?";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$token]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        public function getUserName() {
            $query = "SELECT nome FROM {$this->table} WHERE id = ?";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$_SESSION['id_user']]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        public function findUserByEmail($Email) {
            $query = "SELECT * FROM {$this->table} WHERE email = ?";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$Email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        public function novo(array $data) {
            $this->columns = [
                'nome' => (string) $data['nome'],
                'email' => (string) $data['email'],
                'senha' => (string) $data['senha'],
                'status' => 0
            ];

            return $this->insert();
        }

        public function salvar(array $data) {
            $this->id = (int) $data['id'];

            $this->columns = [
                'nome' => (string) $data['nome'],
                'email' => (string) $data['email'],
                'senha' => (string) $data['senha'],
            ];

            return $this->update();
        }

        public function status($data) {
            $this->id = (int) $data;
            $user = $this->selectByID();
            if($user && isset($user[0])) {
                $user = $user[0];
                if($user['status'] == 0) {
                    $user['status'] = 1;
                } else {
                    $user['status'] = 0;
                }
                $this->columns = [
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                    'senha' => $user['senha'],
                    'status' => $user['status']
                ];
                return $this->update();
            }
            return false;
        }

        public function editar($data) {
            $this->id = $data;
            $user = $this->selectByID();
            if($user && isset($user[0])) {
                $user = $user[0];
                return [
                    'id' => $this->id,
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                    'senha' => $user['senha'],
                    'status' => $user['status']
                ];
            }
            return false;
        }
    }
?>