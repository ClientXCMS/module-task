<?php

use App\Task\Navigations\Items\TaskAdminItem;

use App\Task\TaskSchedule;
use ClientX\Navigation\DefaultMainItem;
use function DI\add;
use function DI\get;

return [
    'admin.menu.items' => add([get(TaskAdminItem::class)]),
    "cron.schedules" => add(TaskSchedule::class),
    'admin.dashboard.items' => add(get(\App\Task\Navigations\Items\TaskDashboardItem::class)),
    'navigation.main.items' => add(new DefaultMainItem([DefaultMainItem::makeItem("task.title", "task", "fa fa-server", true, true)], 40)),
];
