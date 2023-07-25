<?php

namespace App\Utils;

use App\DataBase\DataBase;
use PDOStatement;
use PDO;

class ForeignMapper
{
    private PDOStatement $createMapStatement;
    private PDOStatement $idToForeignStatement;
    private PDOStatement $foreignToIdStatement;

    public int $count = 0;

    public function __construct(
        private DataBase $dataBase,
        private string   $table,
    )
    {
        $this->createMapStatement = $this->dataBase->handle()->prepare(
            "INSERT INTO $this->table (id, `foreign`) values (:id, :foreign)"
        );

        $this->idToForeignStatement = $this->dataBase->handle()->prepare(
            "SELECT foreign from $this->table where id = :id"
        );

        $this->foreignToIdStatement = $this->dataBase->handle()->prepare(
            "SELECT id from $this->table where `foreign` = :foreign"
        );
    }

    public function createMap(int $id, string $foreign): void
    {
        if ($this->foreignToId($foreign) === false) {
            $this->createMapStatement->execute(['id' => $id, 'foreign' => $foreign]);
            $this->count++;
        }
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