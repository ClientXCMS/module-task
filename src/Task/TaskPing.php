<?php

namespace App\Task;

use App\Admin\Entity\Server;
use ClientX\App;
use JsonException;
use Symfony\Component\Filesystem\Filesystem;
use function ClientX\ping;

class TaskPing
{

    private Filesystem $file;

    public function __construct()
    {
        $this->file = new Filesystem();
    }

    public function pingServer(Server $server)
    {
        $ping = ping($server->getIpaddress(), TaskModule::PORTS[$server->getType()], 10);
        return [
            'server' => $server->getIpaddress(),
            'online' => $ping !== 'down',
            'ping' => $ping,
            'lastping' => date('U'),
            'name' => $server->name,
            'location' => $server->noc,
            'id' => $server->getId(),
        ];
    }

    public function pingAllServers(iterable $servers)
    {
        $data = collect($servers)->map([$this, 'pingServer'])->toArray();
        try {
            file_put_contents(self::getPath(), json_encode($data, JSON_PRETTY_PRINT));
        } catch (JsonException $e) {
            return [];
        }
        return $data;
    }

    public function getFromSource(): array
    {
        if (file_exists(self::getPath()) === false) {
            return [];
        }
        return json_decode(file_get_contents(self::getPath()), true);
    }

    public static function getPath(): string
    {
        return App::getTmpDir() . '/__ping.json';
    }

}
