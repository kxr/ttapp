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
	$pg1 = $Ec2Client->DescribeInstances( array(
		'Filters' => array(
			array(
				'Name' => 'tag:Name',
				'Values' => array('pg1'),
			)
		)
	) );
	$pg1_ip = $pg1['Reservations']['0']['Instances']['0']['PrivateIpAddress'];
	$pg2 = $Ec2Client->DescribeInstances( array(
		'Filters' => array(
			array(
				'Name' => 'tag:Name',
				'Values' => array('pg2'),
			)
		)
	) );
	$pg1_ip = $pg2['Reservations']['0']['Instances']['0']['PrivateIpAddress'];

	$pgdb_status=""
	//pg1->dbam
	$dbconn = pg_connect("host=$pg1_ip port=5432 user=monuser dbname=postgres connect_timeout=5")
	if (!$dbconn) {
		$pgdb_status .= "PG1-DBAM: DOWN"
	}
	else {
		$res = pg_query($dbconn, 'select pg_is_in_recovery()');
		$pgdb_status .= "PG1-DBAM: ".print_r($res, true);
	}
	//pg1->dbnz
	$dbconn = pg_connect("host=$pg1_ip port=5433 user=monuser dbname=postgres connect_timeout=5")
	if (!$dbconn) {
		$pgdb_status .= "PG1-DBNZ: DOWN"
	}
	else {
		$res = pg_query($dbconn, 'select pg_is_in_recovery()');
		$pgdb_status .= "PG1-DBNZ: ".print_r($res, true);
	}
	//pg2->dbam
	$dbconn = pg_connect("host=$pg2_ip port=5432 user=monuser dbname=postgres connect_timeout=5")
	if (!$dbconn) {
		$pgdb_status .= "PG2-DBAM: DOWN"
	}
	else {
		$res = pg_query($dbconn, 'select pg_is_in_recovery()');
		$pgdb_status .= "PG2-DBAM: ".print_r($res, true);
	}
	//pg1->dbnz
	$dbconn = pg_connect("host=$pg2_ip port=5433 user=monuser dbname=postgres connect_timeout=5")
	if (!$dbconn) {
		$pgdb_status .= "PG2-DBNZ: DOWN"
	}
	else {
		$res = pg_query($dbconn, 'select pg_is_in_recovery()');
		$pgdb_status .= "PG2-DBNZ: ".print_r($res, true);
	}

	return new Response( $pgdb_status );
    }
}
