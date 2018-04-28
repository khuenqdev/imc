<?php

namespace GuiBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\Link;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * Dashboard
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render(
            '@Gui/gui/image_locations.html.twig'
        );
    }

    /**
     * General Statistics
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function statsAction()
    {
        return $this->render(
            '@Gui/gui/statistics.html.twig',
            $this->get('gui.statistics')->getStatistics([
                'general' => 1,
                'geoparsing' => 1,
                'domain' => 1,
                'execution_times' => 1
            ])
        );
    }

    /**
     * Address Statistics
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function addressStatsAction()
    {
        return $this->render(
            '@Gui/gui/statistics_addresses.html.twig',
            $this->get('gui.statistics')->getStatistics(['address' => 1])
        );
    }

    /**
     * Geoparsing Statistics
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function geoparsingStatsAction()
    {
        return $this->render(
            '@Gui/gui/statistics_geoparsing.html.twig',
            $this->get('gui.statistics')->getStatistics(['geoparsing' => 1])
        );
    }

    /**
     * For testing various UI functionality
     */
    public function testAction()
    {
        $image = $this->getManager()->getRepository(Image::class)->find(3);

        return $this->render(
            '@Gui/gui/test.html.twig',
            [
                'image' => $image
            ]
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

        try {
            $options = array('command' => 'cache:clear', "--env" => 'prod', '--no-warmup' => true);
            $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
            $options = array('command' => 'cache:clear', "--env" => 'prod', '--no-warmup' => true);
            $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));

            // Set cache permission
            $this->chmodRecursive($this->container->get('kernel')->getRootDir() . '/cache');
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        return new Response('Cache cleared!');
    }

    /**
     * Recursively set permission
     *
     * @param $dir
     */
    private function chmodRecursive($dir)
    {
        $dp = opendir($dir);

        while ($file = readdir($dp)) {
            if (($file == ".") || ($file == "..")) continue;

            $path = $dir . "/" . $file;
            $isDir = is_dir($path);

            $this->setPerms($path, $isDir);
            if ($isDir) $this->chmodRecursive($path);
        }

        closedir($dp);
    }

    /**
     * Set permissions for directory or file
     *
     * @param $file
     * @param $isDir
     */
    private function setPerms($file, $isDir)
    {
        $perm = substr(sprintf("%o", fileperms($file)), -4);
        $dirPermissions = "0777";
        $filePermissions = "0777";

        if ($isDir && $perm != $dirPermissions) {
            chmod($file, octdec($dirPermissions));
        } else if (!$isDir && $perm != $filePermissions) {
            chmod($file, octdec($filePermissions));
        }

        flush();
    }

    /**
     * @return \Doctrine\ORM\EntityManager|object
     */
    public function getManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }
}
