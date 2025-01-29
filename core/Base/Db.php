<?php 

namespace Core\Base;

use Exception;
use PDO;
use PDOException;

class Db
{
    public static string $host     = '';
    public static string $username = '';
    public static string $password = '';
    public static string $database = '';
    public static string $charset  = 'utf8mb4';
    public static ?PDO $connection = null;
    public static $statement = null; // Static statement
    public static $table = null;

    // Static method to set configuration values
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

        if (self::$host === '' || self::$username === '' || self::$database === '') {
            throw new Exception("Database configuration is incomplete.", 500);
        }

        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$database . ";charset=" . self::$charset;

        try {
            self::$connection = new PDO($dsn, self::$username, self::$password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE =>  PDO::FETCH_OBJ,
                PDO::ATTR_PERSISTENT         => true, // Optional: Persistent connections
            ]);
        } catch (PDOException $e) {
            // Throw an exception with a custom message
            throw new Exception("Database connection failed: " . $e->getMessage(), 500);
        }

        return self::$connection;
    }


    public static function disconnect(): void
    {
        self::$connection = null;
    }
    public static function table(string $table): Db
    {
        self::$table = $table;
        return new self();
    }
  
    public static function query(string $query, array $params = []): Db
    {
        if (self::$connection === null) {
            throw new Exception("Database connection failed", 500);
        }

        // Prepare and execute the query with parameters
        self::$statement = self::$connection->prepare($query);
        self::$statement->execute($params);

        return new self();
    }


    public static function all(): array
    {
        if (self::$statement === null) {
            return [];
        }

        return self::$statement->fetchAll(PDO::FETCH_OBJ);
    }

    public static function first(): array|null
    {
        if (self::$statement === null) {
            return null;
        }
        $result = self::$statement->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return $result;
        }

        return null;
    }

     public static function insert(array $data): int
    {
        try {
            if (empty($data)) {
                throw new Exception("No data provided for insert", 400);
            }

            // Validate column names
            $validColumns = self::getValidColumns();
            $filteredData = [];

            foreach ($data as $key => $value) {
                if (!in_array($key, $validColumns, true)) {
                    throw new Exception("Invalid column name: $key", 400);
                }
                $filteredData[$key] = $value;
            }

            $fields = implode('`, `', array_keys($filteredData));
            $placeholders = ':' . implode(', :', array_keys($filteredData));

            $query = "INSERT INTO " . self::$table . " (`$fields`) VALUES ($placeholders)";
            self::query($query, $filteredData);

            return (int) self::$connection->lastInsertId();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    public static function update(array $data, int $id): int
    {
        try {
            if (empty($data)) {
                throw new Exception("No data provided for update", 400);
            }

            $validColumns = self::getValidColumns();
            $fields = [];
            $params = [];

            foreach ($data as $key => $value) {
                if (!in_array($key, $validColumns, true)) {
                    throw new Exception("Invalid column name: $key", 400);
                }
                $fields[] = "`$key` = :$key";
                $params[$key] = $value;
            }

            $params['id'] = $id;
            $query = "UPDATE " . self::$table . " SET " . implode(', ', $fields) . " WHERE id = :id";

            self::query($query, $params);
            return self::$statement->rowCount();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

   public static function delete(int $id): int
    {
        $query = "DELETE FROM " . self::$table . " WHERE id = :id";
        self::query($query, ['id' => $id]);
        return self::$statement->rowCount();
    }
    private static function getValidColumns(): array
    {
        static $columns = null;

        if ($columns === null) {
            $query = "SHOW COLUMNS FROM " . self::$table;
            self::query($query);
            $columns = self::$statement->fetchAll(PDO::FETCH_COLUMN);
        }

        return $columns;
    }

}