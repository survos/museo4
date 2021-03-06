<?php

namespace App\Controller;

use App\Entity\Exhibit;
use App\Form\ExhibitType;
use App\Repository\ExhibitRepository;
use App\Repository\MuseumRepository;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class ExhibitController extends AbstractController
{
    /**
     * @Route("/player/{id}", name="exhibit_player", methods={"GET"})
     */
    public function player(Exhibit $exhibit): Response
    {
        return $this->render('exhibit/player.html.twig', [
            'exhibit' => $exhibit
        ]);
    }

    /**
     * @Route("/recorder/{id}", name="exhibit_recorder", methods={"GET"})
     */
    public function recorder(Exhibit $exhibit): Response
    {
        return $this->render('exhibit/microphone.html.twig', [
            'exhibit' => $exhibit
        ]);
    }

    /**
     * @Route("/save_audio/{id}", name="exhibit_save_audio", methods={"POST"})
     */
    public function saveAudio(Request $request, S3Client $s3, Exhibit $exhibit, EntityManagerInterface $em): ?Response
    {

        foreach ($request->files as $file) {
            /** @var UploadedFile $fileName */
            $tempName = $file->getPath() . '/' . $file->getFilename();
            if (!file_exists($tempName)) {
                throw new \Exception("Problem reading " . $fileName);
            }

            $newFilename =  $request->get('video-filename');

            // upload to s3 bucket
            $bucket = getenv('S3_BUCKET') ?: 'museo.survos.com'; //@hack!
            $result = $s3->upload($bucket, $newFilename, fopen($tempName, 'rb'), 'public-read');

            // dump($result);

            $exhibit
                ->setDuration(round(filesize($tempName) / 50));
            $em->flush();

            $url = sprintf('https://s3.amazonaws.com/%s/%s', $bucket, $newFilename);


            /*

            dump($result); die();

            $dir = 'uploads';
            $result = $file->move($dir, $newFilename);
            dump($result, $dir, $newFilename);

            $filePath = 'uploads/' . $newFilename;
            if (!move_uploaded_file($tempName, $filePath)) {
                echo ('Problem saving file.');
            }

            die();
            */
            return new JsonResponse([
                'url' => $url,
                // 'result' => $result,
                'uploaded-filename' => $newFilename]);
        }

        /*



        if (!isset($_POST['audio-filename']) && !isset($_POST['video-filename'])) {
            echo 'PermissionDeniedError';
        }



        $fileName = '';
        $tempName = '';

        if (isset($_POST['audio-filename'])) {
            $fileName = $_POST['audio-filename'];
            $tempName = $_FILES['audio-blob']['tmp_name'];
        } else {
            $fileName = $_POST['video-filename'];
            $tempName = $_FILES['video-blob']['tmp_name'];
        }

        if (empty($fileName) || empty($tempName)) {
            echo 'PermissionDeniedError';
        }
        $filePath = 'uploads/' . $fileName;

        // make sure that one can upload only allowed audio/video files
        $allowed = array(
            'webm',
            'wav',
            'mp4',
            'mp3',
            'ogg'
        );
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!$extension || empty($extension) || !in_array($extension, $allowed)) {
            echo 'PermissionDeniedError';
        }

        if (!move_uploaded_file($tempName, $filePath)) {
            echo ('Problem saving file.');
        }

        echo ($filePath);
        dump($request); die();
        return $this->render('exhibit/microphone.html.twig', [
            'exhibit' => $exhibit
        ]);
        */
    }


    /**
     * @Route("/exhibits", name="exhibit_index", methods={"GET"})
     * @Route("/home", name="home", methods={"GET"})
     */
    public function index(ExhibitRepository $exhibitRepository, MuseumRepository $museumRepository): Response
    {
        return $this->render('exhibit/index.html.twig', [
            'museums' => $museumRepository->findAll(),
            'exhibits' => $exhibitRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="exhibit_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $exhibit = new Exhibit();
        $form = $this->createForm(ExhibitType::class, $exhibit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($exhibit);
            $entityManager->flush();

            return $this->redirectToRoute('exhibit_index');
        }

        return $this->render('exhibit/new.html.twig', [
            'exhibit' => $exhibit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="exhibit_show", methods={"GET"})
     */
    public function show(Exhibit $exhibit): Response
    {
        return $this->render('exhibit/show.html.twig', [
            'exhibit' => $exhibit,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="exhibit_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Exhibit $exhibit): Response
    {
        $form = $this->createForm(ExhibitType::class, $exhibit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('exhibit_show', $exhibit->getRp());
        }

        return $this->render('exhibit/edit.html.twig', [
            'exhibit' => $exhibit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="exhibit_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Exhibit $exhibit): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exhibit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($exhibit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('exhibit_index');
    }
}
