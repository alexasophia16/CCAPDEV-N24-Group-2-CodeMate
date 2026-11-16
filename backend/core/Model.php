<?php

// Base class of every model (Booking, Tutor, User, ...).
// Models are the only classes that talk to the database. Each one extends this class and
// runs its SQL through the helpers below, which always use prepared statements:
// the SQL has ? placeholders and the values are sent separately, so user input can never
// change the query (this is what protects against SQL injection).
//
//   $this->fetchAll('SELECT * FROM courses WHERE status = ?', ['active']);
class Model
{
    // the shared PDO connection; models can also use it directly for transactions
    // ($this->db->beginTransaction(), commit(), rollBack())
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // Every matching row, as a list of arrays (e.g. $rows[0]['full_name']). Empty list if none.
    protected function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    // The first matching row as an array, or null if there is none.
    // Used for "find one" lookups like a user by email or a booking by id.
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    // The first column of the first row, e.g. the number from SELECT COUNT(*).
    // Returns false if there is no row.
    protected function fetchValue(string $sql, array $params = [])
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchColumn();
    }

    // For INSERT, UPDATE and DELETE. Returns how many rows were changed, which the models
    // use as a check: e.g. 0 rows from "UPDATE ... WHERE status = 'open'" means the slot
    // was already taken.
    protected function execute(string $sql, array $params = []): int
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }

    // The id (AUTO_INCREMENT) of the row that was just inserted.
    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
