<?php

namespace MulerTech\FileManipulation\Tests\Files\Entity;

use MulerTech\FileManipulation\Tests\Files\Mapping\MtEntity;
use MulerTech\FileManipulation\Tests\Files\Mapping\MtColumn;
use MulerTech\FileManipulation\Tests\Files\Repository\UserRepository;

/**
 * Class User
 * @package MulerTech\FileManipulation\Tests\Files
 * @author Sébastien Muler
 */
#[MtEntity(repository: UserRepository::class, tableName: "parent_users_test", autoIncrement: 1)]
class ParentUser
{
    #[MtColumn(columnType: "int unsigned", isNullable: false, extra: "auto_increment", columnKey: MtColumn::PRIMARY_KEY)]
    private ?int $id = null;

    #[MtColumn(columnType: "varchar(255)", isNullable: false, columnDefault: "Jack")]
    private ?string $username = null;
}