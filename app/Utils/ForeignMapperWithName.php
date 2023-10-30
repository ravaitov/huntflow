<?php

namespace App\Utils;

use App\DataBase\DataBase;
use PDO;

class ForeignMapperWithName extends ForeignMapper
{
    public function __construct(DataBase $dataBase, string $table)
    {
        parent::__construct($dataBase, $table);

        $this->createMapStatement = $this->dataBase->handle()->prepare(
            "INSERT INTO $this->table (id, `foreign`, name) values (:id, :foreign, :name)"
        );

        $this->foreignToIdStatement = $this->dataBase->handle()->prepare(
            "SELECT id, name from $this->table where `foreign` = :foreign"
        );
    }

    public function createMapWithName(int $id, string $foreign, string $name): bool
    {
        if ($this->foreignToId($foreign) !== false) {
            if ($this->foreignToName($foreign) === $name)
                return false;
            $this->foreignDeleteStatement->execute(['foreign' => $foreign]);
        }
        $this->createMapStatement->execute(['id' => $id, 'foreign' => $foreign, 'name' => $name]);
        $this->count++;
        return true;
    }

    public function foreignToName(string $foreign): string
    {
        $this->foreignToIdStatement->execute(['foreign' => $foreign]);
        return $this->foreignToIdStatement->fetch(PDO::FETCH_ASSOC)['name'] ?? '';
    }

}