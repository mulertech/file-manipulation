<?php

namespace MulerTech\FileManipulation\Tests\Files;

use MulerTech\FileManipulation\Tests\Files\Mapping\MtRoute;

class ContainMtRoutes
{
    #[MtRoute(path: "", name: "home.page")]
    public function home() {
        echo "home";
    }

    #[MtRoute(path: "index_news_{id}", name: "index.news", access: "1")]
    public function news(int $id): void
    {
        //logout for tests
    }

    #[MtRoute(path: "index_logout", name: "index.logout")]
    public function logout(): void
    {
        //logout for tests
    }

    public function noroute(): void
    {

    }
}