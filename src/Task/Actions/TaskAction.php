<?php


namespace App\Task\Actions;

use App\Auth\DatabaseUserAuth;
use App\Task\Database\TaskTable;
use ClientX\Actions\Action;
use ClientX\Database\NoRecordException;
use ClientX\Renderer\RendererInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

class TaskAction extends Action
{

    private TaskTable $table;

    public function __construct(RendererInterface $renderer, TaskTable $table, DatabaseUserAuth $auth)
    {
        $this->renderer = $renderer;
        $this->table = $table;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        try {
            $task = $this->table->find($request->getAttribute('id'));
        } catch (NoRecordException $e) {
            return new Response(404);
        }
        return $this->render('@task/show', compact('task'));
    }
}
