<?php

namespace GuiBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\Link;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     Dashboard
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render(
            '@Gui/gui/image_locations.html.twig',
            $this->get('gui.statistics')->getStatistics()
        );
    }

    /**
     * Clear caches
     *
     * @return Response
     */
    public function clearCacheAction()
    {
        $kernel = $this->get('kernel');
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false);
        $options = array('command' => 'cache:clear',"--env" => 'prod', '--no-warmup' => true);

        try {
            $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        return new Response('Cache cleared!');
    }

    /**
     * @return \Doctrine\ORM\EntityManager|object
     */
    public function getManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }
}
