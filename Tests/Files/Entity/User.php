<?php

namespace MulerTech\FileManipulation\Tests\Files\Entity;

use MulerTech\FileManipulation\Tests\Files\Mapping\MtEntity;
use MulerTech\FileManipulation\Tests\Files\Mapping\MtColumn;
use MulerTech\FileManipulation\Tests\Files\Mapping\MtFk;
use MulerTech\FileManipulation\Tests\Files\Repository\UserRepository;

#[MtEntity(repository: UserRepository::class, tableName: "users_test", autoIncrement: 100)]
class User extends ParentUser
{
    #[MtColumn(columnType: "int unsigned", isNullable: false, extra: "auto_increment", columnKey: MtColumn::PRIMARY_KEY)]
    private ?int $id = null;

    #[MtColumn(columnType: "varchar(255)", isNullable: false, columnDefault: "John")]
    private ?string $username = null;

    #[MtColumn(columnName: "unit_id", columnType: "int unsigned", isNullable: false, columnKey: MtColumn::MULTIPLE_KEY)]
    #[MtFk(referencedTable: Unit::class, referencedColumn: "id", deleteRule: MtFk::RESTRICT, updateRule: MtFk::CASCADE)]
    private ?int $unit = null;
}