<?php

namespace App\Task\Actions;

use App\Admin\Database\NodeTable;
use App\Admin\Database\ServerTable;
use App\Shop\Database\ServiceTable;
use App\Task\Database\CommentTable;
use App\Task\Database\TaskTable;
use App\Task\Entity\Task;
use App\Task\TaskPing;
use ClientX\Actions\CrudAction;
use ClientX\Renderer\RendererInterface;
use ClientX\Router;
use ClientX\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class TaskCrudAction extends CrudAction
{

    protected $viewPath = "@task_admin/crud";
    protected $routePrefix = "task.admin";
    protected $moduleName = "Tasks";
    protected $fillable = ["server_id", "start_at", "close_at", "state", "progress", "category", "name"];
    private ServerTable $serverTable;
    private CommentTable $commentTable;
    private ServiceTable $service;

    public function __construct(
        RendererInterface $renderer,
        TaskTable         $table,
        Router            $router,
        ServiceTable      $service,
        FlashService      $flash,
        ServerTable       $serverTable,
        CommentTable      $commentTable
    ) {
        parent::__construct($renderer, $table, $router, $flash);
        $this->serverTable = $serverTable;
        $this->commentTable = $commentTable;
        $this->service = $service;
        $this->renderer = $renderer;
        $this->table = $table;
        $this->router = $router;
        $this->flash = $flash;
    }

    protected function formParams(array $params): array
    {
        $params['servers'] = $this->serverTable->findList();
        /** @var Task */
        $item = $params['item'];
        $count = $this->service->makeQuery()->where("server_id = :id")->params(['id' => $item->getServerId()])->count();
        $params['states'] = Task::ALL;
        $params['categories'] = collect(Task::CATEGORIES)->mapWithKeys(function ($value) {
            return [$value => $value];
        })->toArray();
        $params['count'] = $count;
        if ($this->mode == 'edit') {
            $params['ping'] = (new TaskPing())->pingServer($this->serverTable->find($item->getServerId()));
        }
        return $params;
    }

    protected function getValidator(\Psr\Http\Message\ServerRequestInterface $request): \ClientX\Validator
    {
        $id = $request->getAttribute('id');
        $validator = (parent::getValidator($request))
            ->inArray('category', array_values(Task::CATEGORIES))
            ->between('progress', 0, 100)
            ->notEmpty('name', 'server_id');
        if ($this->mode === 'edit') {
            $validator->inArray('state', array_keys(Task::ALL));
        } else {
            $validator->notEmpty('content');
        }
        return $validator;
    }

    /**
     * @param Request $request
     * @param $item
     * @param int|null $id
     */
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

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function delete(Request $request): ResponseInterface
    {
        $response = parent::delete($request);
        $this->commentTable->deleteBy("task_id", $request->getAttribute('id'));
        return $response;
    }
}
