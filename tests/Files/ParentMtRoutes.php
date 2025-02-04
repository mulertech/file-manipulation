<?php

namespace MulerTech\FileManipulation\Tests\Files;

use MulerTech\FileManipulation\Tests\Files\Mapping\MtRoute;

class ParentMtRoutes
{
    #[MtRoute(path: "", name: "parent.page")]
    public function parent() {
        echo "parent";
    }
}