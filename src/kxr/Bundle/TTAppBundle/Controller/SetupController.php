<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class SetupController extends Controller
{
    public function indexAction(Request $request)
    {
        $webhook_post = $request->request->get('payload');
        $logger = $this->get('logger');
        $logger->error(print_r($webhook_post, true));
        return new Response( 'nananna' );

    }
}
