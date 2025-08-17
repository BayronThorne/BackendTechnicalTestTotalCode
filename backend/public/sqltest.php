<?php
header('Content-Type: application/json; charset=utf-8');
try {
  require __DIR__ . '/../db.php';
  $r = $pdo->query("SELECT
      TRIM(CONCAT(first_name,' ',last_name)) AS client_name,
      email,
      COUNT(*) AS orders_count,
      SUM(total) AS total_amount
    FROM orders
    WHERE status IN (0,3,4) AND MONTH(date_placed)=10
    GROUP BY client_name, email
    ORDER BY total_amount DESC
  ")->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['ok'=>true,'rows'=>$r], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'detail'=>$e->getMessage()]);
}
