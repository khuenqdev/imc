<?php
/**
 * Created by PhpStorm.
 * User: khue
 * Date: 18.5.2018
 * Time: 14:58
 */

namespace GuiBundle\Controller;

use AppBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    /**
     * Get a list of tasks
     *
     * @param Request $request
     * @return Response
     */
    public function getTasksAction(Request $request)
    {
        return $this->render(
            '@Gui/gui/tasks.html.twig',
            ['tasks' => $this->get('task_manager')->getTasks(
                $request->get('limit', 100),
                $request->get('offset', 0)
            )]
        );
    }

    /**
     * Mark individual crawling task as finished
     *
     * @param Task $task
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markTaskFinishedAction(Task $task)
    {

        $this->get('task_manager')->markAsFinished($task);

        return $this->redirectToRoute('gui_tasks');
    }
}
