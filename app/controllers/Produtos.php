<?php
    namespace App\controllers;
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    use App\database\ProdutosDAO;
    use App\database\CompraLogDAO;
    use App\database\CompraDAO;

    class Produtos {
        private $values = [];

        public function __construct($data) {
            $this->values = $data;
        }

        public function editar() {
            if(!empty($this->values['id'])) {
                if(is_numeric($this->values['id'])) {
                    $Database = new ProdutosDAO;
                    $result = $Database->editar($this->values['id']);
                    if ($result) {
                        return ['status' => 'success', 'message' => $result];
                    } else {
                        return ['status' => 'error', 'message' => 'Nenhum resultado encontrado!'];
                    }
                }
            }
            return ['status' => 'error', 'message' => 'Informe um id valido!'];
        }

        public function editarCentral() {

            if(!empty($this->values['id'])) {

                if(is_numeric($this->values['id'])) {

                    $Database = new ProdutosDAO;
                    $result = $Database->editarCentral($this->values['id']);
                    $DatabaseLog = new CompraLogDAO();

                    if ($result) {
                        if ($result['quantidade'] == 1) {
                            if (!is_null($result['data_reserva']) && !empty($result['data_reserva']) && strtotime($result['data_reserva']) !== false) {

                                $dataAtual = new \DateTime();
                                $dataReserva = new \DateTime($result['data_reserva']);

                                // Soma 2 minutos à data da reserva
                                $dataReserva->add(new \DateInterval('PT2M'));

                                // Compara com o tempo atual
                                if ($dataAtual >= $dataReserva) {
                                    // Token de reserva
                                    $token = bin2hex(random_bytes(32));

                                    if (session_status() === PHP_SESSION_NONE) {
                                        session_start();
                                    }


                                    $_SESSION['token'] = $token;
                                    $data = [
                                        'id' => $this->values['id'],
                                        'token' => $token
                                    ];
                                    $Database->reservar($data);
                                    $data['id_produto'] = $result['id'];
                                    $DatabaseLog->initReserva($data);
                                    $DatabaseLog->initCompra($data);
                                    $result = $Database->editarCentral($this->values['id']);

                                    return ['status' => 'success', 'message' => ['message' => 'Produto localizado!', 'data' => $result]];

                                } else {
                                    return ['status' => 'error', 'message' => 'Aguarde 2 minutos, alguêm reservou o produto!'];
                                }
                            } else {
                                // Token de reserva
                                $token = bin2hex(random_bytes(32));

                                if (session_status() === PHP_SESSION_NONE) {
                                    session_start();
                                }


                                $_SESSION['token'] = $token;
                                $data = [
                                    'id' => $this->values['id'],
                                    'token' => $token
                                ];
                                $Database->reservar($data);
                                $data['id_produto'] = $result['id'];
                                $DatabaseLog->initReserva($data);
                                $result = $Database->editarCentral($this->values['id']);
                                return ['status' => 'success', 'message' => ['message' => 'Produto localizado!', 'data' => $result]];
                            }
                        } else if ($result['quantidade'] > 1) {
                            $data['id_produto'] = $result['id'];
                            $DatabaseLog->initCompra($data);
                            return ['status' => 'success', 'message' => ['message' => 'Produto localizado!', 'data' => $result]];
                        } else {
                            return ['status' => 'error', 'message' => 'Produto sem estoque!'];
                        }
                    } else {
                        return ['status' => 'error', 'message' => 'Nenhum resultado encontrado!'];
                    }
                }
            }
            return ['status' => 'error', 'message' => 'Informe um id valido!'];
        }

        public function salvar() {

            if (!empty($this->values['nome']) && !empty($this->values['preco']) && !empty($this->values['quantidade'])) {

                $this->values['preco'] = str_replace(',', '.', $this->values['preco']);

                if (filter_var($this->values['preco'], FILTER_VALIDATE_FLOAT) !== false && filter_var($this->values['quantidade'], FILTER_VALIDATE_INT) !== false) {
                    
                    $Database = new ProdutosDAO();

                    if(empty($this->values['id'])) {

                        $id = $Database->novo($this->values);

                        if($id) {

                            return ['status' => 'success', 'message' => ['message' => 'Produto/Ingresso criado com sucesso!', 'data' => ['id' => $id]]];
                        } else {

                            return ['status' => 'error', 'message' => 'Erro ao processar!'];
                        }
                    }
                    else {

                        if (is_numeric($this->values['id'])) {

                            $produto = $Database->editar($this->values['id']);

                            if ($produto) {

                                if($Database->salvar($this->values)) {

                                    return ['status' => 'success', 'message' => 'Produto/Ingresso atualizado com sucesso!'];
                                } else {
                                    return ['status' => 'error', 'message' => 'Erro ao processar!'];
                                }
                            } else {
                                return ['status' => 'error', 'message' => 'Produto inexistente!'];
                            }
                        } else {
                            return ['status' => 'error', 'message' => 'ID invalido!'];
                        }
                    }
                }
            }
            return ['status' => 'error', 'message' => 'Dados obrigatórios em branco ou inválidos!'];
        }

        public function deletar() {
            if(!empty($this->values['id'])) {
                if(is_numeric($this->values['id'])) {
                    $Database = new ProdutosDAO();
                    $Database2 = new CompraDAO();
                    if ($Database2->selectByProduto($this->values['id']) > 0) {
                        return ['status' => 'error', 'message' => 'Existem compras vinculadas a este produto!'];
                    }

                    if ($Database->deletar($this->values['id'])) {
                        return ['status' => 'success', 'message' => 'Produto deletado com sucesso!'];
                    }
                }
            }
        }

        public function getProdutos() {
            if (!is_int($this->values['total']) && !is_int($this->values['current'])) {
                return ['status' => 'error', 'message' => 'Dados não inteiros para grid'];
            }
            $Database = new ProdutosDAO();
            $produtos = $Database->getDataGrid($this->values);

            if ($produtos) {
                $result = '';
                foreach ($produtos['data'] as $data) {
                    $nome = mb_strimwidth($data['nome'], 0, 24, '...');
                    $descricao = mb_strimwidth($data['descricao'], 0, 97, '...');
                    $valor = mb_strimwidth($data['preco'], 0, 7, '...');
                    $valor = number_format($data['preco'], 2, ',', '.');
                    $estoque = ($data['quantidade'] > 0) ? 'Em estoque' : 'Produto indisponível';
                    $classEstoque = ($data['quantidade'] > 0) ? 'stock' : 'stockNot';
                    $result .= '
                    <div class="card" data-id=' . $data['id'] . '>
                        <div class="tilt">
                            <div class="img"><img src="../app/assets/img/system/Produto.svg" alt="Premium Laptop"></div>
                        </div>
                        <div class="info">
                            <h2 class="title">' . htmlspecialchars($nome) . '</h2>
                            <p class="desc">' . htmlspecialchars($descricao) . '</p>
                        <div class="bottom">
                        <div class="price">
                            <span>R$' . htmlspecialchars($valor) . '</span>
                        </div>
                        <button class="btn" data="comprar">
                            <span>Comprar</span>
                            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                            </svg>
                        </button>
                        </div>
                        <div class="meta">
                        <div class="' . htmlspecialchars($classEstoque) . '">' . htmlspecialchars($estoque) . '</div>
                        </div>
                        </div>
                    </div>';
                }
                return ['status' => 'success', 'message' => ['message' => 'OK!', 'data' => ['data' => $result, 'total' => $produtos['total'], 'current' => $produtos['current']]]];
            }
        }
    }
?>