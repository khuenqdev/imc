<?php
/**
 * Created by PhpStorm.
 * User: khue
 * Date: 18.5.2018
 * Time: 14:53
 */

namespace GuiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class StatisticsController extends Controller
{
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
     * Crawling task execution report statistics
     */
    public function reportStatsAction()
    {
        return $this->render(
            '@Gui/gui/reports.html.twig',
            ['reports' => $this->get('gui.statistics')->getReports()]
        );
    }
}
