<?php

function columnExists($pdo, $table, $column) {
  $stmt = $pdo->prepare("SELECT * FROM pragma_table_info(:table) WHERE name = :column");
  $stmt->execute(['table' => $table, 'column' => $column]);
  return $stmt->fetch() !== false;
}

$dbConnString = $_ENV["DATABASE_CONN_STRING"] ?? "sqlite:" . dirname(__DIR__) . '/database/db.sqlite';

try {
  $pdo = new PDO($dbConnString);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Create tables
  $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
      id INTEGER PRIMARY KEY,
      name TEXT NOT NULL,
  vat_number TEXT,
  federal_id TEXT NOT NULL,
      address TEXT NOT NULL,
      UNIQUE(name)
      -- add other fields as needed
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
      id INTEGER PRIMARY KEY,
      key TEXT NOT NULL,
      value TEXT NOT NULL
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
      id INTEGER PRIMARY KEY,
      key TEXT NOT NULL,
      value TEXT NOT NULL
  )");

  // SQLite does not have a boolean type :)
  $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
      id INTEGER PRIMARY KEY,
      invoice_number TEXT NOT NULL,
      client_id INTEGER NOT NULL,
      po_reference TEXT NOT NULL,
      issue_date TEXT NOT NULL,
      payed INTEGER NOT NULL DEFAULT 0,
      due_date TEXT NOT NULL,
      notes TEXT,
      discount REAL,
      FOREIGN KEY (client_id) REFERENCES clients(id)
  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS invoice_items (
      id INTEGER PRIMARY KEY,
      invoice_id INTEGER NOT NULL,
      description TEXT NOT NULL,
      subdescription TEXT,
      quantity INTEGER NOT NULL,
      price REAL NOT NULL,
      currency TEXT NOT NULL,
      FOREIGN KEY (invoice_id) REFERENCES invoice(id)
  )");
  $pdo->exec("CREATE TABLE IF NOT EXISTS currencies (
      id INTEGER PRIMARY KEY,
      iso_code TEXT NOT NULL,
      code_number INTEGER NOT NULL,
      decimals INTEGER NOT NULL,
      name TEXT NOT NULL
  )");
  if (columnExists($pdo, 'invoices', 'canceled') === false) {
    $pdo->exec("ALTER TABLE invoices ADD COLUMN canceled INTEGER NOT NULL DEFAULT 0");
  }
  // You can add more table creation statements here
} catch (PDOException $e) {
  echo $e->getMessage();
  exit;
}

?>