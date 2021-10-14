<?php

namespace App\SQLite;

use PDO;
use PDOException;
use function env;


class SQLiteConnection
{

    private $pdo;

    /**
     * return in instance of the PDO object that connects to the SQLite database
     * @return PDO
     */
    public function connect(): PDO {
        if ($this->pdo == null) {
            try {
            $this->pdo = new PDO("sqlite:" . __DIR__ . '/../../' . env('PATH_TO_SQLITE_DB'));
            } catch (PDOException $e) {
                print_r("Error: " . $e);
                // handle the exception here
            }
        }
        return $this->pdo;
    }
}