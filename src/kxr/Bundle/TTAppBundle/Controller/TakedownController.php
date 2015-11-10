<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aws\Ec2\Ec2Client;

class TakedownController extends Controller
{
    public function stopitAction($node)
    {
	if ( $node != 'pg1' and $node != 'pg2' )
		return new Response( "Sorry, this controller cannot take down anything other than pg1 or pg2" );

	$Ec2Client = new Ec2Client ([
		'version' => 'latest',
		'region' => 'us-east-1'
	]);
	// Find the instance id
	$ec2_desc = $Ec2Client->DescribeInstances( array(
		'Filters' => array( array(
				'Name' => 'tag:Name',
				'Values' => array($node) ) ) ) );
	if (!$ec2_desc)
		return new Response( "Sorry didnt find any instance by Name tag: $node" );
	$instid = $ec2_desc['Reservations']['0']['Instances']['0']['InstanceId'];
	$inststate = $ec2_desc['Reservations']['0']['Instances']['0']['State']['Name'];

	$stopresponse = $Ec2Client->stopInstances(array(
		'InstanceIds' => array($instid),
		'Force' => true ) );

	return new Response(	"<b>Sent force shutdown call on $node($instid)</b> <br />".
				"The previous state was: $inststate<br />".
				"Got the following response:".
				"<pre>".
				print_r($stopresponse['StoppingInstances'], true).
				print_r($stopresponse['@metadata'],true).
				"</pre>" );
	
    }
    public function indexAction()
    {

	//Get the internal IP of the both the node pg1 and pg2
	$Ec2Client = new Ec2Client ([
		'version' => 'latest',
		'region' => 'us-east-1'
	]);
	// name => instanceid
	$nodes = ['pg1' , 'pg2' ];
	// db => port
	$dbs = array( 'dbam' => '5432', 'dbnz' => '5433' );
	// status
	$status = [];
	// Iterate through each node
	foreach ( $nodes as $node ){
		$status[$node] = [];
		// Get node IP
		$ip = $Ec2Client->DescribeInstances( array(
			'Filters' => array( array(
					'Name' => 'tag:Name',
					'Values' => array($node) ) ) ) )
			['Reservations']['0']['Instances']['0']['PrivateIpAddress'];
		// Iterate through each db
		foreach ( $dbs as $db => $dbport ) {
			$status[$node][$db] = '';
			$dbconn = pg_connect("host=$ip port=$dbport user=monuser dbname=postgres connect_timeout=5");
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
	// Should have done this is using twig but short on time :(
	return new Response(	'Following is the status of the cluster'.
				'<pre>'.print_r($status, true).'</pre>'.
				'<hr />'.
				'<a href=/takedown/pg1>Click here to stop PG1</a>'.
				'&emsp;&emsp;'.
				'<a href=/takedown/pg2>Click here to stop PG2</a>'.
				'<br />'.
				'Be carefull, it will blindly stop the instance'
	);
    }
}
