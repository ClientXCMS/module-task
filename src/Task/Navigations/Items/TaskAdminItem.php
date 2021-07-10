<?php

namespace App\Task\Navigations\Items;

use ClientX\Navigation\NavigationItemInterface;
use ClientX\Renderer\RendererInterface;

class TaskAdminItem implements NavigationItemInterface
{

    public function render(RendererInterface $renderer): string
    {
        return $renderer->render("@task_admin/menu");
    }

    public function getPosition(): int
    {
        return 80;
    }
}
