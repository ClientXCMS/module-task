<?php

namespace App\Task\Database;

use App\Task\Entity\Task;
use ClientX\Database\Query;
use ClientX\Database\Table;

class TaskTable extends Table
{

    protected $entity = Task::class;

    protected $table = "tasks";

    protected $element = "name";

    public function countForWidget(): int
    {
        return
            $this
                ->makeQuery()
                ->where('status = :status')
                ->params(['status' => 'open'])
                ->count();
    }

    public function fetchAll(): array
    {
        $tmp = [];
        $tasks = $this->findAll();
        foreach ($tasks as $task) {
            $tmp[] = $this->find($task->getId());
        }
        return $tmp;
    }

    public function find(int $id)
    {
        $task = $this->makeQuery()
            ->where("t.id = $id")
            ->select('t.*', 's.name as serverName')
            ->join('servers as s', 's.id = t.server_id')
            ->fetchOrFail();

        $comments = $this->makeQuery()
            ->select('c.content', 'c.created_at', 'c.id',)
            ->where('t.id = :id')
            ->join('tasks_comments as c', 'c.task_id = t.id')
            ->into(\stdClass::class)
            ->params(compact('id'))
            ->fetchAll();
        $comments = collect($comments)->mapWithKeys(function ($comment) {
            return [$comment->id => ['created_at' => $comment->createdAt, 'content' => $comment->content]];
        })->toArray();
        $task->setComments($comments);
        return $task;
    }

    public function makeQueryForAdmin(?array $search = null, $order = "desc"): Query
    {
        $query = parent::makeQueryForAdmin($search, $order);
        $query
            ->select('t.*', 's.name as serverName')
            ->join('servers as s', 's.id = t.server_id');
        return $query;

    }
}
