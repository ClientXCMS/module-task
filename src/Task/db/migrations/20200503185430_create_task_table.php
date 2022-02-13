<?php

use Phinx\Migration\AbstractMigration;

class CreateTaskTable extends AbstractMigration
{
    public function change()
    {
        $task = $this->table('tasks');
        if ($task->exists()){
            $task->addColumn('name', 'string')
            ->addColumn('server_id', 'integer')
            ->addColumn('progress', 'integer', ['default' => 1])
            ->addColumn("category", "string")
            ->addColumn('start_at', 'datetime', ['null' => true])
            ->addColumn('close_at', 'datetime', ['null' => true])
            ->addColumn('state', 'string', ['default' => \App\Task\Entity\Task::OPEN])
            ->addTimestamps()
            ->addForeignKey('server_id', 'servers', ['delete' => 'CASCADE'])
            ->create();
        }
        $comments = $this->table('tasks_comments');

        if ($comments->exists()){
        $comments
            ->addColumn('content', 'string')
            ->addColumn('task_id', 'integer', ['null' => true])
            ->addTimestamps()
            ->create();
        }
    }
}
