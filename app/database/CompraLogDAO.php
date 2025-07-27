<?php
    namespace App\database;

    use App\database\Querys;

    class CompraLogDAO extends Querys {
        protected string $table = "logs_compras";
        protected array $columns = [
            'compra_id' => '',
            'acao' => '',
            'observacoes' => ''
        ];

        public function initReserva($data) {
            $this->columns = [
                'compra_id' => null,
                'acao' => 'Compra iniciada com reserva',
                'observacoes' => 'Produto de ID ' . $data['id_produto'] . ' reservado para o cliente.'
            ];

            return $this->insert();
        }

        public function initCompra($data) {
            $this->columns = [
                'compra_id' => null,
                'acao' => 'Compra iniciada',
                'observacoes' => 'Cliente com o produto de ID' . $data['id_produto'] . ' iniciou a compra.'
            ];

            return $this->insert();
        }


        public function closeCompra($data) {
            $this->columns = [
                'compra_id' => $data['id_compra'],
                'acao' => 'Compra finalizada',
                'observacoes' => 'Cliente com o produto de ID' . $data['id_produto'] . ' finalizou a compra.'
            ];

            return $this->insert();
        }
    }



?>
