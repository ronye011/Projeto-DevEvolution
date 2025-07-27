class Grid {
  constructor() {
    this.apiBase = './../app/routes/routerAjax.php';
  }

  calcularItensPorPagina(table) {
    const tableContainer = document.querySelector(`div[data="${table}"]`);
    const tableBodyWrapper = tableContainer?.querySelector('[data="grid wait"]');

    if (!tableBodyWrapper) {
      console.error("Elemento 'wait' não encontrado.");
      return 1;
    }

    tableBodyWrapper.style.display = "";
    const alturaDisponivel = tableBodyWrapper.clientHeight;
    tableBodyWrapper.style.display = "none";

    return Math.max(Math.floor(alturaDisponivel / 40) - 1, 1);
  }

  preencherGrid(page, dadosPaginados, totalPaginas, table, totalItensForPage) {
    const tableContainer = document.querySelector(`div[data="${table}"]`);
    const tabela = tableContainer?.querySelector('[data="grid grid"]');
    const thead = tabela?.querySelector("thead tr");

    if (!tabela || !thead) {
      console.error("Tabela ou cabeçalho não encontrado.");
      return;
    }

    const totalCols = thead.children.length;
    const dataDiv = tableContainer.querySelector('[data="grid data"]');

    if (!dataDiv) {
      console.error("Elemento de dados não encontrado.");
      return;
    }

    dataDiv.innerHTML = "";
    let quantDadosPreenchidos = 0;

    // Preenche dados
    dadosPaginados.forEach((dado) => {
      const row = document.createElement("tr");

      const cellCheckbox = document.createElement("td");
      cellCheckbox.innerHTML = `<input type="checkbox" name="selected_items[]" value="${dado.id}">`;
      row.appendChild(cellCheckbox);

      Object.keys(dado).forEach((key) => {
        const cell = document.createElement("td");
        cell.textContent = dado[key];
        row.appendChild(cell);
      });
      quantDadosPreenchidos++;
      dataDiv.appendChild(row);
    });

    // Preenche linhas vazias
    const linhasFaltantes = totalItensForPage - quantDadosPreenchidos;

    for (let i = 0; i < linhasFaltantes; i++) {
      const emptyRow = document.createElement("tr");

      const cellCheckbox = document.createElement("td");
      cellCheckbox.innerHTML = "&nbsp;";
      emptyRow.appendChild(cellCheckbox);

      for (let j = 1; j < totalCols; j++) {
        const cell = document.createElement("td");
        cell.innerHTML = "&nbsp;";
        emptyRow.appendChild(cell);
      }

      dataDiv.appendChild(emptyRow);
    }

    dataDiv.style.display = "";

    // Atualiza paginação
    const pageInfoSpan = tableContainer.querySelector('[data-current-page]');
    if (pageInfoSpan) {
      pageInfoSpan.setAttribute('data-current-page', page);
      pageInfoSpan.setAttribute('data-total-pages', totalPaginas);
      pageInfoSpan.textContent = `${page} - ${totalPaginas}`;
    }
  }

  searchBar(page = 1, table, nameDivGrid) {
    const div = document.querySelector(`[data="${nameDivGrid}"]`);
    if (!div) return console.error(`Container com data="${nameDivGrid}" não encontrado.`);
    const filtro = div.querySelector('select[data="grid colunasSearch"]')?.value || null;
    const searchTerm = div.querySelector('input[data="grid search"]')?.value || null;
    const totalItensForPage = this.calcularItensPorPagina(nameDivGrid);

    const payload = { filtro, searchTerm, totalItensForPage, page, table };

    fetch(`${this.apiBase}?route=grid/getDataGrid`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
        this.preencherGrid(page, data.result.data, data.result.totalPages, nameDivGrid, totalItensForPage);
        } else {
        notificacao('error', data.message);
        }
    })
    .catch(err => {
        console.error(err);
        notificacao('error', err);
    });
    }

  search(table, data) {
    this.searchBar(1, table, data);
  }

  reload(table, data) {
    const div = document.querySelector(`[data="${data}"]`);
    const currentPage = Number(div?.querySelector('[data-current-page]')?.getAttribute('data-current-page'));
    this.searchBar(currentPage, table, data);
  }

  next(table, data) {
    const div = document.querySelector(`[data="${data}"]`);
    const currentPage = Number(div?.querySelector('[data-current-page]')?.getAttribute('data-current-page'));
    const totalPages = Number(div?.querySelector('[data-total-pages]')?.getAttribute('data-total-pages'));
    if (currentPage < totalPages) {
      this.searchBar(currentPage + 1, table, data);
    }
  }

  previus(table, data) {
    const div = document.querySelector(`[data="${data}"]`);
    const currentPage = Number(div?.querySelector('[data-current-page]')?.getAttribute('data-current-page'));

    if (currentPage > 0 && currentPage != 1) {
      this.searchBar(currentPage - 1, table, data);
    }
  }
}

// Instância global
window.grid = new Grid();