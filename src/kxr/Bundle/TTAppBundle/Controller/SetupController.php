<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SetupController extends Controller
{
    public function indexAction()
    {
        return new Response('You called Setup');
    }
}
