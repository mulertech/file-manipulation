<?php

namespace MulerTech\FileManipulation\Tests\Files\Entity;

use MulerTech\FileManipulation\Tests\Files\Mapping\MtEntity;
use MulerTech\FileManipulation\Tests\Files\Repository\GroupRepository;

#[MtEntity(repository: GroupRepository::class)]
class Group
{

}