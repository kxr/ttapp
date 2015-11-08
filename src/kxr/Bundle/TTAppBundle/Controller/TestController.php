<?php

namespace kxr\Bundle\TTAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function indexAction()
    {
	// Just for the fun of it, lets generate a random Pronounceable name instead of just random noise
	$titles = array ( "Mr", "Miss", "Mrs", "Dr", "Engg", "Master", "Sir", "Lord", "King", "Queen", "Prince", "Princess", "Emperor", "Empress", "Sheikh" );
	$vowels = array ( "a", "e", "i", "o", "u");
	$constants = array ( "b", "bl", "br", "c", "ch", "chr", "cl", "cr", "d", "dl", "dr", "f", "fl", "fr", "g", "gh", "gl", "gr", "h", "hr", "j", "jr", "k", "kh", "kl", "kr", "l", "ld", "lr", "m", "mr", "n", "ng", "nr", "p", "ph", "pl", "pr", "qu", "r", "rl", "rn", "s", "sh", "sl", "str", "t", "th", "tr", "v", "vl", "w", "wr", "x", "y", "z", "zr" );
$endings = array( "r", "n", "l", "", "", "", "" );
	$ran_name = 	$titles[array_rand($titles,1)] .
			" " .
			ucfirst(        $constants[array_rand($constants,1)] .
					$vowels[array_rand($vowels,1)] .
					$constants[array_rand($constants,1)] .
					$vowels[array_rand($vowels,1)] .
					$endings[array_rand($endings,1)] ) .
			" " .
			ucfirst(        $constants[array_rand($constants,1)] .
					$vowels[array_rand($vowels,1)] .
					$constants[array_rand($constants,1)] .
					$vowels[array_rand($vowels,1)] .
					$endings[array_rand($endings,1)] );

        return new Response( $ran_name );
    }
}
