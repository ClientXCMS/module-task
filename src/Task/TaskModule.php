<?php

namespace App\Task;

use App\Admin\Database\ServerTable;
use App\Task\Actions\CommentAction;
use App\Task\Actions\PingAction;
use App\Task\Actions\TaskAction;
use App\Task\Actions\TaskCrudAction;
use ClientX\Exception\ModuleException;
use ClientX\Module;
use ClientX\Renderer\RendererInterface;
use ClientX\Router;
use ClientX\Theme\ThemeInterface;
use Psr\Container\ContainerInterface;

class TaskModule extends Module
{

    const MIGRATIONS = __DIR__ . "/db/migrations";

    const PORTS = [
        "wisp" => 80,
        "pterodactyl" => 80,
        "cpanel" => 2083,
        "virtualizor" => 4083,
        "virtualizorcloud" => 4085,
        "plesk" => 80,
        "proxmox" => 8006
    ];
    const DEFINITIONS = __DIR__ . '/config.php';

    const TRANSLATIONS = [
        
        "fr_FR" => __DIR__ . "/trans/fr.php",
        "en_GB" => __DIR__ . "/trans/en.php",
        "uk_UA" => __DIR__ . "/trans/ua.php",
        "es_ES" => __DIR__ . "/trans/es.php",
        "de_DE" => __DIR__ . "/trans/de.php"
    ];

    public function __construct(ContainerInterface $container, RendererInterface $renderer, Router $router, ThemeInterface $theme)
    {
        try {
            $this->checkIfCanEnable($container);
        } catch (ModuleException $e) {
            die($e->getMessage());
        }

        $renderer->addPath('task', $theme->getViewsPath() . '/Task');
        $renderer->addPath('task_admin', __DIR__ . '/Views');

        $router->get('/tasks', PingAction::class, 'task');

        $router->get('/tasks/[*:id]', TaskAction::class, 'task.show');
        if ($container->has('admin.prefix')) {
            $prefix = $container->get('admin.prefix');
            $router->crud("$prefix/tasks", TaskCrudAction::class, 'task.admin');
            $router->post("$prefix/comments/[i:id]", CommentAction::class, 'task.admin.comment');
            $router->delete("$prefix/comments/[i:id]", CommentAction::class);
        }
    }

    private function checkIfCanEnable(ContainerInterface $container)
    {
        if ((!$container->has('admin.prefix')) || !class_exists(ServerTable::class)) {
            $string = "The Task module require the Admin module or the class %s not exist";
            throw new ModuleException(sprintf($string, ServerTable::class));
        }
    }
}
