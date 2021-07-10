<?php


namespace App\Task;


use App\Admin\Database\ServerTable;
use App\Admin\Entity\Server;
use ClientX\Cron\AbstractCron;

class TaskSchedule extends AbstractCron
{

    protected $name = "server-ping";
    protected $title = "Server ping";
    protected $icon = "fas fa-server";
    public $time = 60;
    private ServerTable $server;

    public function __construct(ServerTable $server)
    {
        $this->server = $server;
    }

    public function run(): array
    {
        $servers = collect($this->server->findAll())->filter(function (Server $server) {
            return $server->isHidden() == false;
        })->toArray();
        $ping = new TaskPing();
        $data = $ping->pingAllServers($servers);
        return $data;
    }


}