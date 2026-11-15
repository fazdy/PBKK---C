<?php
// Test helper: only the disposable socket created by test_mysql.py is accepted.
$socket = getenv('SHOPEASE_TEST_MYSQL_SOCKET');
if (!$socket || !str_starts_with($socket, '/')) { throw new RuntimeException('Socket pengujian diperlukan.'); }
$db = new mysqli('localhost', 'root', '', 'db_shopease_test', 0, $socket);
$db->set_charset('utf8mb4');
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$result = $db->execute_query($input['sql'], $input['params']);
echo json_encode($result instanceof mysqli_result ? $result->fetch_all(MYSQLI_ASSOC) : [], JSON_THROW_ON_ERROR);
