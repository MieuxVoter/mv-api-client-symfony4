<?php

declare(strict_types=1);

namespace App\Controller\Has;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;


trait FlashBag
{

    /** @var FlashBagInterface */
    protected $flashBag;

    /**
     * @return FlashBagInterface
     */
    public function getFlashBag(): FlashBagInterface
    {
        return $this->flashBag;
    }

    /**
     * @required
     * @param FlashBagInterface $flashBag
     */
    public function setFlashBag(FlashBagInterface $flashBag): void
    {
        $this->flashBag = $flashBag;
    }

}