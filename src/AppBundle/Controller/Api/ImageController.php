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
     * @return array
     */
    public function listAction($page, $limit)
    {
        $images = $this->getManager()->listImages();

        return $this->get('knp_paginator')->paginate($images, $page, $limit)->getItems();
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
        $this->getManager()->updateImage($image, $payload);
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
        $this->getManager()->deleteImage($image);
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