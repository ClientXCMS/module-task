<?php


namespace App\Task\Database;

class CommentTable extends \ClientX\Database\Table
{
    protected $entity = \stdClass::class;

    protected $table = "tasks_comments";
}
