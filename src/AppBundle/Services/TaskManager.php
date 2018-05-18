<?php
/**
 * Created by PhpStorm.
 * User: khue
 * Date: 18.5.2018
 * Time: 14:45
 */

namespace AppBundle\Services;


use AppBundle\Entity\Task;
use Doctrine\ORM\EntityManager;

class TaskManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get executed crawling tasks
     *
     * @param int $limit
     * @param int $offset
     * @return Task[]|array
     */
    public function getTasks($limit = 100, $offset = 0)
    {
        $repo = $this->em->getRepository(Task::class);
        return $repo->findBy([], ['id' => 'desc'], $limit, $offset);
    }

    /**
     * Mark a task as finish
     *
     * @param Task $task
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAsFinished(Task $task)
    {
        $task->finished = true;
        $task->endAt = new \DateTime('now');
        $this->em->persist($task);
        $this->em->flush($task);
    }
}
