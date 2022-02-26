        $first = collect($data)->first(null, ["lastping" => time()]);
<?php


namespace App\Task\Navigations\Items;

use App\Task\Database\TaskTable;
use App\Task\Entity\Task;
use App\Task\TaskPing;
use Carbon\Carbon;
use ClientX\Navigation\NavigationItemInterface;
use ClientX\Renderer\RendererInterface;
use ClientX\Translator\Translater;

class TaskDashboardItem implements NavigationItemInterface
{
    private Translater $translater;
    /**
     * @var \App\Task\Database\TaskTable
     */
    private TaskTable $table;


    public function __construct(Translater $translater, TaskTable $table)
    {
        $this->translater = $translater;
        $this->table = $table;
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

        $tasks = $this->table->fetchAll();
        $data = (new TaskPing())->getFromSource();
        $data = collect($data)->sortByDesc(function ($data) {
            return $data['online'] === false;
        })->toArray();
        $ok = collect($data)->filter(function ($line) {
                return $line['online'];
        })->count() === count($data);
        if (count($tasks) == collect($tasks)->filter(function (Task $task) {
                return $task->getBullet() == 'yes';
        })->count()) {
            $ok = true;
        } else {
            $ok = false;
        }
        $first = collect($data)->first(null, ["lastping" => time()]);

        Carbon::setLocale(explode('_', $this->translater->getLocale(), 2)[0]);
        $lastUpdated = Carbon::createFromTimestamp($first['lastping'])->diffForHumans();
        return $renderer->render("@task_admin/dashboard", compact('data', 'ok', 'lastUpdated'));
    }
}
