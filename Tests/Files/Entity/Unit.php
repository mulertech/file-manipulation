<?php

namespace MulerTech\FileManipulation\Tests\Files\Entity;

use MulerTech\FileManipulation\Tests\Files\Mapping\MtEntity;
use MulerTech\FileManipulation\Tests\Files\Mapping\MtColumn;
use MulerTech\FileManipulation\Tests\Files\Repository\UserRepository;

#[MtEntity(repository: UserRepository::class, tableName: "units_test", autoIncrement: 100)]
class Unit
{
    #[MtColumn(columnType: "int unsigned", isNullable: false, extra: "auto_increment", columnKey: MtColumn::PRIMARY_KEY)]
    private ?int $id = null;

    #[MtColumn(columnType: "varchar(255)", isNullable: false)]
    private ?string $name = null;
}