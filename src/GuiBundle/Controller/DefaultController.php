<?php

namespace GuiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GuiBundle:MopsiCrawler:image_locations.html.twig', [
            'title' => 'MopsiCrawler - Image Locations'
        ]);
    }
}
