<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SetupController extends Controller {

    public function indexAction(Request $request) {

        $webhook_payload = $request->request->get('payload');
	if ( !empty( $webhook_payload ) ) {
		$payload_params = json_decode($webhook_payload, true);
	}
        $logger = $this->get('logger');
        $logger->error(print_r($payload_params, true));
        return new Response( 'nananna' );

    }
}
