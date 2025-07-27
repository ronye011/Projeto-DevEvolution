<?php
    namespace App\database;

    use App\database\Querys;
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    class CuponsDAO extends Querys {
        protected string $table = "cupons";
        protected array $columns = [
            'codigo' => '',
            'valor_desconto' => '',
            'tipo_desconto' => '',
            'validade' => '',
            'usuario_id' => '',
            'usado' => 0
        ];

        public function __construct() {
        }

        public function novo(array $data) {
            if(!$_SESSION['id_user']) {
                session_start();
            }
            $this->columns = [
                'codigo' => (string) $data['codigo'],
                'valor_desconto' => (float) $data['valor_desconto'],
                'tipo_desconto' => (string) $data['tipo_desconto'],
                'validade' => (string) $data['validade'],
                'usuario_id' => $_SESSION['id_user'],
                'usado' => 0
            ];

            return $this->insert();
        }

        public function findCupomByCodigo($codigo) {
            $query = "SELECT * FROM {$this->table} WHERE codigo = ?";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([(string) $codigo]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        public function salvar(array $data) {
            $this->id = (int) $data['id'];

            $this->columns = [
                'codigo' => (string) $data['codigo'],
                'valor_desconto' => (float) $data['valor_desconto'],
                'tipo_desconto' => (string) $data['tipo_desconto'],
                'validade' => (string) $data['validade'],
            ];

            return $this->update();

        }

        public function editar($data) {
            $this->id = (int) $data;
            $cupom = $this->selectByIDUser();
            if($cupom && isset($cupom[0])) {
                $cupom = $cupom[0];
                return [
                    'id' => $this->id,
                    'codigo' => $cupom['codigo'],
                    'valor_desconto' => $cupom['valor_desconto'],
                    'validade' => $cupom['validade'],
                    'usado' => $cupom['usado'],
                    'tipo_desconto' => $cupom['tipo_desconto'],
                    'usuario_id' => $cupom['usuario_id'],
                ];
            }
            return false;
        }

        public function inutilizar($data) {
            $this->id = (int) $data;

            $this->columns = [
                'usado' => 1
            ];

            return $this->update();
        }
    }
?>