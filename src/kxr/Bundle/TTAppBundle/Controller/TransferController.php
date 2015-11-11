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

	// Copy rows in batches to Mongodb to
	// Keep the memory foot print per query low
	//
	// This can be done with doctrine's orm's interation feature
	// http://doctrine-orm.readthedocs.org/projects/doctrine-orm/
	// en/latest/reference/batch-processing.html#iterating-large-results-for-data-processing
	// I'll try to accomplish that with postgresql's scroll feature
	$cursor_batch_size = 1000; // 1000 records assoc array ~= 162KB
	// Some variables to report
	$counter = [	'dbs_to_process'	=> [],
			'last_entry'		=> ['dbam'=>0,'dbnz'=>0],
			'rows_count' 		=> ['dbam'=>0,'dbnz'=>0],
			'batch_iterations'	=> ['dbam'=>0,'dbnz'=>0],
			'copy_time'		=> ['dbam'=>0,'dbnz'=>0],
			'delete_time'		=> ['dbam'=>0,'dbnz'=>0]		];

	// Create database connections,
	// Fetch the latest entry ids from both the database,
	// and check if we have any thing to process
	$conn=[];
	foreach ( ['dbam', 'dbnz'] as $db ) {
		$conn[$db] = $this->get('doctrine.dbal.'.$db.'_connection');
		// Get the last entry's id of our users table from both databases
		$counter['last_entry'][$db] = $conn[$db]->fetchAssoc('SELECT MAX(id) FROM users')['max'];
		if ( $counter['last_entry'][$db] ) {
			array_push( $counter['dbs_to_process'], "$db" );
		}
	}
	if ( count( $counter['dbs_to_process'] )  > 0 ) {
		foreach ( $counter['dbs_to_process'] as $db ) {
			// Begin transaction block
			$conn[$db]->beginTransaction();
			// Create the cursor
			$conn[$db]->executeQuery( "DECLARE cur NO SCROLL CURSOR FOR (SELECT * FROM users WHERE id <= ".$counter['last_entry'][$db].")" );
			$timer = microtime(true);
			do {
				// Fetch batch_size rows from the cursor
				$cursor_batch = $conn[$db]->fetchAll( "FETCH $cursor_batch_size FROM cur" );
				// Directly batch-insert them into mongodb
				$cursor_count = count($cursor_batch);
				if ( $cursor_count > 0 ) {
					$Collection->batchInsert( $cursor_batch );
					$counter['rows_count'][$db] += $cursor_count;
					$counter['batch_iterations'][$db] += 1;		
				}
			} while ( $cursor_count > 0 );
			$conn[$db]->executeQuery( "CLOSE cur" );
			//$conn[$db]->commit();
			$counter['copy_time'][$db] = microtime(true) - $timer;
	
			// Bulk delete the rows from pgsql
			$timer = microtime(true);
			$conn[$db]->executeQuery('DELETE FROM users WHERE  id <= '.$counter['last_entry'][$db] );
			$conn[$db]->commit();
			$counter['delete_time'][$db] = microtime(true) - $timer;
		}	
	
		return new Response(	"<b>Transaction Summary:</b>".
					"<pre>".
					"cursor_batch_size => ".$cursor_batch_size.
					"</pre><pre>".
					print_r( $counter, true ).
					"</pre>"
				);
	}
	else
		return new Response( "<b>Didn't find any record in the databases to process</b>".
					"<pre>".
					print_r( $counter, true ).
					"</pre>"
				);
   }
}
