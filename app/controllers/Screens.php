<?php
    namespace App\controllers;

    class Screens {
        private function generateGrid($columns, $identifier) {
            //Padrão de geração de classes
            $defaultsClass = [
                'classGrid' => 'grid',
                'classSearch' => 'search',
                'classPagination' => 'pagination',
                'classPrevious' => 'previous',
                'classReload' => 'reload',
                'classNext' => 'next',
                'classGridTable' => 'grid-table',
                'classGridBody' => 'grid-body',
                'classWait' => 'wait',
                'classLoader' => 'loader'
            ];

            $settings = array_merge($defaultsClass, $identifier);
            $grid;

            //Pesquisa
            $grid = '<div class="' . $settings['classGrid'] . '"><div class="' . $settings['classSearch'] . '">
                    <select id="grid colunasSearch" name="colunasSearch" data="grid colunasSearch">';
                    foreach ($columns as $column_key => $column_label) {
                        $grid .= '<option value="';
                        $grid .= htmlspecialchars($column_key);
                        $grid .= '">';
                        $grid .= htmlspecialchars($column_label);
                        $grid .= '</option>';
                    }
            $grid .=  ' </select>
                    <input type="text" id="grid search" name="search"  data="grid search" placeholder="Buscar..."/></div>';
            
            //Paginacao
            $grid .= '  <div class="' . $settings['classPrevious'] . '">
                        <button class="' . $settings['classPagination'] . '" type="button" data="grid previus">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                        </svg>
                        </button>
                        <button class="' . $settings['classReload'] . '" type="button" data="grid reload">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                        </svg>
                        </button>
                        <button class="' . $settings['classReload'] . '" type="button" data="grid next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
                        </svg>
                        </button>
                        <span data-current-page="0" data-total-page="0">
                        Aguarde - Aguarde
                        </span>
                    </div>
                </div>';


            //Cabecalho
            $grid .= '<table data="grid grid" class="' . $settings['classGridTable'] . '"><thead><tr><th></th>';

            foreach ($columns as $column_key => $column_label) {
                $grid .= '<th>';
                $grid .= htmlspecialchars($column_label);
                $grid .= '</th>';
            }
            $grid .= '</tr></thead><tbody data="grid data" style="display: none;" class="' . $settings['classGridBody'] . '"></tbody></table><div data="grid wait" class="' . $settings['classWait'] . '"><div class="' . $settings['classLoader'] . '"></div></div>';

            return $grid;
        }

        private function modalHead($fields) {
            $defaults = [
                'head' => [
                    'classModalHead' => 'modal-head',
                    'close' => [
                        'closeClass' => 'close',
                        'data' => ''
                    ],
                    'titleModal' => [
                        'titleClass' => '',
                        'titleName' => ''
                    ],
                    'headButton' => [
                        'ativo' => 'Nao',
                        'buttons' => []
                    ]
                ]
            ];
        
            // Usar array_replace_recursive() para evitar arrays aninhados indesejados
            $campos = array_replace_recursive($defaults, $fields);
            $headModal;
        
            $headModal = '<div class="' . htmlspecialchars($campos['head']['classModalHead']) . '">
                    <span class="' . htmlspecialchars($campos['head']['close']['closeClass']) . '" type="button" data="btn ' . htmlspecialchars($campos['head']['close']['data']) . '">&times;</span>
                    <h2 class="' . htmlspecialchars($campos['head']['titleModal']['titleClass']) . '">' . htmlspecialchars($campos['head']['titleModal']['titleName']) . '</h2>';
        
            // Renderiza botões
            if ($campos['head']['headButton']['ativo'] == "Sim" && !empty($campos['head']['headButton']['buttons'])) {
                foreach ($campos['head']['headButton']['buttons'] as $button) {
                    $headModal .= '<button type="button" data="btn ' . htmlspecialchars($button['data']) . '">' . htmlspecialchars($button['Name']) . '</button>';
                }
            }
        
            $headModal .= '</div>';

            return $headModal;
        }

        private function modalBody($fields) {
            $defaults = [
                'body' => [
                    'fields' => []
                ]
            ];

            // Usar array_replace_recursive() para evitar arrays aninhados indesejados
            $campos = array_replace_recursive($defaults, $fields);
            $bodyModal = "<div class='modal-body'>";

            if (!empty($campos['body']['fields'])) {
                foreach ($campos['body']['fields'] as $field) {
                    switch($field['type']) {
                        case 'text':
                            $bodyModal .= "<div class='campo'>
                                            <label for='" . htmlspecialchars($field['data']) . "'>" . htmlspecialchars($field['label']);
                            if(!empty($field['require']) && $field['require'] == 'S') {
                                $bodyModal .= "<span class='required'>*</span>";
                            }
                            $bodyModal .= "</label>";
                            $bodyModal .= "<input maxlength='255' type='" . htmlspecialchars($field['type']) . "' id='" . htmlspecialchars($field['data']) . "' name='" . htmlspecialchars($field['data']) . "'";
                            if(!empty($field['require']) && $field['require'] == 'S') {
                                $bodyModal .= " require ";
                            }
                            if (!empty($field['disabled']) && $field['disabled'] == 'S') {
                                $bodyModal .= " disabled ";
                            }
                            if (!empty($field['autocomplete'])) {
                                $bodyModal .= " autocomplete='" . $field['autocomplete'] . "'";
                            }
                            $bodyModal .= "></div>";
                            break;

                        case 'number':
                            $bodyModal .= "<div class='campo'>
                                            <label for='" . htmlspecialchars($field['data']) . "'>" . htmlspecialchars($field['label']);
                            if(!empty($field['require']) && $field['require'] == 'S') {
                                $bodyModal .= "<span class='required'>*</span>";
                            }
                            $bodyModal .= "</label>";
                            $bodyModal .= "<input maxlength='255' type='" . htmlspecialchars($field['type']) . "' id='" . htmlspecialchars($field['data']) . "' name='" . htmlspecialchars($field['data']) . "'";
                            if(!empty($field['require']) && $field['require'] == 'S') {
                                $bodyModal .= " require ";
                            }
                            if (!empty($field['disabled']) && $field['disabled'] == 'S') {
                                $bodyModal .= " disabled ";
                            }
                            if (!empty($field['autocomplete'])) {
                                $bodyModal .= " autocomplete='" . $field['autocomplete'] . "'";
                            }
                            $bodyModal .= "></div>";
                            break;

                        case 'date':
                            $bodyModal .= "<div class='campo'>
                                            <label for='" . htmlspecialchars($field['data']) . "'>" . htmlspecialchars($field['label']);
                            if(!empty($field['require']) && $field['require'] == 'S') {
                                $bodyModal .= "<span class='required'>*</span>";
                            }
                            $bodyModal .= "</label>";
                            $bodyModal .= "<input type='" . htmlspecialchars($field['type']) . "' id='" . htmlspecialchars($field['data']) . "' name='" . htmlspecialchars($field['data']) . "'";
                            if(!empty($field['require']) && $field['require'] == 'S') {
                                $bodyModal .= " require ";
                            }
                            if (!empty($field['disabled']) && $field['disabled'] == 'S') {
                                $bodyModal .= " disabled ";
                            }
                            if (!empty($field['autocomplete'])) {
                                $bodyModal .= " autocomplete='" . $field['autocomplete'] . "'";
                            }
                            $bodyModal .= "></div>";
                            break;

                        case 'select':
                            $bodyModal .= "<div class='campo'>;
                            <legend>" . htmlspecialchars($field['label']);
                            if(!empty($field['require']) && $field['require'] == 'S') {
                                $bodyModal .= "<span class='required'>*</span>";
                            }
                            $bodyModal .= "</legend>";
                            foreach ($field['value'] as $value) {
                                $bodyModal .= "<div class='campoCheckBox'><label for='" . htmlspecialchars($field['data']) . "'>" . htmlspecialchars($value['label']) . "</label>
                                                <input type='checkbox' id='" . htmlspecialchars($field['data']) . "' name='" . htmlspecialchars($field['data']) . "' value='" . htmlspecialchars($value['data']) . "'></div>";
                            }
                            $bodyModal .= "</div>";
                            break;

                        case 'check':
                            $bodyModal .= "<div class='campo'>
                                            <legend>" . htmlspecialchars($field['label']);
                            if(!empty($field['require']) && $field['require'] == 'S') {
                                $bodyModal .= "<span class='required'>*</span>";
                            }
                            $bodyModal .= "</legend>";
                            foreach ($field['value'] as $value) {
                                $bodyModal .= "<div class='campoMarcacao'>
                                                <label for='" . htmlspecialchars($value['data']) . "'>" . htmlspecialchars($value['label']) . "
                                                <input type='radio' id='" . htmlspecialchars($value['data']) . "' name='" . htmlspecialchars($field['data']) . "' value='" . htmlspecialchars($value['data']) . "'>
                                               </label></div>";
                            }
                            $bodyModal .= "</div>";
                            break;
                    }
                }
            }

            $bodyModal .= "</div>";

            return $bodyModal;
        }

        private function newForm($data, $grid = null) {
            $form;
            $form = "<form class='content' data-form='" . $data['ObjForm'] . "'>
                        <div class='content_head'>
                            <p class='title'>" . $data['Title'] . "</p>";


            // Renderiza botões
            if (!empty($data['Button'])) {
                $form .= "<div class='buttom_group_data'>";
                foreach ($data['Button'] as $button) {
                    $form .= "<button class='buttom_css' type='button' data='btn " . $button['data'] . "'>" . $button['label'] . "</button>";
                }
                $form .= "</div>";
            }
            $form .= "</div>";

            $form .= "<div data-form='" . $data['ObjForm'] . "' data='gridContainer'>";
            $form .= $grid;
            $form .= "</div>";


            //Modal Fora do Formulário Principal
            $form .= "<div id='modal' class='modal'><div class='modal-content'>";
            $config = $this->getModalConfig($data['ObjForm']);

            if (!$config) {
                return 'Modal não suportado.';
            }

            $form .= $this->modalHead($config);
            $form .= $this->modalBody($config);
            
            $form .= "</div></div></form>";
            return $form;
        }

        public function produtos() {
            return ['status' => 'success', 'message' => $this->newForm([
                'ObjForm' => 'produtos',
                'Title' => 'Produtos',
                'Button' => [
                    ['data' => 'novo', 'label' => 'Novo'],
                    ['data' => 'editar', 'label' => 'Editar'],
                    ['data' => 'deletar', 'label' => 'Deletar']
                ]
            ], $this->generateGrid([
                'id' => 'ID',
                'nome' => 'Produto',
                'preco' => 'Preço',
                'quantidade' => 'Quant.'
            ], []))];
        }

        public function cupons() {
            return ['status' => 'success', 'message' => $this->newForm([
                'ObjForm' => 'cupons',
                'Title' => 'Cupons',
                'Button' => [
                    ['data' => 'novo', 'label' => 'Novo'],
                    ['data' => 'editar', 'label' => 'Editar'],
                    ['data' => 'inutilizar', 'label' => 'Inutilizar']
                ]
            ], $this->generateGrid([
                'id' => 'ID',
                'codigo' => 'Codigo',
                'valor_desconto' => 'Valor',
                'tipo_desconto' => 'Tipo',
                'validade' => 'Validade',
                'usado' => 'Ativo'
            ], []))];
        }

        public function logs_compras() {
            return ['status' => 'success', 'message' => $this->newForm([
                'ObjForm' => 'logs_compras',
                'Title' => 'Compras',
                'Button' => [
                ]
            ], $this->generateGrid([
                'id' => 'ID',
                'compra_id' => 'Compra',
                'acao' => 'Status',
                'data_log' => 'Data'
            ], []))];
        }

        public function usuarios() {
            return ['status' => 'success', 'message' => $this->newForm([
                'ObjForm' => 'usuarios',
                'Title' => 'Usuarios',
                'Button' => [
                    ['data' => 'novo', 'label' => 'Novo'],
                    ['data' => 'editar', 'label' => 'Editar'],
                    ['data' => 'status', 'label' => 'Inativar/Ativar']
                ]
            ], $this->generateGrid([
                'id' => 'ID',
                'nome' => 'Nome',
                'email' => 'E-mail',
                'status' => 'Status'
            ], []))];
        }

        public function compras() {
            return ['status' => 'success', 'message' => $this->newForm([
                'ObjForm' => 'compras',
                'Title' => 'Compras',
                'Button' => [
                    ['data' => 'imprimir', 'label' => 'Imprimir Comprovante']
                ]
            ], 
            $this->generateGrid([
                'id' => 'ID',
                'cliente_id' => 'Cliente',
                'produto_id' => 'Produto',
                'data_compra' => 'Data de Compra'
            ], []))];
        }

        private function getModalConfig(string $table): ?array {
            $configs = [
                'usuarios' => [
                    // Essa relação chave valor tem haver com o caso de onde tiver alguma alteração na forma que os dados são retornados do front-end
                    'head' => [
                        'close' => [
                            'data' => 'fecharModal'
                        ],
                        'titleModal' => [
                            'titleName' => 'Usuario'
                        ],
                        'headButton' => [
                            'ativo' => 'Sim',
                            'buttons' => [
                                ['data' => 'novo', 'Name' => 'Novo'],
                                ['data' => 'salvar', 'Name' => 'Salvar']
                            ]
                        ]
                    ],
                    'body' => [
                        'fields' => [
                            ['type' => 'text', 'label' => 'ID', 'data' => 'id', 'disabled' => 'S'],
                            ['type' => 'text', 'label' => 'Nome', 'data' => 'nome', 'require' => 'S'],
                            ['type' => 'text', 'label' => 'E-mail', 'data' => 'email', 'require' => 'S', 'autocomplete' => 'email'],
                            ['type' => 'text', 'label' => 'Senha', 'data' => 'senha', 'require' => 'S']
                        ]
                    ]
                ],
                'produtos' => [
                    'head' => [
                        'close' => [
                            'data' => 'fecharModal'
                        ],
                        'titleModal' => [
                            'titleName' => 'Produto'
                        ],
                        'headButton' => [
                            'ativo' => 'Sim',
                            'buttons' => [
                                ['data' => 'novo', 'Name' => 'Novo'],
                                ['data' => 'salvar', 'Name' => 'Salvar'],
                                ['data' => 'deletar', 'Name' => 'Deletar']
                            ]
                        ]
                    ],
                    'body' => [
                        'fields' => [
                            ['type' => 'text', 'label' => 'ID', 'data' => 'id', 'disabled' => 'S'],
                            ['type' => 'text', 'label' => 'Nome', 'data' => 'nome', 'require' => 'S'],
                            ['type' => 'text', 'label' => 'Descrição', 'data' => 'descricao'],
                            ['type' => 'text', 'label' => 'Preço (R$)', 'data' => 'preco', 'require' => 'S'],
                            ['type' => 'number', 'label' => 'Quantidade', 'data' => 'quantidade', 'require' => 'S']
                        ]
                    ]
                ],
                'cupons' => [
                    'head' => [
                        'close' => [
                            'data' => 'fecharModal'
                        ],
                        'titleModal' => [
                            'titleName' => 'Cupom'
                        ],
                        'headButton' => [
                            'ativo' => 'Sim',
                            'buttons' => [
                                ['data' => 'novo', 'Name' => 'Novo'],
                                ['data' => 'salvar', 'Name' => 'Salvar']
                            ]
                        ]
                    ],
                    'body' => [
                        'fields' => [
                            ['type' => 'text', 'label' => 'ID', 'data' => 'id', 'disabled' => 'S'],
                            ['type' => 'text', 'label' => 'Codigo', 'data' => 'codigo', 'require' => 'S'],
                            ['type' => 'check', 'label' => 'Tipo', 'data' => 'tipo_desconto', 'require' => 'S', 'value' => [['data' => 'fixo', 'label' => 'Fixo'], ['data' => 'percentual', 'label' => 'Percentual']]],
                            ['type' => 'text', 'label' => 'Valor', 'data' => 'valor_desconto', 'require' => 'S'],
                            ['type' => 'date', 'label' => 'Validade', 'data' => 'validade', 'require' => 'S'],
                        ]
                    ]
                ],
                'logs_compras' => [
                    'head' => [
                        'close' => [
                            'data' => 'fecharModal'
                        ],
                        'titleModal' => [
                            'titleName' => 'Compra'
                        ],
                        'headButton' => [
                            'ativo' => 'Sim',
                            'buttons' => [
                                ['data' => 'imprimir', 'Name' => 'Imprimir']
                            ]
                        ]
                    ],
                    'body' => [
                        'fields' => [
                            ['type' => 'text', 'label' => 'ID', 'data' => 'id', 'disabled' => 'S'],
                            ['type' => 'text', 'label' => 'Compra', 'data' => 'compra_id', 'disabled' => 'S'],
                            ['type' => 'text', 'label' => 'Status', 'data' => 'acao', 'disabled' => 'S'],
                            ['type' => 'date', 'label' => 'Data', 'data' => 'data_log', 'disabled' => 'S'],
                            ['type' => 'text', 'label' => 'Observações', 'data' => 'observacoes', 'disabled' => 'S']
                        ]
                    ]
                ],
                'compras' => [
                    'head' => [
                        'close' => [
                            'data' => 'fecharModal'
                        ],
                        'titleModal' => [
                            'titleName' => 'Compra'
                        ],
                        'headButton' => [
                            'ativo' => 'Sim',
                            'buttons' => [
                                ['data' => 'imprimir', 'Name' => 'Imprimir']
                            ]
                        ]
                    ],
                    'body' => [
                        'fields' => [
                        ]
                    ]
                ]
            ];

            return $configs[$table] ?? null;
        }
    }
?>