<?php
// backend/routes/orders.php
require_once __DIR__ . '/../db.php';

$config = require __DIR__ . '/../config.php';
$DEBUG = !empty($config['api']['debug']);

try {
    // (Robustez) Relajar sql_mode por si el init global no aplicó
    try {
        $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','')");
        $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE','')");
        $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'NO_ZERO_IN_DATE','')");
        $pdo->exec("SET time_zone = '+00:00'");
    } catch (Throwable $e) {
        // Ignorar si no se puede cambiar; no detiene la ejecución.
    }

    // ===== Filtros =====
    $monthParam  = $_GET['month']  ?? 'all';
    $statusParam = $_GET['status'] ?? 'all';

    // ===== Paginación =====
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $perPage  = (int)($_GET['per_page'] ?? 10);
    if ($perPage <= 0) $perPage = 10;
    if ($perPage > 100) $perPage = 100; // tope sano

    $where  = [];
    $params = [];

    if ($monthParam !== 'all') {
        $where[] = 'MONTH(o.date_placed) = :month';
        $params[':month'] = (int)$monthParam;
    }

    if ($statusParam !== 'all') {
        $allowed = [0,3,4];
        $st = (int)$statusParam;
        if (!in_array($st, $allowed, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $where[] = 'o.status = :status';
        $params[':status'] = $st;
    } else {
        $where[] = 'o.status IN (0,3,4)';
    }

    $sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // ===== Conteo total de grupos (clientes) para paginación =====
    // MySQL 8 permite COUNT(DISTINCT col1, col2, col3)
    $countSql = "
        SELECT COUNT(DISTINCT o.first_name, o.last_name, o.email) AS total_rows
        FROM orders o
        $sqlWhere
    ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRows = (int)($countStmt->fetchColumn() ?: 0);

    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    if ($page > $totalPages) $page = $totalPages;
    $offset = ($page - 1) * $perPage;

    // ===== Totales globales (sobre TODOS los resultados filtrados, no solo la página) =====
    // Más eficiente: directamente sobre orders sin agrupar por cliente.
    $aggSql = "
        SELECT COUNT(*) AS orders_count,
               COALESCE(SUM(o.total), 0) AS total_amount
        FROM orders o
        $sqlWhere
    ";
    $aggStmt = $pdo->prepare($aggSql);
    $aggStmt->execute($params);
    $agg = $aggStmt->fetch(PDO::FETCH_ASSOC);
    $totalsAll = [
        'orders_count' => (int)($agg['orders_count'] ?? 0),
        'total_amount' => (float)($agg['total_amount'] ?? 0.0),
    ];

    // ===== Página de filas (agrupadas por cliente) =====
    $rowsSql = "
        SELECT
          TRIM(CONCAT(o.first_name, ' ', o.last_name)) AS client_name,
          o.email AS email,
          COUNT(*) AS orders_count,
          COALESCE(SUM(o.total), 0) AS total_amount
        FROM orders o
        $sqlWhere
        GROUP BY o.first_name, o.last_name, o.email
        ORDER BY total_amount DESC
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $pdo->prepare($rowsSql);

    // Bind de params de filtro
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    // Bind de paginación (enteros)
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $payload = [
        'filters' => [
            'month'  => ($monthParam === 'all') ? 'all' : (int)$monthParam,
            'status' => $statusParam,
        ],
        'pagination' => [
            'page'        => $page,
            'per_page'    => $perPage,
            'total_rows'  => $totalRows,
            'total_pages' => $totalPages,
        ],
        'rows'   => $rows,
        'totals' => $totalsAll, // totales globales sobre todos los resultados filtrados
    ];

    if ($DEBUG) {
        $payload['_debug'] = [
            'count_sql' => $countSql,
            'agg_sql'   => $aggSql,
            'rows_sql'  => $rowsSql,
            'params'    => $params,
        ];
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[ORDERS PHP] '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
    http_response_code(500);
    $err = ['error' => 'Query error'];
    if ($DEBUG) {
        $err['detail'] = $e->getMessage();
    }
    echo json_encode($err, JSON_UNESCAPED_UNICODE);
}
