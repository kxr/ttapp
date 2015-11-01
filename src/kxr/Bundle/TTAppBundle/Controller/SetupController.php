<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SetupController extends Controller
{
    public function indexAction(Request $request)
    {
        return new Response( $request->request->get('test') );
    }
}
