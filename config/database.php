<?php

/**
 * Database Configuration — MySQLi singleton
 * Provides iud() for INSERT/UPDATE/DELETE and search() for SELECT queries.
 */
class Database
{
    public static $connection;

    public static function setUpConnection()
    {
        if (!isset(self::$connection)) {
            self::$connection = mysqli_init();
            self::$connection->ssl_set(null, null, null, null, null);
            self::$connection->real_connect(
                env('DB_HOST', 'localhost'),
                env('DB_USER', 'root'),
                env('DB_PASS', 'Pasidu2003@'),
                env('DB_NAME', 'new_path_2'),
                (int) env('DB_PORT', '3308'),
                null,
                MYSQLI_CLIENT_SSL
            );

            if (self::$connection->connect_error) {
                die("Database connection failed: " . self::$connection->connect_error);
            }

            self::$connection->set_charset('utf8mb4');
        }
    }

    /**
     * Execute INSERT, UPDATE, DELETE queries
     */
    public static function iud($q): bool
    {
        self::setUpConnection();
        self::$connection->query($q);
        return self::$connection->affected_rows >= 0;
    }

    /**
     * Execute SELECT queries — returns mysqli_result
     */
    public static function search($q)
    {
        self::setUpConnection();
        $rs = self::$connection->query($q);
        return $rs;
    }
}
