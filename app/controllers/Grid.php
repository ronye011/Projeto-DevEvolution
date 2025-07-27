<?php
namespace App\controllers;

use App\database\GridDAO;
use Exception;

class Grid {
    private $requestData;

    public function __construct(array $requestData) {
        $this->requestData = $requestData;
    }

    public function getDataGrid() {
        if (!isset($this->requestData['table'], $this->requestData['filtro'], $this->requestData['page'], $this->requestData['totalItensForPage'])) {
            return ['status' => 'error', 'message' => 'Parâmetros obrigatórios ausentes.'];
        }

        $config = $this->getTableConfig($this->requestData['table']);

        if (!$config) {
            return ['status' => 'error', 'message' => 'Tabela não suportada.'];
        }

        $validFilters = $config['filters'];

        if (!array_key_exists($this->requestData['filtro'], $validFilters)) {
            return ['status' => 'error', 'message' => 'Filtro inválido para esta tabela.'];
        }

        try {
            $result = GridDAO::getDataGrid(
                $this->requestData['filtro'],
                $this->requestData['searchTerm'],
                $this->requestData['table'],
                $validFilters,
                $this->requestData['totalItensForPage'],
                $this->requestData['page']
            );

            return ['status' => 'success', 'result' => $result];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Replicação das colunas da grid no codigo para facilitar a escalibilidade
    private function getTableConfig(string $table): ?array {
        $configs = [
            'usuarios' => [
                // Essa relação chave valor tem haver com o caso de onde tiver alguma alteração na forma que os dados são retornados do front-end
                'filters' => [
                    'id' => 'id',
                    'nome' => 'nome',
                    'email' => 'email',
                    'status' => 'status'
                ]
            ],
            'produtos' => [
                'filters' => [
                    'id' => 'id',
                    'nome' => 'nome',
                    'preco' => 'preco',
                    'quantidade' => 'quantidade'
                ]
            ],
            'cupons' => [
                'filters' => [
                    'id' => 'id',
                    'codigo' => 'codigo',
                    'valor_desconto' => 'valor_desconto',
                    'tipo_desconto' => 'tipo_desconto',
                    'validade' => 'validade',
                    'usado' => 'usado'
                ]
            ],
            'logs_compras' => [
                'filters' => [
                    'id' => 'id',
                    'compra_id' => 'compra_id',
                    'acao' => 'acao',
                    'data_log' => 'data_log'
                ]
            ],
            'compras' => [
                'filters' => [
                    'id' => 'ID',
                    'cliente_id' => 'cliente_id',
                    'produto_id' => 'produto_id',
                    'data_compra' => 'data_compra'
                ]
            ],
        ];

        return $configs[$table] ?? null;
    }
}
