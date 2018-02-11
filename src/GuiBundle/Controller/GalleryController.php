<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 2/11/2018
 * Time: 12:47 PM
 */

namespace GuiBundle\Controller;

use AppBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GalleryController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $images = $em->getRepository('AppBundle:Image')->findAll();

        return $this->render('GuiBundle:gallery:index.html.twig', array(
            'images' => $images,
        ));
    }

    /**
     * Finds and displays a image entity.
     *
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Image $image)
    {
        return $this->render('GuiBundle:gallery:show.html.twig', array(
            'image' => $image,
        ));
    }

    public function editAction()
    {

    }

    /**
     * Deletes a image entity.
     *
     * @param Request $request
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Image $image)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($image);
        $em->flush();

        return $this->redirectToRoute('gallery_index');
    }
}