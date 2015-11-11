<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aws\Ec2\Ec2Client;

class TeardownController extends Controller
{
    public function indexAction()
    {
	// This endpoint will remove any instances that were added
	// by the launch configuration

	// Launch configuration filter
	$lc_filter = [ 'Name' => 'tag:Type', 'Values' => ['Autoscale'] ];
	$Ec2Client = new Ec2Client ([
		'version' => 'latest',
		'region' => 'us-east-1'
	]);
	// Find the instance id
	$reservations = $Ec2Client->DescribeInstances( array(
		'Filters' => array( $lc_filter ) ) )
	['Reservations'];
 
        if (!$reservations) 
                return new Response( 'Sorry didnt find any instance by tag Type: "Autoscale"' );
	else
		$inst_list = [];
		foreach ( $reservations as $reservation)
			foreach ( $reservation['Instances'] as $instance )
				array_push( $inst_list, $instance['InstanceId']);

		return new Response( 'Found the following instances:'.
					'<pre>'.
					print_r($inst_list, true).
					'</pre>'
				);
    }
}
