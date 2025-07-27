<?php
    namespace App\database;

    use App\database\Querys;
    use PDO;
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    class ClienteDAO extends Querys {
        protected string $table = "clientes";
        protected array $columns = [
            'nome' => '',
            'email' => '',
            'telefone' => ''
        ];

        public function __construct() {
        }

        public function novo(array $data) {
            $this->columns = [
                'nome' => (string) $data['nome'],
                'email' => (string) $data['email'],
                'telefone' => (int) isset($data['telefone']) ? $data['telefone'] : null
            ];

            return $this->insert();
        }

        public function findClienteByEmail($email) {
            $query = "SELECT * FROM {$this->table} WHERE email = ?;";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$email]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function salvar(array $data) {
            $this->id = (int) $data['id'];

            $this->columns = [
                'nome' => (string) $data['nome'],
                'email' => (string) $data['email'],
                'telefone' => (int) isset($data['telefone']) ? $data['telefone'] : null
            ];

            return $this->update();

        }

        public function editar($data) {
            $this->id = (int) $data;
            $compra = $this->selectByID();
            if($compra && isset($compra[0])) {
                $compra = $compra[0];
                return [
                    'nome' => $compra['nome'],
                    'email' => $compra['email'],
                    'telefone' => $compra['telefone']
                ];
            }
        }
    }
?>