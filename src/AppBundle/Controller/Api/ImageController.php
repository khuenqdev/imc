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
use Knp\Component\Pager\Pagination\PaginationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
     * @Rest\QueryParam(name="page", key="page", default="1", nullable=true)
     * @Rest\QueryParam(name="limit", key="limit", default="10", nullable=true)
     * @Rest\QueryParam(name="sort", key="sort", default=null, nullable=true)
     * @Rest\QueryParam(name="direction", key="direction", default=null, nullable=true)
     * @Rest\QueryParam(name="filter_field", key="filter_field", default=null, nullable=true)
     * @Rest\QueryParam(name="filter_value", key="filter_value", default=null, nullable=true)
     * @Rest\View(statusCode=200)
     * @param $page
     * @param $limit
     * @return PaginationInterface
     */
    public function listAction($page, $limit)
    {
        $images = $this->get('image_manager')->listImages();
        $paginated = $this->get('knp_paginator')->paginate($images, $page, $limit);

        return $paginated;
    }

    /**
     * Create an image
     * (Does not seem to be reasonable as the system crawls for images)
     *
     * @deprecated
     * @Rest\RequestParam(name="source", key="source", default=null, nullable=true)
     * @Rest\RequestParam(name="src", key="src", default="", nullable=false, strict=true)
     * @ParamConverter("source", class="AppBundle:Link", options={}, optional=true)
     * @Rest\View(statusCode=201)
     * @param Request $request
     * @param null $source
     * @throws \Exception
     */
    public function createAction(Request $request, $source = null)
    {
        $payload = $request->request->all();
        $this->get('image_manager')->createImage($source, $payload);
    }

    /**
     * Read an image
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
     * @Rest\View(statusCode=204)
     * @param Request $request
     * @param Image $image
     * @throws \Exception
     */
    public function updateAction(Request $request, Image $image)
    {
        $payload = $request->request->all();
        $this->get('image_manager')->updateImage($image, $payload);
    }

    /**
     * Remove image from the database
     *
     * @Rest\View(statusCode=204)
     * @param Image $image
     * @throws \Exception
     */
    public function deleteAction(Image $image)
    {
        $this->get('image_manager')->deleteImage($image);
    }
}