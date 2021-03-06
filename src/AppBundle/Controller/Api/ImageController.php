<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 12/16/2017
 * Time: 11:44 AM
 */

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Image;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class ImageController
 *
 * @package AppBundle\Controller\Api
 */
class ImageController extends Controller
{

    /**
     * List all images
     *
     * @ApiDoc(
     *     resource=true,
     *     description="List all images",
     *     output={"collection"=true, "collectionName"="classes", "class"="AppBundle\Entity\Image"},
     *     tags={"stable"},
     *     statusCodes={
     *         200="Returned when successful",
     *         500="Returned when there is a server error"
     *     }
     * )
     *
     * @Rest\QueryParam(name="offset", key="offset", default=0, nullable=true, description="Result offset")
     * @Rest\QueryParam(name="limit", key="limit", default=100, nullable=true, description="Amount of results returned")
     * @Rest\QueryParam(name="sort", key="sort", default=null, nullable=true, description="Name of the field used for sorting")
     * @Rest\QueryParam(name="direction", key="direction", default=null, nullable=true, description="Direction of sorting (asc or desc)")
     * @Rest\QueryParam(name="search", key="search", default=null, nullable=true, description="Keywords for searching the images")
     * @Rest\QueryParam(name="min_lat", key="min_lat", default=null, nullable=true, description="Minimum latitude")
     * @Rest\QueryParam(name="max_lat", key="max_lat", default=null, nullable=true, description="Maximum latitude")
     * @Rest\QueryParam(name="min_lng", key="min_lng", default=null, nullable=true, description="Minimum longitude")
     * @Rest\QueryParam(name="max_lng", key="max_lng", default=null, nullable=true, description="Maximum longitude")
     * @Rest\QueryParam(name="only_with_location", key="only_with_location", default=1, nullable=true, description="Whether there should only be images with locations, either set to 1 or unset")
     * @Rest\QueryParam(name="only_location_from_exif", key="only_location_from_exif", default=1, nullable=true, description="Whether there should only be images with location info from metadata")
     * @Rest\View(statusCode=200)
     * @param Request $request
     * @return array
     */
    public function listAction(Request $request)
    {
        $images = $this->getManager()->listImages($request->query->all());

        return $images;
    }

    /**
     * Read an image
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Get individual image",
     *     output="AppBundle\Entity\Image",
     *     tags={"stable"},
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the image is not found",
     *         500="Returned when there is a server error"
     *     },
     *     parameters={
     *         {"name"="id", "dataType"="integer", "required"=true, "description"="ID of individual image"}
     *     }
     * )
     *
     * @Rest\View(statusCode=200)
     * @param Image $image
     * @return Image
     */
    public function readAction(Image $image)
    {
        return $image;
    }

    /**
     * Update an image's data
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Update information of individual image",
     *     tags={"stable"},
     *     statusCodes={
     *         204="Returned when successful",
     *         404="Returned when the image is not found",
     *         500="Returned when there is a server error"
     *     },
     *     parameters={
     *         {"name"="id", "dataType"="integer", "required"=true, "description"="ID of individual image"},
     *         {"name"="description", "dataType"="integer", "required"=false, "description"="ID of individual image"},
     *         {"name"="latitude", "dataType"="decimal", "required"=false, "description"="Latitude of the image's location"},
     *         {"name"="longitude", "dataType"="decimal", "required"=false, "description"="Longitude of the image's location"},
     *         {"name"="address", "dataType"="string", "required"=false, "description"="Address text of the image's location"}
     *     }
     * )
     *
     * @Rest\RequestParam(name="description", key="description", nullable=true, description="Image description")
     * @Rest\RequestParam(name="latitude", key="latitude", nullable=true, description="Latitude of the image's location")
     * @Rest\RequestParam(name="longitude", key="longitude", nullable=true, description="Longitude of the image's location")
     * @Rest\RequestParam(name="address", key="address", nullable=true, description="Address text of the image's location")
     *
     * @Rest\View(statusCode=204)
     * @param Request $request
     * @param Image $image
     * @throws \Exception
     */
    public function updateAction(Request $request, Image $image)
    {
        $payload = $request->request->all();
        $this->getManager()->updateImage($image, $payload);
    }

    /**
     * Remove image from the database
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Delete individual image",
     *     tags={"stable"},
     *     statusCodes={
     *         204="Returned when successful",
     *         404="Returned when the image is not found",
     *         500="Returned when there is a server error"
     *     },
     *     parameters={
     *         {"name"="id", "dataType"="integer", "required"=true, "description"="ID of individual image"}
     *     }
     * )
     *
     * @Rest\View(statusCode=204)
     * @param Image $image
     * @throws \Exception
     */
    public function deleteAction(Image $image)
    {
        $this->getManager()->deleteImage($image);
    }

    /**
     * Get system statistics
     *
     * @Rest\QueryParam(name="general", key="general", nullable=true, default=0, description="Include general statistics")
     * @Rest\QueryParam(name="geoparsing", key="geoparsing", nullable=true, default=0, description="Include geoparsing statistics")
     * @Rest\QueryParam(name="regional", key="regional", nullable=true, default=0, description="Include regional statistics (number of images per world regions)")
     * @Rest\QueryParam(name="address", key="address", nullable=true, default=0, description="Include address statistics (number of images per discovered address)")
     * @Rest\QueryParam(name="domain", key="domain", nullable=true, default=0, description="Include domain statistics (number of images per URL domain)")
     * @Rest\QueryParam(name="execution_times", key="execution_times", nullable=true, default=0, description="Include task execution time statistics")
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Get system statistics. Specify filter parameters to filter out statistic results. If no filter parameters specified, all statistics are included.",
     *     tags={"stable"},
     *     statusCodes={
     *         204="Returned when successful",
     *         500="Returned when there is a server error"
     *     }
     * )
     *
     * @Rest\View(statusCode=200)
     *
     * @param Request $request
     * @return array
     */
    public function statisticsAction(Request $request)
    {
        return $this->getManager()->getStatistics($request->query->all());
    }

    /**
     * Get image manager
     *
     * @return \AppBundle\Services\ImageManager|object
     */
    public function getManager()
    {
        return $this->get('image_manager');
    }
}