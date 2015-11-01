<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Aws\CodeDeploy\CodeDeployClient;

class SetupController extends Controller {

    public function indexAction(Request $request) {

        $webhook_payload = $request->request->get('payload');
	if ( !empty( $webhook_payload ) ) {
		$payload_params = json_decode($webhook_payload, true);
		$head_commit_id = $payload_params['head_commit']['id'];
        	$logger = $this->get('logger');
        	$logger->error(print_r($head_commit_id, true));

		$CodeDeployClient = new CodeDeployClient( [ 'version' => 'latest', 'region' => 'us-east-1' ] );
		$DeploymentResult = $CodeDeployClient->createDeployment ([
			'applicationName' => 'ttapp',
			'deploymentGroupName' => 'ttapp_dg',
			'revisionType' => 'GitHub',
			'revision' => array(
				'repository' => 'kxr/ttapp',
				'commitId' => "$head_commit_id"
			)
		]);
	        $logger->error(print_r($DeploymentResult, true));
	}
    }
}
