<?php
namespace Core\Base;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Db
{
    protected static string $host     = '';
    protected static string $username = '';
    protected static string $password = '';
    protected static string $database = '';
    protected static string $charset  = 'utf8mb4';
    protected static ?PDO $connection = null;
    protected static ?PDOStatement  $statement = null;
    protected static ?string $table = null;

    public static function configure(): void
    {
        self::$host     = config('database', 'host') ?? '';
        self::$username = config('database', 'username') ?? '';
        self::$password = config('database', 'password') ?? '';
        self::$database = config('database', 'database') ?? '';
        self::$charset  = config('database', 'charset') ?? 'utf8mb4';
    }

    public static function connect(): ?PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        self::configure();

        if (empty(self::$host) || empty(self::$username) || empty(self::$database)) {
            return null;
        }

        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$database . ";charset=" . self::$charset;

        try {
            self::$connection = new PDO($dsn, self::$username, self::$password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_PERSISTENT         => true,
            ]);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage(), 500);
        }

        return self::$connection;
    }

    public static function disconnect(): void
    {
        self::$connection = null;
    }

    public static function table(string $table): self
    {
        self::$table = $table;
        return new self();
    }

    public static function query(string $query, array $params = []): self
    {
        if (self::$connection === null) {
            self::connect();
        }

        self::$statement = self::$connection->prepare($query);
        self::$statement->execute($params);

        return new self();
    }

    public function all(): array
    {
        return self::$statement ? self::$statement->fetchAll() : [];
    }

    public function first(): ?object
    {
        return self::$statement ? self::$statement->fetch() : null;
    }

    public function insert(array $data): int
    {
        if (empty($data)) {
            throw new Exception("No data provided for insert", 400);
        }

        $validColumns = self::getValidColumns();
        $filteredData = array_intersect_key($data, array_flip($validColumns));

        if (empty($filteredData)) {
            throw new Exception("No valid columns provided", 400);
        }

        $fields       = implode('`, `', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));

        $query = "INSERT INTO " . self::$table . " (`$fields`) VALUES ($placeholders)";
        self::query($query, $filteredData);

        return (int) self::$connection->lastInsertId();
    }

    public function update(array $data, int $id): int
    {
        if (empty($data)) {
            throw new Exception("No data provided for update", 400);
        }

        $validColumns = self::getValidColumns();
        $filteredData = array_intersect_key($data, array_flip($validColumns));

        if (empty($filteredData)) {
            throw new Exception("No valid columns provided", 400);
        }

        $fields = implode(', ', array_map(fn($key) => "`$key` = :$key", array_keys($filteredData)));
        $filteredData['id'] = $id;

        $query = "UPDATE " . self::$table . " SET $fields WHERE id = :id";
        self::query($query, $filteredData);
        
        return self::$statement->rowCount();
    }

    public function delete(int $id): int
    {
        $query = "DELETE FROM " . self::$table . " WHERE id = :id";
        self::query($query, ['id' => $id]);
        return self::$statement->rowCount();
    }

    protected static function getValidColumns(): array
    {
        static $columns = [];

        if (!isset($columns[self::$table])) {
            $query = "SHOW COLUMNS FROM " . self::$table;
            self::query($query);
            $columns[self::$table] = self::$statement->fetchAll(PDO::FETCH_COLUMN);
        }

        return $columns[self::$table] ?? [];
    }

    public function find(int $id, string $column = 'id'): ?object
    {
        $query = "SELECT * FROM " . self::$table . " WHERE `$column` = :id";
        self::query($query, ['id' => $id]);
        return self::first();
    }

    public function paginate(int $count = 10): array
    {
        $current_page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($current_page - 1) * $count;

        $query = "SELECT * FROM " . self::$table . " LIMIT $count OFFSET $offset";
        return self::query($query)->all();
    }
}