<?php


namespace App\Task\Actions;

use App\Task\Database\TaskTable;
use App\Task\Entity\Task;
use App\Task\TaskPing;
use Carbon\Carbon;
use ClientX\Actions\Action;
use ClientX\Renderer\RendererInterface;
use ClientX\Translator\Translater;
use Psr\Http\Message\ServerRequestInterface;

class PingAction extends Action
{

    private TaskTable $table;

    public function __construct(RendererInterface $renderer, TaskTable $table, Translater $translater)
    {
        $this->renderer = $renderer;
        $this->table = $table;
        $this->translater = $translater;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        
        $tasks = $this->table->fetchAll();
        $data = (new TaskPing())->getFromSource();
        $data = collect($data)->sortByDesc(function ($data) {
            return $data['online'] === false;
        })->toArray();

        if (count($data) == collect($data)->filter(function ($row) {
                return $row['online'] == 'yes';
            })->count()) {
            $ok = true;
        } else {
            $ok = false;
        }
        if (array_key_exists('json', $request->getQueryParams())) {
            $tasks = collect($tasks)->map(function (Task $task) {
                return $task->toArray();
            });
            return $this->json(['tasks' => $tasks, 'ping' => $data, 'ok' => $ok]);
        }

        $first = collect($data)->first(null, ["lastping" => time()]);
        Carbon::setLocale(explode('_', $this->translater->getLocale(), 2)[0]);
        $lastUpdated = Carbon::createFromTimestamp($first['lastping'])->diffForHumans();
        return $this->render('@task/index', compact('first', 'lastUpdated', 'ok', 'data', 'tasks'));
    }
}
