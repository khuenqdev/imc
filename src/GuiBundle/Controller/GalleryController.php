<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 2/11/2018
 * Time: 12:47 PM
 */

namespace GuiBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Form\ImageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GalleryController extends Controller
{
    /**
     * Main gallery index
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $images = $em->getRepository('AppBundle:Image')->findAll();

        return $this->render('GuiBundle:gallery:index.html.twig', array(
            'images' => $images,
        ));
    }

    /**
     * @param Request $request
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Image $image)
    {
        return $this->render('GuiBundle:gallery:view.html.twig', array(
            'image' => $image
        ));
    }

    /**
     * Show and edit an image
     *
     * @param Request $request
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Image $image)
    {
        $form = $this->createForm(ImageType::class, $image);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();

            return $this->redirect($this->generateUrl('gallery_index'));
        }

        return $this->render('GuiBundle:gallery:edit.html.twig', array(
            'image' => $image,
            'form' => $form->createView()
        ));
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