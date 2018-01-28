<?php


namespace GuiBundle\Controller;

use AppBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class VerificationController extends Controller
{

    /**
     * Fetch one unverified image
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showImageAction()
    {
        $image = $this->getManager()->fetchUnverifiedImage();

        if ($image) {
            return $this->render('@Gui/verfication/show_unverified.html.twig',[
                'image' => $image
            ]);
        }

        return $this->render('@Gui/verfication/noimage.html.twig');
    }

    /**
     * Mark an image as correct
     *
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function verifyAction(Image $image)
    {
        $this->getManager()->verifyImageLocation($image, $this->get('request')->query->get('is_correct'));

        return $this->redirectToRoute('verifification_show_image');
    }

    /**
     * Ignore an image during verification
     *
     * @param Image $image
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ignoreAction(Image $image)
    {
        $ignoreImageIds = $this->get('session')->get('ignored_image_ids', []);
        $ignoreImageIds[] = $image->id;
        $this->get('session')->set('ignored_image_ids', $ignoreImageIds);

        return $this->redirectToRoute('verifification_show_image');
    }

    /**
     * Get the image manager
     *
     * @return \AppBundle\Services\ImageManager|object
     */
    public function getManager()
    {
        return $this->get('image_manager');
    }

}