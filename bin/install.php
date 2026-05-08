#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * First-run installer for Quiz Practice System.
 *   - Initializes the SQLite database from schema.sql (if empty)
 *   - Seeds a super-admin account
 *
 * Usage:
 *   php bin/install.php
 *   php bin/install.php --username=admin --password=admin123 --name="Administrator"
 */

$root = dirname(__DIR__);
chdir($root);

$opts = getopt('', ['username::', 'password::', 'name::', 'force']);
$username = $opts['username'] ?? 'admin';
$password = $opts['password'] ?? 'admin123';
$name     = $opts['name']     ?? 'Administrator';
$force    = array_key_exists('force', $opts);

$dbPath  = $root . '/database/quiz_system.sqlite';
$schema  = $root . '/database/schema.sql';

if (!is_file($schema)) {
    fwrite(STDERR, "schema.sql not found at {$schema}\n");
    exit(1);
}

$fresh = !is_file($dbPath) || filesize($dbPath) === 0;

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($fresh) {
    echo "→ Initializing database from schema.sql …\n";
    $pdo->exec(file_get_contents($schema));
}

$exists = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$exists->execute([$username]);
$row = $exists->fetch(PDO::FETCH_ASSOC);

if ($row && !$force) {
    echo "✓ User '{$username}' already exists (id={$row['id']}). Use --force to reset the password.\n";
    exit(0);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($row) {
    $pdo->prepare('UPDATE users SET password_hash=?, display_name=?, role=?, status=1 WHERE id=?')
        ->execute([$hash, $name, 'super_admin', $row['id']]);
    echo "✓ Reset super-admin '{$username}' (id={$row['id']}).\n";
} else {
    $pdo->prepare('INSERT INTO users (username,password_hash,display_name,role,status) VALUES (?,?,?,?,1)')
        ->execute([$username, $hash, $name, 'super_admin']);
    echo "✓ Created super-admin '{$username}' (id=" . $pdo->lastInsertId() . ").\n";
}

echo "  Username: {$username}\n";
echo "  Password: {$password}\n";
echo "  Login at: /login\n";
echo "⚠  Please change the password after first login.\n";
