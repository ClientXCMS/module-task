<?php


namespace App\Task\Actions;

use App\Auth\DatabaseUserAuth;
use App\Shop\Database\ServiceTable;
use App\Task\Database\TaskTable;
use ClientX\Actions\Action;
use ClientX\Database\NoRecordException;
use ClientX\Renderer\RendererInterface;
use http\Env\Response;
use Psr\Http\Message\ServerRequestInterface;

class TaskAction extends Action
{


    private TaskTable $table;
    private ServiceTable $serviceTable;

    public function __construct(RendererInterface $renderer, TaskTable $table, DatabaseUserAuth $auth, ServiceTable $serviceTable)
    {
        $this->renderer = $renderer;
        $this->table = $table;
        $this->auth = $auth;
        $this->serviceTable = $serviceTable;
    }

    public function __invoke(ServerRequestInterface $request): string
    {
        try {
            $task = $this->table->find($request->getAttribute('id'));
            if ($this->isLogged()) {
                $services = $this->serviceTable->findForUser($this->getUserId());
            } else {
                $services = [];
            }
        } catch (NoRecordException $e) {
            return new Response(404);
        }
        return $this->render('@task/show', compact('task', 'services'));
    }
}
