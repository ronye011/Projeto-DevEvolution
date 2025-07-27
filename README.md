# Projeto-DevEvolution
Sistema de vendas de produtos desenvolvido para a imersão Dev{Evolution}, promovida pela IXC Soft S.A.

## 📄 Descrição

O **SALES+/BUY+** é um sistema que permite cadastrar produtos, usuarios, cupons e gera comprovantes de pagamento e realizar a compra.
É um MVC, logo algumas funcionalidades podem parecer incompletas, mas todo projeto foi feito com muito empenho no periodo de duas semanas que tive para realizar.

---

## ⚙️ Pré-requisitos

- Composer 2.8 ou superior
- DomPDF 3.1 ou superior
- PHP 8.2 ou superior
- PHP-XML
- SQLite
- Git

---

## 📦 Instalação

### 1. Baixe o arquivo de nome install.sh do repositorio:

### 2. Acesse o usuário root do Debian/Ubuntu no terminal

> `su` Debian
> `sudo su` Ubuntu

### 3. Acesse a pasta que você baixou o arquivo install.sh (Normalmente na pasta Downloads)

> `cd /home/user/Downloads/`

### 4. Transforme o arquivo install.sh em um arquivo execultavel

> `chmod +x install.sh`

### 5. Por fim execulte o arquivo

> `./install.sh`

### 6. Agora é só acessar os links no navegador para explorar o sistema
Principais links:
- `http://localhost/sistema/public/` Login do painel
- `http://localhost/sistema/public/home.html` Painel de Administração
- `http://localhost/sistema/public/central.html` Local de compra pelo cliente
---

### O que foi implementado?

### Usuários

- [X]  Criar (cadastro via formulário HTML)
- [X]  Editar e deletar (somente próprios dados)
- [X]  Ver (lista restrita)

### Produtos / Ingressos

- [X]  Criar, editar, deletar, visualizar
- [X]  Reserva de estoque em tempo real (com `data_reserva`)
- [X]  Bloqueio por 2 minutos ao acessar o último item

### Clientes

- [X]  Criar, editar, deletar (restrito por usuário) OBS: Neste sistema não editamos o cliente diretamente, mas há um processamento interno para registrar os dados.
- [X]  Visualização restrita por usuário (não veem outros clientes)

### Compras

- [X]  Comprar produto, com controle de estoque
- [X]  Cancelar reserva após timeout (2 minutos)
- [X]  Exibir mensagem de "Produto indisponível" se esgotado

### Bonus
      **Histórico de compras**
- [X]   Inserir um sistema de logs de compras de ingressos/produtos.
- [X]   Permitir que o usuário veja todas as compras feitas
      **Geração de comprovante em PDF**
- [X]   Ex: usar `mpdf/mpdf` ou `dompdf/dompdf`
      **Códigos de desconto / cupom**
- [X]   Campo promocional que reduz o preço
---

Diagrama de funcionamento da compra:

```mermaid
graph TD;
    Comprar--> Detalhamento;
    Detalhamento --> 'Comprar 2';
    'Comprar 2' --> Finalizar;
    'Comprar 2' --> Desconto;
    Desconto --> Finalizar;
```

Diagrama de funcionamento do produto:

```mermaid
graph TD;
    Comprar--> Detalhamento;
    Detalhamento --> Comprar2;
    Comprar2 --> Finalizar;
    Comprar2 --> Desconto;
    Desconto --> Finalizar;
```

Diagrama de funcionamento do cupom:

```mermaid
graph TD;
    Comprar--> Detalhamento;
    Detalhamento --> Comprar2;
    Comprar2 --> Finalizar;
    Comprar2 --> Desconto;
    Desconto --> Finalizar;
```
