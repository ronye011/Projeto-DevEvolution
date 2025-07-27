<?php
    namespace App\database;

    use App\database\Querys;

    class CompraDAO extends Querys {
        protected string $table = "compras";
        protected array $columns = [
            'cep' => '',
            'rua' => '',
            'numero' => '',
            'complemento' => '',
            'cliente_id' => '',
            'produto_id' => '',
            'quantidade' => 0,
            'data_compra' => '',
            'usuario_id' => '',
            'valor' => ''
        ];

        public function novo(array $data) {
            $this->columns = [
                'cep' => (int) $data['cep'],
                'complemento' => isset($data['complemento']) ? $data['complemento'] : null,
                'numero' => (int) $data['numero'],
                'rua' => (string) $data['rua'],
                'cliente_id' => (int) $data['cliente'],
                'produto_id' => $data['produto'],
                'quantidade' => 1,
                'data_compra' => date('Y-m-d H:i:s'),
                'usuario_id' => $data['usuario_id'],
                'valor' => $data['valor']
            ];

            return $this->insert();
        }

        public function selectByProduto($data) {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE produto_id = ?;";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([(int) $data]);
            return (int) $stmt->fetchColumn();
        }

        public function editar($data) {
            $this->id = (int) $data;
            $compra = $this->selectByIDUser();
            if($compra && isset($compra[0])) {
                $compra = $compra[0];
                return [
                    'id' => $this->id,
                    'cep' => $compra['cep'],
                    'rua' => $compra['rua'],
                    'numero' => $compra['numero'],
                    'complemento' => $compra['complemento'],
                    'cliente_id' => $compra['cliente_id'],
                    'produto_id' => $compra['produto_id'],
                    'quantidade' => $compra['quantidade'],
                    'data_compra' => $compra['data_compra'],
                    'usuario_id' => $compra['usuario_id'],
                    'valor' => $compra['valor']
                ];
            }
        }
    }
?>