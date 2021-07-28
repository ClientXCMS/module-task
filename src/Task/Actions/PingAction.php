<?php


namespace App\Task\Actions;

use App\Task\Database\TaskTable;
use App\Task\TaskPing;
use Carbon\Carbon;
use ClientX\Actions\Action;
use ClientX\Renderer\RendererInterface;
use ClientX\Translator\Translater;

class PingAction extends Action
{

    private TaskTable $table;

    public function __construct(RendererInterface $renderer, TaskTable $table, Translater $translater)
    {
        $this->renderer = $renderer;
        $this->table = $table;
        $this->translater = $translater;
    }

    public function __invoke(): string
    {
        $tasks = $this->table->fetchAll();
        $data = (new TaskPing())->getFromSource();
        $data = collect($data)->sortByDesc(function ($data) {
            return $data['online'] === false;
        })->toArray();
        $ok = collect($data)->filter(function ($line) {
                return $line['online'];
        })->count() === count($data);
        if (count($data) === 0) {
            $ok = false;
        }
        $first = collect($data)->first(null, ["lastping" => time()]);
        Carbon::setLocale(explode('_', $this->translater->getLocale(), 2)[0]);
        $lastUpdated = Carbon::createFromTimestamp($first['lastping'])->diffForHumans();
        return $this->render('@task/index', compact('first', 'lastUpdated', 'ok', 'data', 'tasks'));
    }
}
