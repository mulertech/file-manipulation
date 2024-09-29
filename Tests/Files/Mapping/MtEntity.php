<?php

namespace MulerTech\FileManipulation\Tests\Files\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class MtEntity
{
    public function __construct(
        public string|null $repository = null,
        public string|null $tableName = null,
        public int|null $autoIncrement = null
    )
    {}
}