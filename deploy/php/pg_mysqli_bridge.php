<?php
/**
 * Minimal mysqli-compatible wrapper over PostgreSQL for the legacy PHP admin.
 * Lets /var/www/html/admin keep using $conn->query() against the same DB as Express/Prisma.
 */

declare(strict_types=1);

final class PgMysqliResult
{
    /** @var list<array<string, mixed>> */
    private array $rows;
    private int $index = 0;

    public int $num_rows;

    /** @param list<array<string, mixed>> $rows */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
        $this->num_rows = count($rows);
    }

    /** @return array<string, mixed>|null */
    public function fetch_assoc(): ?array
    {
        if ($this->index >= $this->num_rows) {
            return null;
        }
        return $this->rows[$this->index++];
    }

    /** @return list<array<string, mixed>> */
    public function fetch_all(int $mode = MYSQLI_ASSOC): array
    {
        unset($mode);
        return $this->rows;
    }
}

final class PgMysqliStmt
{
    private \PgSql\Connection $pg;
    private string $sql;
    /** @var list<string> */
    private array $types = [];
    /** @var list<mixed> */
    private array $values = [];
    /** @var list<array<string, mixed>> */
    private array $rows = [];
    /** @var list<mixed> */
    private array $boundRefs = [];
    private int $rowIndex = 0;
    public int $affected_rows = 0;
    public int $insert_id = 0;
    public int $num_rows = 0;

    public function __construct(\PgSql\Connection $pg, string $sql)
    {
        $this->pg = $pg;
        $this->sql = pg_mysqli_translate_sql(pg_mysqli_convert_placeholders($sql));
    }

    public function bind_param(string $types, &...$vars): bool
    {
        $this->types = str_split($types);
        $this->values = [];
        foreach ($vars as $var) {
            $this->values[] = $var;
        }
        return true;
    }

    public function execute(): bool
    {
        $params = $this->values;
        $result = $params
            ? @pg_query_params($this->pg, $this->sql, $params)
            : @pg_query($this->pg, $this->sql);

        if ($result === false) {
            return false;
        }

        $this->affected_rows = pg_affected_rows($result);

        if (pg_num_fields($result) > 0) {
            $this->rows = pg_fetch_all($result, PGSQL_ASSOC) ?: [];
            $this->num_rows = count($this->rows);
            $this->rowIndex = 0;
        } else {
            $this->rows = [];
            $this->num_rows = 0;
        }

        if (preg_match('/^\s*INSERT\s+/i', $this->sql) && !preg_match('/\bRETURNING\b/i', $this->sql)) {
            $idRow = pg_fetch_assoc(@pg_query($this->pg, 'SELECT LASTVAL() AS id'));
            if ($idRow && isset($idRow['id'])) {
                $this->insert_id = (int) $idRow['id'];
            }
        }

        pg_free_result($result);
        return true;
    }

    public function store_result(): bool
    {
        return true;
    }

    public function bind_result(mixed &...$vars): bool
    {
        $this->boundRefs = $vars;
        return true;
    }

    public function fetch(): bool
    {
        if ($this->rowIndex >= $this->num_rows) {
            return false;
        }

        $row = array_values($this->rows[$this->rowIndex++]);
        foreach ($this->boundRefs as $i => &$ref) {
            $ref = $row[$i] ?? null;
        }
        return true;
    }

    public function get_result(): PgMysqliResult
    {
        return new PgMysqliResult($this->rows);
    }

    public function close(): void
    {
    }
}

final class PgMysqli
{
    private \PgSql\Connection $pg;
    public ?string $connect_error = null;
    public int $insert_id = 0;
    public int $affected_rows = 0;
    public int $errno = 0;
    public string $error = '';

    public function __construct(\PgSql\Connection $pg)
    {
        $this->pg = $pg;
    }

    public function query(string $sql): PgMysqliResult|bool
    {
        $trimmed = trim($sql);

        if (preg_match("/^SHOW TABLES LIKE '([^']+)'$/i", $trimmed, $m)) {
            $table = $m[1];
            $check = pg_query_params(
                $this->pg,
                "SELECT table_name FROM information_schema.tables
                 WHERE table_schema = 'public' AND table_name = $1 LIMIT 1",
                [$table],
            );
            $rows = $check ? (pg_fetch_all($check, PGSQL_ASSOC) ?: []) : [];
            if ($check) {
                pg_free_result($check);
            }
            return new PgMysqliResult($rows);
        }

        if (preg_match('/^SHOW COLUMNS FROM\s+([`"]?)(\w+)\1\s+LIKE\s+\'([^\']+)\'/i', $trimmed, $m)) {
            $table = $m[2];
            $column = $m[3];
            $check = pg_query_params(
                $this->pg,
                "SELECT column_name FROM information_schema.columns
                 WHERE table_schema = 'public' AND table_name = $1 AND column_name = $2 LIMIT 1",
                [$table, $column],
            );
            $rows = $check ? (pg_fetch_all($check, PGSQL_ASSOC) ?: []) : [];
            if ($check) {
                pg_free_result($check);
            }
            return new PgMysqliResult($rows);
        }

        // Skip MySQL-only bootstrap DDL when tables already exist in Postgres.
        if (preg_match('/^(CREATE TABLE|ALTER TABLE|INSERT INTO `admin_users`)/i', $trimmed)) {
            return true;
        }

        $translated = pg_mysqli_translate_sql($trimmed);
        $result = @pg_query($this->pg, $translated);
        if ($result === false) {
            $this->errno = 1;
            $this->error = (string) pg_last_error($this->pg);
            return false;
        }

        $this->affected_rows = pg_affected_rows($result);

        if (preg_match('/^\s*INSERT\s+/i', $translated) && !preg_match('/\bRETURNING\b/i', $translated)) {
            $idRow = pg_fetch_assoc(@pg_query($this->pg, 'SELECT LASTVAL() AS id'));
            if ($idRow && isset($idRow['id'])) {
                $this->insert_id = (int) $idRow['id'];
            }
        }

        if (pg_num_fields($result) > 0) {
            $rows = pg_fetch_all($result, PGSQL_ASSOC) ?: [];
            pg_free_result($result);
            return new PgMysqliResult($rows);
        }

        pg_free_result($result);
        return true;
    }

    public function prepare(string $sql): PgMysqliStmt|false
    {
        return new PgMysqliStmt($this->pg, $sql);
    }

    public function real_escape_string(string $value): string
    {
        return pg_escape_string($this->pg, $value);
    }

    public function close(): void
    {
        @pg_close($this->pg);
    }

    public function getError(): string
    {
        return $this->error !== '' ? $this->error : (string) pg_last_error($this->pg);
    }
}

function pg_mysqli_translate_sql(string $sql): string
{
    $sql = str_replace('`', '"', $sql);
    $sql = preg_replace('/\bIFNULL\s*\(/i', 'COALESCE(', $sql) ?? $sql;
    $sql = preg_replace('/\bNOW\s*\(\s*\)/i', 'CURRENT_TIMESTAMP', $sql) ?? $sql;
    $sql = preg_replace('/\s+ENGINE\s*=\s*InnoDB\b/i', '', $sql) ?? $sql;
    $sql = preg_replace('/\s+DEFAULT CHARSET\s*=\s*\w+/i', '', $sql) ?? $sql;
    $sql = preg_replace('/\s+COLLATE\s+\w+/i', '', $sql) ?? $sql;
    $sql = preg_replace('/\bint\s*\(\s*11\s*\)/i', 'INTEGER', $sql) ?? $sql;
    $sql = preg_replace('/\bvarchar\s*\(\s*(\d+)\s*\)/i', 'VARCHAR($1)', $sql) ?? $sql;
    $sql = preg_replace('/\bdatetime\b/i', 'TIMESTAMP', $sql) ?? $sql;
    $sql = preg_replace('/\bUNSIGNED\b/i', '', $sql) ?? $sql;
    return trim($sql);
}

function pg_mysqli_convert_placeholders(string $sql): string
{
    $index = 0;
    return preg_replace_callback('/\?/', static function () use (&$index): string {
        $index += 1;
        return '$' . $index;
    }, $sql) ?? $sql;
}

function pg_mysqli_connect_from_env(): PgMysqli
{
    $databaseUrl = pg_mysqli_read_database_url();
    if (!$databaseUrl) {
        throw new RuntimeException('DATABASE_URL not configured for PHP admin');
    }

    $parts = parse_url($databaseUrl);
    if (!$parts || empty($parts['host']) || empty($parts['user'])) {
        throw new RuntimeException('Invalid DATABASE_URL for PHP admin');
    }

    $host = $parts['host'];
    $port = $parts['port'] ?? '5432';
    $user = rawurldecode($parts['user']);
    $pass = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';
    $db = ltrim($parts['path'] ?? '/postgres', '/');

    $connStr = sprintf('host=%s port=%s dbname=%s user=%s password=%s', $host, $port, $db, $user, $pass);
    $pg = @pg_connect($connStr);
    if ($pg === false) {
        throw new RuntimeException('PostgreSQL connection failed for PHP admin');
    }

    return new PgMysqli($pg);
}

function pg_mysqli_read_database_url(): ?string
{
    $fromEnv = getenv('DATABASE_URL');
    if (is_string($fromEnv) && $fromEnv !== '') {
        return preg_replace('/\?.*$/', '', $fromEnv) ?: $fromEnv;
    }

    $candidates = [
        '/var/www/saiflower-vps/apps/server/.env',
        '/var/www/saiflower-vps/packages/prisma/.env',
        __DIR__ . '/../../apps/server/.env',
    ];

    foreach ($candidates as $file) {
        if (!is_readable($file)) {
            continue;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (str_starts_with(trim($line), 'DATABASE_URL=')) {
                $value = trim(substr($line, strlen('DATABASE_URL=')), " \t\"'");
                return preg_replace('/\?.*$/', '', $value) ?: $value;
            }
        }
    }

    return null;
}

function pg_mysqli_connect(): PgMysqli
{
    return pg_mysqli_connect_from_env();
}

/** @param PgMysqli $conn */
function mysqli_query(mysqli|PgMysqli $conn, string $sql): PgMysqliResult|bool
{
    return $conn->query($sql);
}

/** @param PgMysqliResult $result */
function mysqli_fetch_assoc(PgMysqliResult|PgMysqli $result): ?array
{
    return $result->fetch_assoc();
}

/** @param PgMysqliResult $result */
function mysqli_num_rows(PgMysqliResult $result): int
{
    return $result->num_rows;
}

/** @param PgMysqli $conn */
function mysqli_real_escape_string(mysqli|PgMysqli $conn, string $value): string
{
    return $conn->real_escape_string($value);
}

/** @param PgMysqli $conn */
function mysqli_error(mysqli|PgMysqli $conn): string
{
    return $conn->getError();
}

function mysqli_connect_error(): string
{
    return '';
}

function mysqli_report(int $flags): bool
{
    unset($flags);
    return true;
}
