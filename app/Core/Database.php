<?php

declare(strict_types=1);

namespace App\Core;

class Database
{
    private static ?self $instance = null;
    private \PDO $pdo;

    private function __construct(array $config)
    {
        $dbPath = $config['path'] ?? dirname(__DIR__, 2) . '/database/quiz_system.sqlite';
        $dbDir = dirname($dbPath);

        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        if (file_exists($dbPath) && !is_writable($dbPath)) {
            throw new \RuntimeException(
                "Database file is not writable: {$dbPath}\n" .
                "Fix: chown www:www {$dbPath} {$dbDir} && chmod 664 {$dbPath} && chmod 775 {$dbDir}"
            );
        }

        if (!is_writable($dbDir)) {
            throw new \RuntimeException(
                "Database directory is not writable: {$dbDir}\n" .
                "SQLite WAL mode needs to create -wal and -shm files in this directory.\n" .
                "Fix: chown www:www {$dbDir} && chmod 775 {$dbDir}"
            );
        }

        $this->pdo = new \PDO('sqlite:' . $dbPath, null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $this->pdo->exec('PRAGMA journal_mode = WAL');
        $this->pdo->exec('PRAGMA foreign_keys = ON');
        $this->pdo->exec('PRAGMA busy_timeout = 5000');
    }

    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function pdo(): \PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->query($sql, [...array_values($data), ...$whereParams]);
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function count(string $table, string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as cnt FROM {$table} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return (int) ($result['cnt'] ?? 0);
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function paginate(string $sql, array $params, int $page, int $perPage): array
    {
        $countSql = preg_replace('/SELECT .+? FROM/i', 'SELECT COUNT(*) as cnt FROM', $sql, 1);
        $countSql = preg_replace('/ORDER BY .+$/i', '', $countSql);
        $total = (int) ($this->fetch($countSql, $params)['cnt'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }
}
