// Validação ao entrar na pagina
window.addEventListener("load", async function(event) {
    try {
        const response = await fetch('./../app/routes/routerInterface.php?route=Login/Valid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        });

        const text = await response.text();
        const result = JSON.parse(text);

        if (result.status === 'success') {
          const response = await fetch('./../app/routes/routerInterface.php?route=Login/NameUser', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify({})
          });
          const text = await response.text();
          const result = JSON.parse(text);
          document.getElementById("nameUserLogged").textContent = result.message;
          return;
        } else {
          window.location.href = '/public/central.html'; // Redireciona para página de login
        }
    } catch (error) {
    }
});

// Função para o dinamismo do menu
$(document).ready(function () {
  $("#leftside-navigation .sub-menu > a").click(function (e) {
    $("#leftside-navigation ul ul").slideUp();
    if (!$(this).next().is(":visible")) {
      $(this).next().slideDown();
    }
    e.stopPropagation();
  });
});

// Carregador de paginas do menu
document.addEventListener("DOMContentLoaded", function () {
  const links = document.querySelectorAll('[data-home]');

  links.forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();

      const view = link.getAttribute('data-home');
      const modalContainer = document.getElementById("menus_content");

      fetch(`./../app/routes/routerScreen.php?route=${view}`)
        .then(response => {
          if (!response.ok) {
            notificacao('error', "Erro ao carregar o conteúdo");
          }
          return response.text();
        })
        .then(data => {
          const result = JSON.parse(data)
          if(result.status == 'success') {
            modalContainer.innerHTML = result.message;
            const instance = window["grid"];
            if (instance && typeof instance['searchBar'] === 'function') {
              instance['searchBar'](1, view, "gridContainer");
            }
          } else {
            notificacao(result.status, result.message);
          }
          // Só exibe após o conteúdo estar carregado */  
          modalContainer.style.display = "block";
        })
        .catch();
    });
  });
});

// Plugin arrastavel das telas (COPIA DA WEB)
(function($) {
    $.fn.drags = function(opt) {

        opt = $.extend({handle:"",cursor:"move"}, opt);

        if(opt.handle === "") {
            var $el = this;
        } else {
            var $el = this.find(opt.handle);
        }

        return $el.css('pointer', opt.cursor).on("mousedown", function(e) {
            if(opt.handle === "") {
                var $drag = $(this).addClass('draggable');
            } else {
                var $drag = $(this).addClass('active-handle').parent().addClass('draggable');
            }
            var z_idx = $drag.css('z-index'),
                drg_h = $drag.outerHeight(),
                drg_w = $drag.outerWidth(),
                pos_y = $drag.offset().top + drg_h - e.pageY,
                pos_x = $drag.offset().left + drg_w - e.pageX;
            $drag.css('z-index', 1000).parents().on("mousemove", function(e) {
                $('.draggable').offset({
                    top:e.pageY + pos_y - drg_h,
                    left:e.pageX + pos_x - drg_w
                }).on("mouseup", function() {
                    $(this).removeClass('draggable').css('z-index', z_idx);
                });
            });
            e.preventDefault(); // disable selection
        }).on("mouseup", function() {
            if(opt.handle === "") {
                $(this).removeClass('draggable');
            } else {
                $(this).removeClass('active-handle').parent().removeClass('draggable');
            }
        });

    }
})(jQuery);

$('#modal').drags();

$('#modal-trigger').click(function(){
  $(this).addClass('hide');
  $('#modal').addClass('show');
});

// Limpador de campos
function limparInputs(id) {
  const container = document.getElementById(id);
  const inputs = container.querySelectorAll('input');

  inputs.forEach(input => {
    input.value = '';
  });
}

// Logout
document.getElementById("user").addEventListener("click", async function(event) {
  const buttom = event.target.closest("[data]");

  if (buttom) {
    const dataValue = buttom.getAttribute("data");
    if (!dataValue) return;
    if (dataValue == 'exit') {
      const result = await callAPI("login/exit");

      if (result.status === 'success') {
        window.location.href = '/public/central.html'; // Redireciona para página de login
      } else {
        notificacao(result.status, result.message);
      }
    }
  }

});

// Detector de cliques
document.getElementById("content_menu").addEventListener("click", async function(event) {
  const buttom = event.target.closest("[data]");

  if (buttom) {
    const dataValue = buttom.getAttribute("data");
    if (!dataValue) return;
    const chips = dataValue.split(" ");
    switch(chips[0]) {
      case "grid":
        // Captura o metodo de buttom
        const methodName = chips[1];

        // Pega o atributo data-form mais perto
        const tableContainer = buttom.closest("[data-form]");
        const dataValue = tableContainer?.getAttribute("data");
        const tableNames = tableContainer?.getAttribute("data-form");
        const instance = window[chips[0]];

        //Exemplo de chamada
        //Objeto >> Metodo >> Argumento1(tabela) >> Argumento2(div)
        //grid[reload](usuarios, gridConteiner)

        if (instance && typeof instance[methodName] === "function") {
          // Chama a função dinamicamente
          instance[methodName](tableNames, dataValue);
        }
        break;

      case "btn":
        switch (chips[1]) {
          case "novo":
            limparInputs('modal');
            document.getElementById('modal').style.display = 'flex';

            // Ativa o plugin
            $(modal).drags({ handle: ".modal-head" });
            $(modal).addClass("draggable-ready");
            break;

          case "fecharModal":
            limparInputs('modal');
            document.getElementById('modal').style.display = 'none';
            break;

          case "salvar":
            // Pega o data mais proximo, o data pai
            const formsalvar = buttom.closest("[data-form]");

            // Pegando o valor do atributo data-form
            const tablesalvar = formsalvar.getAttribute("data-form");

            // Cria um objeto com os dados do formulário
            const dadossalvar = {};
            const inputssalvar = formsalvar.querySelectorAll("input");
            inputssalvar.forEach(input => {
              if (input.type === 'checkbox') {
                // Para checkbox, pega se está marcado (true/false)
                dadossalvar[input.name] = input.checked;
              } else if (input.type === 'radio') {
                // Para radio, pega o valor do radio marcado no grupo (mesmo name)
                if (input.checked) {
                  dadossalvar[input.name] = input.id;
                } else if (!(input.name in dadossalvar)) {
                  dadossalvar[input.name] = null;
                }
              } else {
                // Para outros tipos de input
                dadossalvar[input.name] = input.value;
              }
            });
            try {
              const result = await callAPI(`${tablesalvar}/${chips[1]}`, dadossalvar);

              if (result.status === 'success') {
                if(result.message.data) {
                  // Se message for um objeto com chaves e valores
                  if (typeof result.message.data === 'object' && result.message.data !== null) {
                    Object.entries(result.message.data).forEach(([key, value]) => {
                      const input = document.getElementById(key);
                      if (input) {
                        input.value = value;
                      }
                    });
                  }

                  notificacao(result.status, result.message.message);
                }
                else {
                  notificacao(result.status, result.message);
                }
              return;
              } else if (result.status === 'error') {
                notificacao(result.status, result.message);
              }
            } catch (err) {
              notificacao('error', 'Erro ao tentar salvar os dados');
            }
            break;

          case 'deletar':
            // Pega o data mais proximo, o data pai
            const formdeletar = buttom.closest("[data-form]");

            // Pegando o valor do atributo data-form
            const tabledeletar = formdeletar.getAttribute("data-form");

            const datadeletar = checkedIDGrid();
            if(!datadeletar) return;
            
            try {
              const result = await callAPI(`${tabledeletar}/${chips[1]}`, datadeletar);

              if (result.status === 'success') {
                notificacao(result.status, result.message);
              return;
              } else if (result.status === 'error') {
                notificacao(result.status, result.message);
              }
            } catch (err) {
              notificacao('error', 'Erro ao tentar salvar os dados');
            }
            break;

          case 'status':
            // Pega o data mais proximo, o data pai
            const formstatus = buttom.closest("[data-form]");

            // Pegando o valor do atributo data-form
            const tablestauts = formstatus.getAttribute("data-form");

            const datastatus = checkedIDGrid();
            if(!datastatus) return;
            
            try {
              const result = await callAPI(`${tablestauts}/${chips[1]}`, datastatus);

              if (result.status === 'success') {
                notificacao(result.status, result.message);
              return;
              } else if (result.status === 'error') {
                notificacao(result.status, result.message);
              }
            } catch (err) {
              notificacao('error', 'Erro ao tentar salvar os dados');
            }
            break;

          case 'editar':
            // Pega o data mais proximo, o data pai
            const formeditar = buttom.closest("[data-form]");

            // Pegando o valor do atributo data-form
            const tableeditar = formeditar.getAttribute("data-form");

            const dataeditar = checkedIDGrid();
            if(!dataeditar) return;
            
            try {
              const result = await callAPI(`${tableeditar}/${chips[1]}`, dataeditar);

              if (result.status === 'success') {
                // Se message for um objeto com chaves e valores
                if (typeof result.message === 'object' && result.message !== null) {
                  Object.entries(result.message).forEach(([key, value]) => {
                    if (key === 'tipo_desconto') {
                      const input = document.getElementById(value);
                      if (input) {
                        input.checked = true;
                      }
                    }
                    const input = document.getElementById(key);
                    if (input) {
                      input.value = value;
                    }
                  });
                }

                document.getElementById('modal').style.display = 'flex';

                // Ativa o plugin
                $(modal).drags({ handle: ".modal-head" });
                $(modal).addClass("draggable-ready");

                return;
              } else if (result.status === 'error') {
                notificacao(result.status, result.message);
              }
            } catch (err) {
              notificacao('error', 'Erro ao tentar retornar os dados');
            }
            break;

          case 'inutilizar':
            // Pega o data mais proximo, o data pai
            const forminutilizar = buttom.closest("[data-form]");

            // Pegando o valor do atributo data-form
            const tableinutilizar = forminutilizar.getAttribute("data-form");

            const datainutilizar = checkedIDGrid();
            if(!datainutilizar) return;
            
            try {
              const result = await callAPI(`${tableinutilizar}/${chips[1]}`, datainutilizar);

              if (result.status === 'success') {
                notificacao(result.status, result.message);
              return;
              } else if (result.status === 'error') {
                notificacao(result.status, result.message);
              }
            } catch (err) {
              notificacao('error', 'Erro ao tentar salvar os dados');
            }
            break;

            case 'imprimir':
            const dataimprimir = checkedIDGrid();
            if(!dataimprimir) return;
            // Abre o PDF em nova aba
            window.open(`./../app/routes/routerAjax.php?route=compras/imprimir&id=${dataimprimir['id']}`, '_blank');
            break;
        }
      break;
    }
  }
});

// Detector de teclas
document.getElementById("content_menu").addEventListener("keydown", function(event) {
  const isEnter = event.key === "Enter";
  const buttom = event.target.closest("[data]");

  if (isEnter) {
    event.preventDefault(); // <-- Impede o envio automático do form
    const dataValue = buttom.getAttribute("data");
    if (!dataValue) return;
    const chips = dataValue.split(" ");
    switch(chips[0]) {
      case "grid":
        // Captura o metodo de buttom
        const methodName = chips[1];

        // Pega o atributo data-form mais perto
        const tableContainer = buttom.closest("[data-form]");
        const dataValue = tableContainer?.getAttribute("data");
        const tableNames = tableContainer?.getAttribute("data-form");
        const instance = window[chips[0]];

        //Exemplo de chamada
        //Objeto >> Metodo >> Argumento1(tabela) >> Argumento2(div)
        //grid[reload](usuarios, gridConteiner)

        if (instance && typeof instance[methodName] === "function") {
          // Chama a função dinamicamente
          instance[methodName](tableNames, dataValue);
        }
        break;
    }
  }
});

function checkedIDGrid() {
  // Pega o id da grid
  const container = document.querySelector('div[data="gridContainer"]');
  const selecionados = [...container.querySelectorAll('input[name="selected_items[]"]:checked')].map(el => el.value);

  if (selecionados.length !== 1) {
    notificacao('warning', selecionados.length > 1 ? "Selecione apenas um registro!" : "Selecione pelo menos um registro!");
    return;
  }
  const data = {};
  data['id'] = selecionados['0'];
  return data;
}

async function callAPI(route, values) {
  const response = await fetch(`./../app/routes/routerAjax.php?route=${route}`, {
  method: 'POST',
  headers: {
      'Content-Type': 'application/json'
  },
  body: JSON.stringify(values)
  });

  const text = await response.text();
  return JSON.parse(text);
}
