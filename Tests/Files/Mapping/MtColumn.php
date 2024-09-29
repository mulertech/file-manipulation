<?php

namespace MulerTech\FileManipulation\Tests\Files\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MtColumn
{
    public const string PRIMARY_KEY = 'PRI';
    public const string UNIQUE_KEY = 'UNI';
    public const string MULTIPLE_KEY = 'MUL';

    /**
     * MtColumn constructor.
     * @param string|null $columnName
     * @param string|null $columnType
     * @param bool $isNullable
     * @param string|null $extra
     * @param string|null $columnDefault
     * @param string|null $columnKey
     */
    public function __construct(
        public string|null $columnName = null,
        public string|null $columnType = null,
        public bool $isNullable = true,
        public string|null $extra = null,
        public string|null $columnDefault = null,
        public string|null $columnKey = null
    )
    {}
}