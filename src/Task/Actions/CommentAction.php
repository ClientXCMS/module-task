<?php


namespace App\Task\Actions;

use App\Task\Database\CommentTable;
use App\Task\Database\TaskTable;
use ClientX\Actions\Action;
use ClientX\Database\NoRecordException;
use ClientX\Renderer\RendererInterface;
use ClientX\Router;
use ClientX\Session\FlashService;
use Psr\Http\Message\ServerRequestInterface as Request;

class CommentAction extends Action
{
    private CommentTable $table;
    private TaskTable $taskTable;

    public function __construct(
        RendererInterface $renderer,
        TaskTable $taskTable,
        CommentTable $table,
        Router $router,
        FlashService $flash
    )
    {
        $this->renderer = $renderer;
        $this->router = $router;
        $this->flash = $flash;
        $this->table = $table;
        $this->taskTable = $taskTable;
    }

    public function __invoke(Request $request): \ClientX\Response\RedirectBackResponse
    {
        $id = $request->getAttribute('id');
        if ($request->getMethod() === 'DELETE') {
            $this->table->delete($id);
            return $this->back($request);
        }
        try {
            $task = $this->taskTable->find($id);

            $content = $request->getParsedBody()['content'];
            if (!empty($content)) {
                $this->table->insert([
                    'content' => $content,
                    'task_id' => $id
                ]);
                $this->success("Done!");
            }
        } catch (NoRecordException $e) {
        }
        return $this->back($request);
    }
}
