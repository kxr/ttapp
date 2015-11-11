<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use MongoClient;

class CountController extends Controller
{
    public function indexAction()
    {
	// Connect to the Mongodb database
	$Mongo = new MongoClient( 'mongodb://'.
					$this->getParameter('mongodb_server').
					':'.
					$this->getParameter('mongodb_port')  );
	if (!$Mongo)
		return new Response('Error connecting to mongodb');
	$Collection = $Mongo->ttdb->users;
        return new Response('Total documents in "ttdb.users": ' . $Collection->count() );
    }
}
