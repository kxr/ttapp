<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function indexAction()
    {
	// Just for the fun of it, lets generate a random Pronounceable name instead of just random noise
	$titles = array ( "Mr", "Miss", "Mrs", "Dr", "Engg", "Master", "Sir", "Lord", "King", "Queen", "Prince", "Princess", "Emperor", "Empress", "Sheikh", "Muhammad" );
	$alphabets = array ( "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
	$vowels = array ( "a", "e", "i", "o", "u");
	$constants = array ( "b", "bl", "br", "c", "ch", "chr", "cl", "cr", "d", "dl", "dr", "f", "fl", "fr", "g", "gh", "gl", "gr", "h", "hr", "j", "jr", "k", "kh", "kl", "kr", "l", "ld", "lr", "m", "mr", "n", "ng", "nr", "p", "ph", "pl", "pr", "qu", "r", "rl", "rn", "s", "sh", "sl", "str", "t", "th", "tr", "v", "vl", "w", "wr", "x", "y", "z", "zr" );
	$endings = array( "r", "n", "l", "", "", "", "" );

	// We will generate the first character from the whole set of alphabets for even distribution
	$random_char = $alphabets[array_rand($alphabets,1)];
	$first_name = 	$titles[array_rand($titles,1)] .
			" " .
			ucfirst(        $random_char .
					$vowels[array_rand($vowels,1)] .
					$constants[array_rand($constants,1)] .
					$vowels[array_rand($vowels,1)] .
					$endings[array_rand($endings,1)] );

	$last_name =	ucfirst(        $constants[array_rand($constants,1)] .
					$vowels[array_rand($vowels,1)] .
					$constants[array_rand($constants,1)] .
					$vowels[array_rand($vowels,1)] .
					$endings[array_rand($endings,1)] );

	// Random age between 1 and 120
	$age = rand ( 1, 120);


	// We check the first character of the name is b/w a-m or n-z
	// ascii value comparison should be more efficient
	if ( ord($random_char) <= 109 )
		$db='doctrine.dbal.db-am_connection';
	else
		$db='doctrine.dbal.db-nz_connection';

	

        return new Response( '<b>First Name:</b> ' . $first_name . '<br><b>Last Name:</b> ' . $last_name . '<br><b>Age:</b> ' . $age . '<br> Database: ' . $db );
    }
}
