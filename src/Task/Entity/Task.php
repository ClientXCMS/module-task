<?php

namespace App\Task\Entity;

use ClientX\Support\Arrayable;
use function ClientX\d;

class Task implements Arrayable
{
    private ?int $id = null;
    private ?int $serverId = null;
    private ?\DateTime $startAt = null;
    private ?\DateTime $closeAt = null;
    private string $category = self::CATEGORIES[0];
    private int $progress = 10;
    private string $state = self::OPEN;
    private array $comments = [];
    private ?string $name = null;
    use \ClientX\Entity\Timestamp;

    const PLANNED = "Planned";
    const IN_PROGRESS = "In progress";
    const CLOSED = "Closed";
    const OPEN = "Open";

    const CATEGORIES = [
        "Information", "Upgrade", "Maintenance", "Incident"
    ];

    const ALL = [
        self::PLANNED => self::PLANNED,
        self::IN_PROGRESS => self::IN_PROGRESS,
        self::CLOSED => self::CLOSED,
        self::OPEN => self::OPEN
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Task
    {
        $this->id = $id;

        return $this;
    }

    public function getStartAt(): ?\DateTime
    {
        if ($this->startAt == null) {
            return new \DateTime();
        }
        return $this->startAt;
    }

    /**
     * @throws \Exception
     */
    public function setStartAt($startAt)
    {
        if (is_string($startAt)) {
            $this->startAt = new \DateTime($startAt);
        } else {
            $this->startAt = $startAt;
        }
    }

    public function getCloseAt(): ?\DateTime
    {
        if ($this->closeAt === null) {
            return (new \DateTime())->add(\DateInterval::createFromDateString('+ 2 hours'));
        }
        return $this->closeAt;
    }

    /**
     * @throws \Exception
     */
    public function setCloseAt($closeAt)
    {
        if (is_string($closeAt)) {
            $this->closeAt = new \DateTime($closeAt);
        } else {
            $this->closeAt = $closeAt;
        }
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function addComment(string $date, string $comment): Task
    {
        $this->comments[$date] = $comment;
        return $this;
    }

    public function setState(string $state)
    {
        $this->state = $state;
    }

    public function getBullet(): string
    {
        if ($this->getState() === self::CLOSED || (new \DateTime())->format('U') < $this->getStartAt()->format('U')) {
            return 'no';
        }
        return $this->getStartAt()->format('U') < $this->getCloseAt()->format('U') ? 'yes' : 'no';
    }

    public function getServerId(): ?int
    {
        return $this->serverId;
    }

    public function setServerId(?int $serverId): void
    {
        $this->serverId = $serverId;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): void
    {
        if ($progress > 100) {
            $progress = 100;
        }
        $this->progress = $progress;
    }

    public function getName(): ?string
    {
        return d($this->name);
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    public function toArray():array
    {
        return [
            'id' => $this->getId(),
            'comments' => collect($this->comments)->map(function ($comment) {
                return [
                    'content' => $comment['content'],
                    'created_at' => (new \DateTime($comment['created_at']))->format('U')
                ];
            }),
            'name' => $this->getName(),
            'progress' => $this->getProgress(),
            'category' => $this->getCategory(),
            'server_id' => $this->getServerId(),
            'state' => $this->getState(),
            'start_at' => $this->getStartAt() ? $this->getStartAt()->format('U') : null,
            'close_at' => $this->getCloseAt() ? $this->getCloseAt()->format('U') : null,
            'updated_at' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('U') : null,
        ];
    }
}
