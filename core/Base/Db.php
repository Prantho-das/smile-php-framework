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

    // Static method to set configuration values
    public static function configure(): void
    {
        // Assuming `config()` function is available to get settings
        self::$host     = config('database', 'host') ?? '';
        self::$username = config('database', 'username') ?? '';
        self::$password = config('database', 'password') ?? '';
        self::$database = config('database', 'database') ?? '';
        self::$charset  = config('database', 'charset') ?? 'utf8mb4';
    }

    /**
     * Establish a PDO connection to the database
     * @return PDO|null
     * @throws Exception
     */
    public static function connect(): ?PDO
    {
        // Check if a connection already exists
        if (self::$connection !== null) {
            return self::$connection;
        }

        self::configure(); // Configure the connection

        // Validate required parameters
        if (self::$host === '' || self::$username === '' || self::$database === '') {
            throw new Exception("Database configuration is incomplete.", 500);
        }

        // Set DSN (Data Source Name)
        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$database . ";charset=" . self::$charset;

        try {
            // Create a new PDO instance
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

    /**
     * Disconnect the PDO connection
     */
    public static function disconnect(): void
    {
        self::$connection = null;
    }

    /**
     * Perform a query with parameters
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return Db Returns the current Db instance for method chaining
     * @throws Exception
     */
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

    /**
     * Fetch all rows from the query
     * @return array
     */
    public static function all(): array
    {
        if (self::$statement === null) {
            return [];
        }

        return self::$statement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch a single row from the query
     * @return array|null
     */

    /**
     * Fetch the first result of the query
     * @return array|null
     */
    public static function first(): array|null
    {
        if (self::$statement === null) {
            return null;
        }

        $result = self::$statement->fetch(PDO::FETCH_OBJ);

        // If there's at least one row, return it
        if ($result) {
            return $result;
        }

        return null;
    }
}