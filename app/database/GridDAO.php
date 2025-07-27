<?php
    namespace App\database;

    use PDO;
    use PDOException;
    use Exception;
    use App\database\Connection;

    class GridDAO {
        private static int $paramUser = 0;

        public static function getDataGrid(
            string $filtro,
            ?string $resource,
            string $table,
            array $validFilters,
            int $ItensForPage,
            int $page
        ) {
            $conn = Connection::Connect();

            if (!array_key_exists($filtro, $validFilters)) {
                throw new Exception("Filtro inválido.");
            }

            $column = $validFilters[$filtro];
            $offset = ($page - 1) * $ItensForPage;
            $sqlBase = "FROM $table WHERE 1=1";

            $paramValue = null;
            $applyFilter = $resource !== null && $resource !== '';

            if ($applyFilter) {
                if ($filtro === 'id') {
                    $sqlBase .= " AND $column = ?";
                    $paramValue = (int)$resource;

                } else if ($filtro === 'status' || $filtro === 'ativo') {
                    $value = strtoupper($resource);
                    $paramValue = $value === 'SIM' ? 1 : 0;
                    $sqlBase .= " AND $column = ?";

                } else if($filtro === 'uso_unico' || $filtro === 'usado') {
                    $value = strtoupper($resource);
                    $paramValue = $value === 'SIM' ? 0 : 1;
                    $sqlBase .= " AND $column = ?";
                    
                } else {
                    $sqlBase .= " AND $column LIKE ?";
                    $paramValue = "%" . $resource . "%";
                }
            }

            if ($table == 'produtos' || $table == 'cupons' || $table == 'compras') {
                $sqlBase .= " AND usuario_id = ?";
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                self::$paramUser = (int) $_SESSION['id_user'];
            }

            // Campos projetados
            if ($table === 'usuarios') {
                $selectFields = "id, nome, email, CASE WHEN status = 1 THEN 'Não' ELSE 'Sim' END AS status";
            } else if ($table === 'cupons') {
                $selectFields = "id, codigo, valor_desconto, tipo_desconto, validade, CASE WHEN usado = 1 THEN 'Não' ELSE 'Sim' END AS status";
            } else {
                $selectFields = implode(', ', array_values($validFilters));
            }

            // --------- Consulta de dados ---------
            $query = "SELECT $selectFields " . $sqlBase . " LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($query);

            $paramIndex = 1;
            if ($applyFilter) {
                $stmt->bindValue($paramIndex++, $paramValue);
            }
            if ($table == 'produtos' || $table == 'cupons' || $table == 'compras') {
                $stmt->bindValue($paramIndex++, self::$paramUser);
            }
            $stmt->bindValue($paramIndex++, $ItensForPage, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // --------- Contagem total ---------
            $countQuery = "SELECT COUNT(*) as total " . $sqlBase;
            $stmtCount = $conn->prepare($countQuery);

            $paramIndex = 1;
            if ($applyFilter) {
                $stmtCount->bindValue($paramIndex++, $paramValue);
            }
            if ($table == 'produtos' || $table == 'cupons' || $table == 'compras') {
                $stmtCount->bindValue($paramIndex++, self::$paramUser);
            }

            $stmtCount->execute();
            $countResult = $stmtCount->fetch(PDO::FETCH_ASSOC);
            $totalRows = $countResult['total'];

            $conn = null;

            $totalPages = ceil($totalRows / $ItensForPage);

            return [
                'data' => $data,
                'totalPages' => $totalPages
            ];
        }
    }
?>