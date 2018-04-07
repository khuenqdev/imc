<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 3/25/2018
 * Time: 12:51 PM
 */

namespace GuiBundle\Twig;

use AppBundle\Entity\Image;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;

class AppExtension extends \Twig_Extension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('imc_image', array($this, 'getImcImage')),
            new \Twig_SimpleFunction('base_path', array($this, 'getBasePath')),
        );
    }

    public function getImcImage(Image $image)
    {
        $basePath = $this->requestStack->getCurrentRequest()->getBasePath();

        return $basePath . '/downloaded/' . $image->path . '/' . $image->filename;
    }

    public function getBasePath()
    {
        $basePath = $this->requestStack->getCurrentRequest()->getBasePath();

        return $basePath;
    }
}