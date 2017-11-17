<?php

namespace QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('QueueBundle:Default:index.html.twig');
    }
}
