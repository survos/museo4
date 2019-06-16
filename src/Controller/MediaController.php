<?php

namespace App\Controller;

use Aws\Result;
use Aws\S3\S3Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{
    /**
     * @Route("/media", name="s3_index")
     */
    public function index(S3Client $s3)
    {
        $result = $s3->listBuckets([]);
        return $this->render('media/index.html.twig', [
            'buckets' => $result->get('Buckets'),
            'controller_name' => 'MediaController',
        ]);
    }

    /**
     * @Route("/bucket/{name}", name="s3_bucket")
     */
    public function bucketList(S3Client $s3, $name)
    {
        /** @var Result $result */
        $result = $s3->listObjects(['Bucket' => $name]);
        return $this->render('media/bucket.html.twig', [
            'bucket' => $result->get('Contents'),
        ]);
    }

}
