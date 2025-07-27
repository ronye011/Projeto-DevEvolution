<?php
namespace App\controllers;

use App\database\CompraDAO;
use App\database\ClienteDAO;
use App\database\UsuarioDAO;
use App\database\CompraLogDAO;
use App\database\ProdutosDAO;
use App\controllers\Cupons;
use Dompdf\Dompdf;

class Compra {
    private $values;

    public function __construct($data) {
        $this->values = $data;
    }

    public function closeCompra() {
        // Validar nome
        if (empty($this->values['nome']) || !is_string($this->values['nome'])) {
            return ['status' => 'error', 'message' => 'Nome inválido!'];
        }

        // Validar e-mail
        if (empty($this->values['email']) || !filter_var(trim($this->values['email']), FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'E-mail inválido!'];
        }

        // Validar CEP
        $this->values['cep'] = preg_replace('/\D/', '', $this->values['cep']);
        if (empty($this->values['cep']) || !is_numeric($this->values['cep']) || strlen($this->values['cep']) !== 8) {
            return ['status' => 'error', 'message' => 'CEP inválido!'];
        }

        // Validar telefone
        if (!empty($this->values['telefone'])) {
            $this->values['telefone'] = preg_replace('/\D/', '', $this->values['telefone']);
            if (!is_numeric($this->values['telefone']) || strlen($this->values['telefone']) != 11) {
                return ['status' => 'error', 'message' => 'Telefone inválido!'];
            }
        }

        // Validar número
        if (!empty($this->values['numero'])) {
            $this->values['numero'] = preg_replace('/\D/', '', $this->values['numero']);
            if (!is_numeric($this->values['numero'])) {
                return ['status' => 'error', 'message' => 'Número inválido!'];
            }
        }

        $produtoDAO = new ProdutosDAO();
        $produto = $produtoDAO->editarCentral($this->values['produto']);

        if (!$produto) {
            return ['status' => 'error', 'message' => 'Produto não localizado'];
        }

        if($produto['quantidade'] < 1) {
            return ['status' => 'error', 'message' => 'Erro ao processar!'];
        }

        // Se tiver cupom
        if (!empty($this->values['codigo_promo'])) {
            $this->values['usuario_id'] = $produto['usuario_id'];
            $cupomControl = new Cupons([
                'codigo' => $this->values['codigo_promo'],
                'produto' => $this->values['produto']
            ]);

            $cupom = $cupomControl->getCupom();

            if ($cupom && $cupom['status'] === 'success') {
                $this->values['valor'] = $cupom['message']['data']['valor'];
            } else {
                return $cupom;
            }
        } else {
            // Sem cupom: buscar preço do produto
            $this->values['valor'] = $produto['preco'];
            $this->values['usuario_id'] = $produto['usuario_id'];
        }

        // Validar cliente
        $clienteDAO = new ClienteDAO();
        $cliente = $clienteDAO->findClienteByEmail($this->values['email']);

        if (!$cliente) {
            $this->values['cliente'] = $clienteDAO->novo($this->values);
        } else {
            if (isset($cliente[0])) {
                $cliente = $cliente[0]; // Ajuste para retorno em array
            }
            $this->values['cliente'] = $cliente['id'];

            if (
                $this->values['nome'] !== $cliente['nome'] ||
                $this->values['email'] !== $cliente['email'] ||
                $this->values['telefone'] !== $cliente['telefone']
            ) {
                $clienteDAO->salvar([
                    'id' => $this->values['cliente'],
                    'nome' => $this->values['nome'],
                    'email' => $this->values['email'],
                    'telefone' => $this->values['telefone']
                ]);
            }
        }

        // Decrementar estoque
        $produtoDAO->decrementar(['id' => $this->values['produto']]);

        // Criar compra
        $compraDAO = new CompraDAO();
        $compra = $compraDAO->novo($this->values);

        if ($compra) {
            // Log da compra
            $logDAO = new CompraLogDAO();
            $logDAO->closeCompra([
                'id_produto' => $this->values['produto'],
                'id_compra' => $compra
            ]);

            return [
                'status' => 'success',
                'message' => [
                    'message' => 'Compra finalizada!',
                    'data' => ['id' => $compra]
                ]
            ];
        }

        return ['status' => 'error', 'message' => 'Erro ao finalizar a compra!'];
    }

    public function comprovante() {
        $compraDAO = new CompraDAO();
        $compra = $compraDAO->editar($this->values['id']);

        $produtoDAO = new ProdutosDAO();
        $produto = $produtoDAO->editar($compra['produto_id']);

        $clienteDAO = new ClienteDAO();
        $cliente = $clienteDAO->editar($compra['cliente_id']);

        $usuarioDAO = new UsuarioDAO();
        $usuario = $usuarioDAO->getUserName();

        $html = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 14px; margin-right: 40px; }
                    .container { width: 100%; padding: 20px; border: 1px solid #000; }
                    .titulo { font-size: 20px; font-weight: bold; margin-bottom: 20px; text-align: center; }
                    .linha { margin-bottom: 10px; }
                    .label { font-weight: bold; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='titulo'>Comprovante de Pagamento</div>
                    <div class='linha'><span class='label'>Nome:</span> {$cliente['nome']}</div>
                    <div class='linha'><span class='label'>Email:</span> {$cliente['email']}</div>
                    <div class='linha'><span class='label'>Telefone:</span> {$cliente['telefone']}</div>
                    <div class='linha'><span class='label'>Produto:</span> {$produto['nome']}</div>
                    <div class='linha'><span class='label'>Descrição:</span> {$produto['descricao']}</div>
                    <div class='linha'><span class='label'>Valor Pago:</span> {$compra['valor']}</div>
                    <div class='linha'><span class='label'>Data do Pagamento:</span> {$compra['data_compra']}</div>
                    <div class='linha'><span class='label'>CEP Entrega:</span> {$compra['cep']}</div>
                    <div class='linha'><span class='label'>Rua:</span> {$compra['rua']}</div>
                    <div class='linha'><span class='label'>Número:</span> {$compra['numero']}</div>
                    <div class='linha'><span class='label'>Complemento:</span> {$compra['complemento']}</div>
                    <br>
                    <div style='text-align: center;'>Documento gerado automaticamente pelo SALES+.</div>
                </div>
            </body>
            </html>
        ";

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream("comprovante_pagamento.pdf", ["Attachment" => false]);
    }
}
?>
