<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use MongoClient;
#use kxr\Bundle\TTAppBundle\Entity\Users;

class TransferController extends Controller
{
    public function indexAction()
    {

	// Connect to the Mongodb database
	$Mongo = new MongoClient( 'mongodb://'.
					$this->getParameter('mongodb_server').
					':'.
					$this->getParameter('mongodb_port').
					'/?w=0' );
	if (!$Mongo)
		return new Response('Error connecting to mongodb');
	$Collection = $Mongo->ttdb->users;

	// Create connection to the Postgresql databases
	#$conn_dbam = $this->getDoctrine()->getRepository('kxrTTAppBundle:Users');
	#$conn_dbnz = $this->getDoctrine()->getRepository('kxrTTAppBundle:Users');
	$conn_dbam = $this->get('doctrine.dbal.dbam_connection');
	$conn_dbnz = $this->get('doctrine.dbal.dbnz_connection');

	// Get the last entry's id of our users table from both databases
	$dbam_lastent = $conn_dbam->fetchAssoc('SELECT MAX(id) FROM users')['max'];
	$dbnz_lastent = $conn_dbnz->fetchAssoc('SELECT MAX(id) FROM users')['max'];

	// Copy rows in batches to Mongodb to
	// Keep the memory foot print per query low
	//
	// This can be done with doctrine's orm's interation feature
	// http://doctrine-orm.readthedocs.org/projects/doctrine-orm/
	// en/latest/reference/batch-processing.html#iterating-large-results-for-data-processing
	// I'll try to accomplish that with postgresql's scroll feature
	$cursor_batch_size = 1000; // 1000 records assoc array ~= 162KB

	$count=1;
	$conn_dbam->beginTransaction();
	$conn_dbam->executeQuery( "SET transaction_read_only=true" );
	$conn_dbam->executeQuery( "DECLARE cur NO SCROLL CURSOR FOR (SELECT * from users where id <= $dbam_lastent)" );
	do {
		$cursor_batch = $conn_dbam->fetchAll( "FETCH $cursor_batch_size FROM cur" );
		$cursor_count = count($cursor_batch);
		if ( $cursor_count > 0 ) {
			$Collection->batchInsert( $cursor_batch );
			$count ++;
		}
	} while ( $cursor_count > 0 );

	






	// Delete the copied rows from pgsql



	//$Coll->batchInsert([
	//	[ 'BatchInsert' => 'aaa', 'adfas' => 'adfas', '232' => 'adfas' ],
	//	[ 'BatchInsert' => 'xxx', 'adfas' => 'adfas', '232' => 'adfas' ],
	//	[ 'BatchInsert' => 'ggg', 'adfas' => 'adfas', '232' => 'adfas' ]
	//]);


	return new Response("iterations: ".$count);

   }
}
