<?php
namespace GuiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FlickrController extends Controller
{
    /**
     * Get most recent photos uploaded to Flickr
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getRecentAction(Request $request)
    {
        $service = $this->get('flickr');
        $extras = $request->get('extras', null);
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        return $this->render('@Gui/flickr/get_recent.html.twig', [
            'recents' => $service->apiGetRecent($extras, $perPage, $page)
        ]);
    }
}