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

	//Get the internal IP of the both the node pg1 and pg2
	$Ec2Client = new Ec2Client ([
		'version' => 'latest',
		'region' => 'us-east-1'
	]);
	// name => instanceid
	$nodes = ['pg1' => '' , 'pg2' => ''];
	// db => port
	$dbs = array( 'dbam' => '5432', 'dbnz' => '5433' );
	// status
	$status = [];
	// Iterate through each node
	foreach ( $nodes as $node => $iid ){
		$status[$node] = [];
		// Get node IP
		$ec2c = $Ec2Client->DescribeInstances( array(
			'Filters' => array(
				array(
					'Name' => 'tag:Name',
					'Values' => array($node) ) ) ) );
		$ip = $ec2c['Reservations']['0']['Instances']['0']['PrivateIpAddress'];
		// While we are at it, populate the instance id
		$nodes[$node] = $ec2c['Reservations']['0']['Instances']['0']['InstanceId'];
		// Iterate through each db
		foreach ( $dbs as $db => $dbport ) {
			$status[$node][$db] = '';
			$dbconn = pg_connect("host=$ip port=$dbport user=monuser dbname=postgres connect_timeout=10");
			if (!$dbconn)
				$status[$node][$db] = 'DOWN';
			else {
				$res = pg_query($dbconn, 'select pg_is_in_recovery()');
				$stat = pg_fetch_assoc($res);
				if ( $stat['pg_is_in_recovery'] == 't' )
					$status[$node][$db] = 'SLAVE';
				elseif ( $stat['pg_is_in_recovery'] == 'f' )
					$status[$node][$db] = 'MASTER';
				else
					$status[$node][$db] = 'UNKNOWN';
			}
			
		}
	}

	


	return new Response( print_r($status, true).print_r($nodes, true) );
    }
}
