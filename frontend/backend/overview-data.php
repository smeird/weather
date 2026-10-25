<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../dbconn.php';
require_once __DIR__ . '/../lib/dashboard-data.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');
try {
  echo json_encode(dashboard_overview($link), JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
  error_log('Overview data failed: ' . $error->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'Unable to load observations']);
}
