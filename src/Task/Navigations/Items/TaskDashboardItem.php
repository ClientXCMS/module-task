<?php


namespace App\Task\Navigations\Items;

use App\Task\TaskPing;
use Carbon\Carbon;
use ClientX\Navigation\NavigationItemInterface;
use ClientX\Renderer\RendererInterface;
use ClientX\Translator\Translater;

class TaskDashboardItem implements NavigationItemInterface
{
    private Translater $translater;
    public function __construct(Translater $translater)
    {
        $this->translater = $translater;
    }


    /**
     * @inheritDoc
     */
    public function getPosition(): int
    {
        return 10;
    }

    /**
     * @inheritDoc
     */
    public function render(RendererInterface $renderer): string
    {

        $data = (new TaskPing())->getFromSource();
        $data = collect($data)->sortByDesc(function ($data) {
            return $data['online'] === false;
        })->toArray();
        $ok = collect($data)->filter(function ($line) {
                return $line['online'];
        })->count() === count($data);
        $first = collect($data)->first(null, ["lastping" => time()]);

        Carbon::setLocale(explode('_', $this->translater->getLocale(), 2)[0]);
        $lastUpdated = Carbon::createFromTimestamp($first['lastping'])->diffForHumans();
        return $renderer->render("@task_admin/dashboard", compact('data', 'ok', 'lastUpdated'));
    }
}
