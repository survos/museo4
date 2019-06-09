<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Wikimate;

class AppController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AppController.php',
        ]);
    }

    /**
     * @Route("/test", name="test")
     */
    public function test()
    {
        $api_url = 'http://mw.localhost';
        $username = 'tac';
        $password = 'no2smoke';
        $wiki = new Wikimate($api_url);
#$wiki->setDebugMode(TRUE);

        echo "Attempting to log in . . . ";
        if ($wiki->login($username, $password)) {
            echo "Success.\n";
        } else {
            $error = $wiki->getError();
            echo "\nWikimate error: ".$error['login']."\n";
            exit(1);
        }

        echo "Fetching 'Sausages'...\n";
        $page = $wiki->getPage('Sausages');

// check if the page exists or not
        if (!$page->exists() ) {
            echo "'Sausages' doesn't exist.\n";

        } else {
            // get the page title
            echo "Title: ".$page->getTitle()."\n";
            // get the number of sections on the page
            echo "Number of sections: ".$page->getNumSections()."\n";
            // get an array of where each section starts and its length
            echo "Section offsets:".print_r($page->getSectionOffsets(), true)."\n";

        }

    }
}
