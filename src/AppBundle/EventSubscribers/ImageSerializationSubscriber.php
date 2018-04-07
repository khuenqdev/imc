<?php
/**
 * Created by PhpStorm.
 * User: khuen
 * Date: 4/4/2018
 * Time: 4:43 PM
 */

namespace AppBundle\EventSubscribers;


use AppBundle\Entity\Image;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class ImageSerializationSubscriber implements EventSubscriberInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @inheritdoc
     */
    static public function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'class' => Image::class,
                'method' => 'onPostSerialize'
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $event->getVisitor()->addData('local_src', $this->getLocalSrc($event->getObject()));
        $event->getVisitor()->setData('thumbnail', $this->getThumbnailPath($event->getObject()));
    }

    /**
     * Get link to server image
     *
     * @param $image
     * @return string
     */
    private function getLocalSrc($image)
    {
        $host = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        $basePath = $this->requestStack->getCurrentRequest()->getBasePath();

        return $host . $basePath . '/downloaded/' . $image->path . '/' . $image->filename;
    }

    /**
     * Get thumbnail path
     *
     * @param $image
     * @return string
     */
    private function getThumbnailPath($image)
    {
        $basePath = $this->requestStack->getCurrentRequest()->getBasePath();

        return $basePath . '/downloaded/thumbnails/' . $image->thumbnail;
    }
}