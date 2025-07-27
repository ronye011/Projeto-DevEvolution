window.addEventListener("load", async function(event) {
    try {
        const element = document.querySelector('[data-total-page]');
        const total = Number(element.dataset.totalPage);
        const current = Number(element.dataset.currentPage);
        const valor = null;

        //console.log(total);
        const response = await fetch('./../app/routes/routerInterface.php?route=Produtos/getProdutos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({valor, total, current})
        });

        const text = await response.text();
        const result = JSON.parse(text);

        if(result.status == 'success') {
            const gridProdutos = document.getElementById("gridProdutos");
            gridProdutos.innerHTML = result.message.data.data;
        }
        
    } catch (error) {
    }
});

// Limpador de campos p
function limparP(id) {
  const container = document.getElementById(id);
  const pS = container.querySelectorAll('p');

  pS.forEach(p => {
    p.textContent = ''; // ou p.innerText = '';
  });
}

// Limpador de campos input
function limparInputs(id) {
  const container = document.getElementById(id);
  const inputs = container.querySelectorAll('input');

  inputs.forEach(input => {
    input.value = '';
  });
}

document.getElementById('gridProdutos').addEventListener('click', async function (e) {
    const buttom = e.target.closest("[data]");

    if(buttom) {
        const dataValue = buttom.getAttribute("data");
        if (!dataValue) return;
        switch(dataValue) {
            case 'comprar':
                limparP('produtoViewer');

                const produto = e.target.closest("[data-id]");
                const id = produto.getAttribute("data-id");
                document.getElementById('produto').setAttribute('data-idProduto', id);

                fetch(`./../app/routes/routerInterface.php?route=Produtos/editar`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({id})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status == "success") {
                        if (data.message.data.quantidade == 1) {
                            notificacao('info', 'Ultimo produto, você tem 2 minutos para finalizar a compra! Se você fechar o detalhamento do seu produto, perderá a reserva.');
                        }

                        document.getElementById('titulo').textContent = data.message.data.nome;
                        document.getElementById('descricao').textContent = data.message.data.descricao;
                        document.getElementById('moeda').textContent = "R$";
                        document.getElementById('valor').textContent = data.message.data.preco;
                        document.getElementById('quantidade').textContent = data.message.data.quantidade;
                        document.getElementById('text').textContent = "Unidade(s) restante";
                        document.getElementById('produtoViewer').style.display = 'flex';
                    } else {
                        notificacao(data.status, data.message);
                    }
                })
                .catch(err => {
                    console.log(err);
                });
                break;
        }
    }
});

document.getElementById('produtoViewer').addEventListener('click', async function (e) {
    const buttom = e.target.closest("[data]");

    if(buttom) {
        const dataValue = buttom.getAttribute("data");
        if (!dataValue) return;
        switch(dataValue) {
            case 'fecharModal':
                limparP('produtoViewer');
                document.getElementById('produtoViewer').style.display = 'none';
                break;

            case 'Buy':
                document.getElementById('totalAtual').textContent = 'R$ ' + document.getElementById('valor').textContent;
                document.getElementById('checkoutModal').style.display = 'flex';
                break;
        }
    }
});

document.getElementById('sucesso').addEventListener('click', async function (e) {
    const buttom = e.target.closest("[data]");

    if(buttom) {
        const dataValue = buttom.getAttribute("data");
        if (!dataValue) return;
        switch(dataValue) {
            case 'fecharModal':
                document.getElementById('sucesso').style.display = 'none';
                break;
        }
    }
});

document.getElementById('checkoutModal').addEventListener('click', async function (e) {
    const buttom = e.target.closest("[data]");

    if(buttom) {

        const dataValue = buttom.getAttribute("data");
        if (!dataValue) return;

        switch(dataValue) {
            case 'fecharCheckout':

                document.getElementById('checkoutModal').style.display = 'none';
                limparInputs('checkoutModal');

                break;

            case 'atualizarCupom':

                const produto = document.getElementById('produto').getAttribute('data-idProduto');
                const codigo = document.getElementById('codigoPromo').value;
                fetch(`./../app/routes/routerInterface.php?route=Cupons/getCupom`, {

                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({codigo, produto})

                })
                .then(res => res.json())
                .then(data => {

                    if (data.status == 'success') {
                        document.getElementById('totalAtual').textContent = 'R$ ' + data.message.data['valor'];
                    } else {
                        notificacao(data.status, data.message);
                    }
                })
        
                break;

            case 'finalizarPedido':

                const dados = {
                    nome: document.getElementById('nomeCliente').value.trim(),
                    email: document.getElementById('emailCliente').value.trim(),
                    telefone: document.getElementById('telefoneCliente').value.trim(),
                    codigo_promo: document.getElementById('codigoPromo').value.trim(),
                    cep: document.getElementById('cep').value.trim(),
                    rua: document.getElementById('rua').value.trim(),
                    numero: document.getElementById('numero').value.trim(),
                    complemento: document.getElementById('complemento').value.trim(),
                    produto: document.getElementById('produto').getAttribute('data-idProduto')
                };
                fetch(`./../app/routes/routerInterface.php?route=Compra/finalizar`, {

                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(dados)

                })
                .then(res => res.json())
                .then(data => {

                    if (data.status == 'success') {
                        limparP('checkoutModal');
                        document.getElementById('produtoViewer').style.display = 'none';
                        document.getElementById('checkoutModal').style.display = 'none';
                        document.getElementById('sucesso').style.display = 'flex';
                        reload();
                    } else {
                        notificacao(data.status, data.message);
                    }
                })
        
                break;
        }
    }
});


document.getElementById('search').addEventListener('click', async function (e) {
    e.preventDefault();
    const buttom = e.target.closest("[data]");

    if(buttom) {
        const dataValue = buttom.getAttribute("data");
        if (!dataValue) return;
        switch(dataValue) {
            case 'grid-search':
                reload();
                break;
            
            case 'grid reload':
                reload();
                break;

            case 'grid previus':
                reload('previus');
                break;

            case 'grid next':
                reload('next');
                break;
        }
    }
});

function reload(type) {
    const valor = document.getElementById('grid-search').value;
    const element = document.querySelector('[data-total-page]');
    let total;
    let current;
    if (type == 'reload' || type == null) {
        total = Number(element.dataset.totalPage);
        current = Number(element.dataset.currentPage);
    } else if (type == 'next') {
        total = Number(element.dataset.totalPage);
        current = Number(element.dataset.currentPage) + 1;
    } else {
        total = Number(element.dataset.totalPage);
        current = Number(element.dataset.currentPage) - 1;
    }
    if (current < 1) current = 1;
    if (current > total) current = total;

    const payload = {valor, total, current};
    
    fetch(`./../app/routes/routerInterface.php?route=Produtos/getProdutos`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if(data.status == 'success') {
            const gridProdutos = document.getElementById("gridProdutos");
            gridProdutos.innerHTML = "";
            gridProdutos.innerHTML = data.message.data.data;

            // Atualiza os atributos de paginação
            element.dataset.totalPage = data.message.data.total;
            element.dataset.currentPage = data.message.data.current;
        } else {
            notificacao(data.status, data.message);
        }
    })
    .catch(err => {
        console.log(err);
    });
}