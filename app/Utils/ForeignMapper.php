<?php

namespace App\Utils;

use App\DataBase\DataBase;
use PDOStatement;
use PDO;

class ForeignMapper
{
    protected PDOStatement $createMapStatement;
    protected PDOStatement $idToForeignStatement;
    protected PDOStatement $foreignToIdStatement;
    protected PDOStatement $foreignDeleteStatement;
    protected DataBase $dataBase;
    protected string $table;

    public int $count = 0;

    public function __construct(DataBase $dataBase, string $table,)
    {
        $this->dataBase = $dataBase;
        $this->table = $table;

        $this->createMapStatement = $this->dataBase->handle()->prepare(
            "INSERT INTO $this->table (id, `foreign`) values (:id, :foreign)"
        );

        $this->idToForeignStatement = $this->dataBase->handle()->prepare(
            "SELECT `foreign` from $this->table where id = :id"
        );

        $this->foreignToIdStatement = $this->dataBase->handle()->prepare(
            "SELECT id from $this->table where `foreign` = :foreign"
        );

        $this->foreignDeleteStatement = $this->dataBase->handle()->prepare(
            "DELETE from $this->table where `foreign` = :foreign"
        );
    }

    public function createMap(int $id, string $foreign, bool $replase = false): bool
    {
        if ($this->foreignToId($foreign) !== false) {
            if (!$replase)
                return false;
            $this->foreignDeleteStatement->execute(['foreign' => $foreign]);
        }
        $this->createMapStatement->execute(['id' => $id, 'foreign' => $foreign]);
        $this->count++;
        return true;
    }

    public function idToForeign(int $id): string|bool
    {
        $this->idToForeignStatement->execute(['id' => $id]);
        return $this->idToForeignStatement->fetch(PDO::FETCH_ASSOC)['foreign'] ?? false;
    }

    public function foreignToId(string $foreign): int|bool
    {
        $this->foreignToIdStatement->execute(['foreign' => $foreign]);
        return $this->foreignToIdStatement->fetch(PDO::FETCH_ASSOC)['id'] ?? false;
    }
}