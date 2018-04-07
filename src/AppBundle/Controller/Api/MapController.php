<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 3/21/2018
 * Time: 4:29 PM
 */

namespace AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class MapController extends Controller
{
    /**
     * @param Request $request
     * @return array
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Get markers for map display",
     *     output={"collection"=true, "collectionName"="classes", "class"="AppBundle\Entity\Image"},
     *     tags={"stable"},
     *     statusCodes={
     *         200="Returned when successful",
     *         500="Returned when there is a server error"
     *     }
     * )
     *
     * @Rest\QueryParam(name="offset", key="offset", default="0", nullable=true, description="Result offset")
     * @Rest\QueryParam(name="limit", key="limit", default=null, nullable=true, description="Amount of results returned")
     * @Rest\QueryParam(name="sort", key="sort", default=null, nullable=true, description="Name of the field used for sorting")
     * @Rest\QueryParam(name="direction", key="direction", default=null, nullable=true, description="Direction of sorting (asc or desc)")
     * @Rest\QueryParam(name="search", key="search", default=null, nullable=true, description="Keywords for searching the images")
     * @Rest\QueryParam(name="min_lat", key="min_lat", default=null, nullable=true, description="Minimum latitude")
     * @Rest\QueryParam(name="max_lat", key="max_lat", default=null, nullable=true, description="Maximum latitude")
     * @Rest\QueryParam(name="min_lng", key="min_lng", default=null, nullable=true, description="Minimum longitude")
     * @Rest\QueryParam(name="max_lng", key="max_lng", default=null, nullable=true, description="Maximum longitude")
     * @Rest\QueryParam(name="only_location_from_exif", key="only_location_from_exif", default=1, nullable=true, description="Whether the marker should be from images whose location in metadata")
     * @Rest\View(statusCode=200)
     */
    public function getMarkerLocationsAction(Request $request)
    {
        return $this->get('image_manager')->getMarkerLocations($request->query->all());
    }
}