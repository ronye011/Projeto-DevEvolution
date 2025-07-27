<?php
    namespace App\controllers;

    use App\database\CuponsDAO;
    use App\database\ProdutosDAO;

    class Cupons {
        private $values = [];

        public function __construct($data) {

            $this->values = $data;

        }

        public function salvar() {
            // Campos obrigatorios
            if (!empty($this->values['codigo'])) {

                if(!empty($this->values['tipo_desconto']) && !is_null($this->values['tipo_desconto'])) {

                    if ($this->values['tipo_desconto'] != 'percentual' && $this->values['tipo_desconto'] != 'fixo') {

                        return ['status' => 'error', 'message' => 'Selecione um tipo válido!'];

                    }

                    // Formata valor
                    $this->values['valor_desconto'] = str_replace(',', '.', $this->values['valor_desconto']);
                    if (filter_var($this->values['valor_desconto'], FILTER_VALIDATE_FLOAT) !== false) {

                        if ($this->values['tipo_desconto'] == 'percentual') {

                            if ($this->values['valor_desconto'] > 99.00) {

                                return ['status' => 'error', 'message' => 'Porcentagem maior que 99% não é permitido!'];

                            }
                        }

                        // Valida data
                        if(!empty($this->values['validade'])) {

                            $valor = str_replace("-", "", $this->values['validade']);
                            

                            if (strlen($valor) != 8 ) {

                                return ['status' => 'error', 'message' => 'Validade invalida!'];

                            }

                            if (is_numeric($valor)) {

                                $formato = 'Y-m-d';
                                $objData = \DateTime::createFromFormat($formato, $this->values['validade']);

                                if ($objData && $objData->format($formato) === $this->values['validade']) {

                                    $Database = new CuponsDAO();

                                    // Valida se o sistema terá que criar um novo registro ou atualizar
                                    if (empty($this->values['id'])) {

                                        // Identifica duplicidade
                                        $Cupom = $Database->findCupomByCodigo($this->values['codigo']);

                                        if($Cupom) {

                                            return ['status' => 'error', 'message' => 'Já existe um cupom com esse codigo'];

                                        }

                                        $cupomID = $Database->novo($this->values);

                                        if($cupomID) {

                                            return ['status' => 'success', 'message' => ['message' => 'Cupom cadastrado com sucesso', 'data' => ['id' => $cupomID]]];

                                        }
                                    } else {
                                        
                                        if (is_numeric($this->values['id'])) {
                                            
                                            $cupom = $Database->editar($this->values['id']);

                                            if (!empty($cupom['usuario_id'])) {

                                                if (session_status() === PHP_SESSION_NONE) {

                                                    session_start();

                                                }

                                                if ($cupom['usuario_id'] === $_SESSION['id_user']) {

                                                    $sucesso = $Database->salvar($this->values);
                                                    if ($sucesso) {
                                                        return ['status' => 'success', 'message' => 'Cupom atualizado com sucesso'];
                                                    } else {
                                                        return ['status' => 'error', 'message' => 'Erro ao atualizar o cupom'];
                                                    }

                                                }
                                            }
                                            else {
                                                return ['status' => 'error', 'message' => 'Cupom inexistente com o id informado'];
                                            }
                                        }
                                        else {
                                            return ['status' => 'error', 'message' => 'ID invalido!'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return ['status' => 'error', 'message' => 'Dados obrigatórios em branco ou inválidos!'];
        }

        public function getCupom() {

            if (!empty($this->values['codigo'])) {

                // Identifica o cupom
                $Database = new CuponsDAO();
                $Cupom = $Database->findCupomByCodigo($this->values['codigo']);

                if ($Cupom) {

                    $dataHoje = new \DateTime('now');

                    if ($Cupom['usado'] == 0 && $Cupom['validade'] >= $dataHoje->format('Y-m-d')) {

                        if(!empty($this->values['produto'])) {

                            if(is_numeric($this->values['produto'])) {

                                $Database2 = new ProdutosDAO();
                                $Produto = $Database2->editarCentral($this->values['produto']);

                                if ($Produto) {

                                    $valor = 0;

                                    if ($Cupom['tipo_desconto'] == 'fixo') {

                                        if ($Cupom['valor_desconto'] >= $Produto['preco']) {

                                            return ['status' => 'error', 'message' => 'O valor da compra não pode ser menor que R$ 0,01!'];

                                        } 

                                        $valor = $Produto['preco'] - $Cupom['valor_desconto'];

                                    } else {

                                        $porcentagemDecimal = $Cupom['valor_desconto'] / 100;
                                        $procentagemValor = $Produto['preco'] * $porcentagemDecimal;
                                        $valor = $Produto['preco'] - $procentagemValor;

                                        if ($valor < 0.01) {

                                            return ['status' => 'error', 'message' => 'O valor da compra não pode ser menor que R$ 0,01!'];

                                        }

                                    }

                                    return ['status' => 'success', 'message' => ['message' => 'Cupom localizado com sucesso', 'data' => ['valor' => $valor]]];

                                } else {

                                    return ['status' => 'error', 'message' => 'Nenhum resultado encontrado!'];

                                }
                            }
                        }
                    }

                    return ['status' => 'error', 'message' => 'Cupom expirado!'];

                }

                return ['status' => 'error', 'message' => 'Cupom inexistente!'];

            }

            return ['status' => 'error', 'message' => 'Codigo invalido!'];

        }

        public function editar() {

            if(!empty($this->values['id'])) {

                if(is_numeric($this->values['id'])) {

                    $Database = new CuponsDAO;
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

        public function inutilizar() {
            if(!empty($this->values['id'])) {

                if(is_numeric($this->values['id'])) {

                    $Database = new CuponsDAO;


                    if (session_status() === PHP_SESSION_NONE) {

                        session_start();

                    }

                    $cupom = $Database->editar($this->values['id']);

                    if ($cupom['usuario_id'] === $_SESSION['id_user']) {

                        $result = $Database->inutilizar($this->values['id']);

                        if ($result) {

                            return ['status' => 'success', 'message' => 'Inutilizado com sucesso!'];

                        }

                    }
                    return ['status' => 'error', 'message' => 'Nenhum resultado encontrado!'];

                }
            }

            return ['status' => 'error', 'message' => 'Informe um id valido!'];
        }

        public function usar() {
            
        }
    }
?>