<?php

namespace App\Task\Actions;

use App\Admin\Database\NodeTable;
use App\Admin\Database\ServerTable;
use App\Task\Database\CommentTable;
use App\Task\Database\TaskTable;
use App\Task\Entity\Task;
use ClientX\Actions\CrudAction;
use ClientX\Renderer\RendererInterface;
use ClientX\Router;
use ClientX\Session\FlashService;
use Psr\Http\Message\ServerRequestInterface as Request;

class TaskCrudAction extends CrudAction
{

    protected $viewPath = "@task_admin/crud";
    protected $routePrefix = "task.admin";
    protected $moduleName = "Tasks";
    protected $fillable = ["server_id", "start_at", "close_at", "state", "progress", "category", "name"];
    private ServerTable $serverTable;
    private CommentTable $commentTable;

    public function __construct(RendererInterface $renderer, TaskTable $table, Router $router, FlashService $flash, ServerTable $serverTable, CommentTable $commentTable)
    {
        parent::__construct($renderer, $table, $router, $flash);
        $this->serverTable = $serverTable;
        $this->commentTable = $commentTable;
    }

    protected function formParams(array $params): array
    {
        $params['servers'] = $this->serverTable->findList();
        /** @var Task */
        $item = $params['item'];

        $params['states'] = Task::ALL;
        $params['categories'] = collect(Task::CATEGORIES)->mapWithKeys(function ($value) {
            return [$value => $value];
        })->toArray();
        return $params;
    }

    protected function getValidator(\Psr\Http\Message\ServerRequestInterface $request): \ClientX\Validator
    {
        $id = $request->getAttribute('id');
        $validator = (parent::getValidator($request))
            ->inArray('category', array_keys(Task::CATEGORIES))
            ->between('progress', 0, 100)
            ->notEmpty('name')
            ->exists('server_id', $this->serverTable, null, 'id', $id, 'id');
        if ($this->mode === 'edit') {
            $validator->inArray('state', array_keys(Task::ALL));
        }
        return $validator;
    }

    protected function afterUpdate(Request $request, $item, ?int $id = null)
    {
        $params = $request->getParsedBody();
        if (!empty($params['content'])) {
            $this->commentTable->insert([
                'content' => $params['content'],
                'task_id' => $id
            ]);
        }
    }
}
