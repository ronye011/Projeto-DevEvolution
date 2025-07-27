<?php
    namespace App\database;

    use App\database\Querys;
    use PDO;

    class ProdutosDAO extends Querys {
        protected string $table = "produtos";
        protected array $columns = [
            'nome' => '',
            'descricao' => '',
            'preco' => '',
            'quantidade' => '',
            'reservado' => 0,
            'data_reserva' => '',
            'usuario_id' => ''
        ];

        public function __construct() {
        }

        public function novo(array $data) {
            if(!$_SESSION['id_user']) {
                session_start();
            }
            $this->columns = [
                'nome' => (string) $data['nome'],
                'descricao' => isset($data['descricao']) ? $data['descricao'] : null,
                'preco' => (float) $data['preco'],
                'quantidade' => (int) $data['quantidade'],
                'data_reserva' => 00000000000000,
                'usuario_id' => $_SESSION['id_user']
            ];

            return $this->insert();
        }

        public function salvar(array $data) {
            $this->id = $data['id'];

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $this->columns = [
                'nome' => (string) $data['nome'],
                'descricao' => isset($data['descricao']) ? $data['descricao'] : null,
                'preco' => (float) $data['preco'],
                'quantidade' => (int) $data['quantidade']
            ];

            return $this->update();
        }

        protected function upReserva(): bool {
            $this->pdo = Connection::Connect();

            $stmt = $this->pdo->prepare("
                UPDATE {$this->table}
                SET data_reserva = ?, token = ?
                WHERE id = ? AND (
                    data_reserva IS NULL OR 
                    datetime(data_reserva) <= datetime('now', '-2 minutes')
                )
            ");

            $values = [
                $this->columns['data_reserva'],
                $this->columns['token'],
                $this->id
            ];

            $stmt->execute($values);

            // Verifica se realmente foi atualizado
            return $stmt->rowCount() > 0;
        }

        public function reservar($data) {
            $this->id = (int) $data['id'];

            $this->columns = [
                'data_reserva' => (new \DateTime())->format('Y-m-d H:i:s'),
                'token' => $data['token']
            ];

            return $this->upReserva();
        }

        public function editar($data) {
            $this->id = (int) $data;
            $produto = $this->selectByIDUser();
            if($produto && isset($produto[0])) {
                $produto = $produto[0];
                return [
                    'id' => $this->id,
                    'nome' => $produto['nome'],
                    'descricao' => $produto['descricao'],
                    'preco' => $produto['preco'],
                    'quantidade' => $produto['quantidade']
                ];
            }
            return false;
        }

        public function editarCentral($data) {
            $this->id = (int) $data;
            $produto = $this->selectByID();
            if($produto && isset($produto[0])) {
                $produto = $produto[0];
                return [
                    'id' => $this->id,
                    'nome' => $produto['nome'],
                    'descricao' => $produto['descricao'],
                    'preco' => $produto['preco'],
                    'quantidade' => $produto['quantidade'],
                    'data_reserva' => $produto['data_reserva'],
                    'usuario_id' => $produto['usuario_id']
                ];
            }
            return false;
        }

        public function deletar($id) {
            $this->id = (int) $id;
            return $this->delete();
        }

        public function getDataGrid($data) {
            $offset = ((int) $data['current'] - 1) * 16;

            $query = "SELECT id, nome, descricao, preco, quantidade FROM {$this->table} ";

            $paraFilter = "";
            if($data['valor'] && $data['valor'] != null) {
                $paraFilter = '%' . (string) $data['valor'] . '%';
                $query .= "WHERE nome LIKE ?";
            }

            $query .= "LIMIT 16 OFFSET ?";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);

            if($data['valor'] && $data['valor'] != null) {
                $stmt->execute([$paraFilter, $offset]);
            } else {
                $stmt->execute([$offset]);
            }

            $countQuery = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt2 = $this->pdo->prepare($countQuery);
            $stmt2->execute();
            $countResult = $stmt2->fetch(PDO::FETCH_ASSOC);

            return [
                'current' => (int) $data['current'],
                'total' => ceil($countResult['total'] / 16),
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        }

        public function decrementar($data) {
            $this->id = $data['id'];
            $produto = $this->editarCentral($this->id);

            $this->columns = [
                'quantidade' => (int) $produto['quantidade'] - 1
            ];

            return $this->update();
        }
    }
?>