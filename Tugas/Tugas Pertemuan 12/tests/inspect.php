<?php
// Pemeriksaan baca-saja untuk basis data uji browser.
if (PHP_SAPI !== 'cli' || getenv('PBKK12_TEST_RUN') !== '1') {
    exit(2);
}
$dsn = getenv('PBKK12_TEST_DSN');
if (!is_string($dsn) || $dsn === '') {
    fwrite(STDERR, "Isi PBKK12_TEST_DSN untuk database praktikum sementara.\n");
    exit(2);
}
$db = new PDO($dsn, getenv('PBKK12_TEST_USER') ?: null, getenv('PBKK12_TEST_PASSWORD') ?: null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$mysql = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
$checks = [];
foreach (['categories', 'products', 'product_images'] as $table) {
    $checks[$table] = (int) $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
}
$checks['version'] = $db->query($mysql ? 'SELECT VERSION()' : 'SELECT sqlite_version()')->fetchColumn();
$checks['driver'] = $mysql ? 'MySQL' : 'SQLite';
$checks['foreign_keys'] = $mysql
    ? $db->query("SELECT TABLE_NAME AS child_table, REFERENCED_TABLE_NAME AS parent_table, UPDATE_RULE AS on_update, DELETE_RULE AS on_delete FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME IN ('products', 'product_images') ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC)
    : array_merge($db->query('PRAGMA foreign_key_list(products)')->fetchAll(PDO::FETCH_ASSOC), $db->query('PRAGMA foreign_key_list(product_images)')->fetchAll(PDO::FETCH_ASSOC));
$checks['invalid_primary_groups'] = (int) $db->query('SELECT COUNT(*) FROM (SELECT id_product FROM product_images GROUP BY id_product HAVING SUM(is_primary) <> 1) AS bad')->fetchColumn();
echo json_encode($checks, JSON_THROW_ON_ERROR);
