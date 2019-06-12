<?php

namespace App\Controller;

use App\Repository\ExhibitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Wikimate;

class AppController extends AbstractController
{

    /**
     * @Route("/exhibits-feed.{_format}", name="exhibits_feed")
     */
    public function exhibitsFeed(ExhibitRepository $repo, SerializerInterface $serializer, $_format='json')
    {
        $exhibits = $repo->findWithAudio();

        $data = $serializer->serialize($exhibits, 'json', ['groups' => ['playlist'] ] );

        return $this->json(json_decode($data, true));
    }

    /**
     * @Route("/audio-guide", name="player")
     */
    public function playlist(ExhibitRepository $repo)
    {
        $exhibits = $repo->findWithAudio();
        return $this->render("playlist.html.twig", [
            'exhibits' => $exhibits
        ]);
    }

    /**
     * @Route("/phpinfo", name="phpinfo")
     * PASSWORDS EXPOSED!!
    public function phpinfo(ExhibitRepository $repo)
    {
        ob_start();
        phpinfo();
        $html = ob_get_contents();
        ob_end_clean();
        return new Response($html);
    }
     */

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
