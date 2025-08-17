<?php
require_once __DIR__ . '/../db.php';

$config = require __DIR__ . '/../config.php';
$DEBUG = !empty($config['api']['debug']);

try {
    try {
        $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','')");
        $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE','')");
        $pdo->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'NO_ZERO_IN_DATE','')");
        $pdo->exec("SET time_zone = '+00:00'");
    } catch (Throwable $tmp) {
    }

    $monthParam  = $_GET['month']  ?? 'all';
    $statusParam = $_GET['status'] ?? 'all';

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

    $sql = "
        SELECT
          TRIM(CONCAT(o.first_name, ' ', o.last_name)) AS client_name,
          o.email AS email,
          COUNT(*) AS orders_count,
          COALESCE(SUM(o.total), 0) AS total_amount
        FROM orders o
        $sqlWhere
        GROUP BY o.first_name, o.last_name, o.email
        ORDER BY total_amount DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totals = ['orders_count' => 0, 'total_amount' => 0.0];
    foreach ($rows as $r) {
        $totals['orders_count'] += (int)$r['orders_count'];
        $totals['total_amount'] += (float)$r['total_amount'];
    }

    $payload = [
        'filters' => [
            'month'  => ($monthParam === 'all') ? 'all' : (int)$monthParam,
            'status' => $statusParam,
        ],
        'rows'   => $rows,
        'totals' => $totals,
    ];

    if ($DEBUG) {
        $payload['_debug'] = [
            'sql'    => $sql,
            'params' => $params,
        ];
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[ORDERS PHP] '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());

    http_response_code(500);
    $err = ['error' => 'Query error'];
    if ($DEBUG) {
        $err['detail'] = $e->getMessage();
        $err['trace_at'] = $e->getFile().':'.$e->getLine();
    }
    echo json_encode($err, JSON_UNESCAPED_UNICODE);
}
