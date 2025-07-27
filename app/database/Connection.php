<?php
    namespace App\database;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    use PDO;
    use PDOException;

    class Connection
    {
        public static function Connect()
        {
            try {
                $pdo = new PDO("sqlite:" . __DIR__ . "/Database.sqlite");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::createTables($pdo);

                return $pdo;
            } catch (PDOException $e) {
                die("Erro ao conectar ao banco de dados: " . $e->getMessage());
                return null;
            }
        }

        private static function createTables(PDO $db)
        {
            $senha = '$2y$10$Fn6kfMSxke1lRS2WZV/Xf.g8q4SzzeJ7ts4dW.rrEg8Q7OV/5TxL.';
            $sql = "
            CREATE TABLE IF NOT EXISTS cupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                codigo TEXT NOT NULL UNIQUE,
                valor_desconto REAL NOT NULL,
                tipo_desconto TEXT CHECK(tipo_desconto IN ('percentual', 'fixo')) NOT NULL,
                validade DATETIME,
                usuario_id INTEGER NOT NULL,
                usado BOOLEAN DEFAULT 0,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
            );

            CREATE TABLE IF NOT EXISTS logs_compras (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                compra_id INTEGER,
                acao TEXT NOT NULL,
                data_log DATETIME DEFAULT CURRENT_TIMESTAMP,
                observacoes TEXT,
                FOREIGN KEY (compra_id) REFERENCES compras(id)
            );

            CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                senha TEXT NOT NULL,
                token TEXT UNIQUE,
                status INT NOT NULL
            );

            INSERT INTO usuarios (nome, email, senha, status)
            SELECT 'ADM', 'adm@adm.com', '$2y$10$" . "Fn6kfMSxke1lRS2WZV/Xf.g8q4SzzeJ7ts4dW.rrEg8Q7OV/5TxL.', '0'
            WHERE NOT EXISTS (
                SELECT 1 FROM usuarios WHERE id = 1
            );

            CREATE TABLE IF NOT EXISTS produtos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                descricao TEXT,
                preco REAL NOT NULL,
                quantidade INTEGER NOT NULL,
                data_reserva INTEGER,
                token TEXT,
                usuario_id INTEGER NOT NULL,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
            );

            CREATE TABLE IF NOT EXISTS clientes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                telefone TEXT
            );

            CREATE TABLE IF NOT EXISTS compras (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cep INTERGER,
                rua TEXT,
                numero INTERGER,
                complemento TEXT,
                cliente_id INTEGER NOT NULL,
                produto_id INTEGER NOT NULL,
                quantidade INTEGER NOT NULL,
                valor INTEGER NOT NULL,
                data_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
                usuario_id INTEGER NOT NULL,
                FOREIGN KEY (cliente_id) REFERENCES clientes(id),
                FOREIGN KEY (produto_id) REFERENCES produtos(id),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
            );
            ";

            try {
                $db->exec($sql);
                return true;

            } catch (PDOException $e) {
                echo "Erro ao criar tabelas: " . $e->getMessage();
                return false;

            }
        }
    }
?>
