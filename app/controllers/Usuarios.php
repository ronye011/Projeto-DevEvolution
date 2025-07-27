<?php
    namespace App\controllers;
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    use App\database\UsuarioDAO;

    class Usuarios {
        private $values = [];

        public function __construct($data) {
            $this->values = $data;
        }

        public function editar() {
            if(!empty($this->values['id'])) {
                if(is_numeric($this->values['id'])) {
                    $Database = new UsuarioDAO;
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

        public function salvar() {
            if (!empty($this->values['email']) && filter_var($this->values['email'], FILTER_VALIDATE_EMAIL)) {
                if (!empty($this->values['nome'])) {
                    if (!empty($this->values['senha'])) {

                        $this->values['senha'] = hash_hmac("sha256", $this->values['senha'], "localhost");
                        $this->values['senha'] = password_hash($this->values['senha'], PASSWORD_DEFAULT);

                        $Database = new UsuarioDAO;
                        $user = $Database->findUserByEmail($this->values['email']);

                        if (empty($this->values['id'])) {
                            if ($user) {
                                return ['status' => 'error', 'message' => 'Já existe um usuário com esse email!'];
                            }

                            $newUser = $Database->novo($this->values);

                            if($newUser) {
                                return ['status' => 'success', 'message' => ['message' => 'Usuário cadastrado com sucesso', 'data' => ['id' => $newUser]]];
                            }
                        } else {
                            if(is_numeric($this->values['id'])) {
                                if ($Database->editar($this->values['id'])) {
                                    if($user['id'] != $this->values['id']) {
                                        return ['status' => 'error', 'message' => 'Já existe um usuário com esse email!'];
                                    }
                                    if ($Database->salvar($this->values)) {
                                        return ['status' => 'success', 'message' => 'Usuário atualizado com sucesso.'];
                                    }
                                }
                            } else {
                                return ['status' => 'error', 'message' => 'Não exite usuario com esse id!'];
                            }
                        }
                    }
                }
            }

            return ['status' => 'error', 'message' => 'Dados obrigatórios em branco ou inválidos!'];
        }

        public function status() {
            if(!empty($this->values['id'])) {
                if(is_numeric($this->values['id'])) {
                    $Database = new UsuarioDAO;
                    if($Database->status($this->values['id'])) {
                        return ['status' => 'success', 'message' => 'Status alterado com sucesso!'];
                    }
                }
            }
            return ['status' => 'error', 'message' => 'Informe um id valido!'];
        }
    }
?>