<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aws\Ec2\Ec2Client;

class TakedownController extends Controller
{
    public function indexAction()
    {

	// To be safe we will make sure that it is safe to take down a node
	// Additionally we will show the the status of each node

	//Get the internal IP of the first node pg1
	$Ec2Client = new Ec2Client ([
		'region' => 'us-east-1'
	]);
	$pg1 = $Ec2Client->DescribeInstances( array(
		'Filters' => array(
			array(
				'Name' => 'tag:Name',
				'Values' => 'pg1',
			)
		)
	) );

	return new Response($pg1);
    }
}
