<?php

namespace App\Command;

use App\Entity\Exhibit;
use App\Services\DocxConversion;
use Doctrine\ORM\EntityManagerInterface;
/*
use GGGGino\WordBundle\Factory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Reader\ReaderInterface;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Writer\Word2007\ElementTest;
*/
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Wikimate;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ImportDocxCommand extends Command
{
    protected static $defaultName = 'app:import-docx';
    private $phpWord;
    private $reader;
    private $io;
    private $em;
    private $twig;

    protected function configure()
    {
        $this
            ->setDescription('Import Docx audio text files')
            ->addArgument('rootDir', InputArgument::OPTIONAL, 'Source AudioGuias Directory', "./data")
            ->addArgument('cacheDir', InputArgument::OPTIONAL, 'Target Text AudioGuias Directory', "../data/museo")
            ->addOption('purge', null, InputOption::VALUE_NONE, 'Purge pages first')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Page limmit', 0)
            ->addOption('search', null, InputOption::VALUE_OPTIONAL, 'filename search', null )
        ;
    }

    public function __construct(EntityManagerInterface $em, Environment $twig, string $name = null)
    {
        parent::__construct($name);
        // $this->phpWord = $phpWord;
        $this->em = $em;
        $this->twig = $twig;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /*
        $tr = new GoogleTranslate();

        $tr->setSource('en'); // Translate from English
        $tr->setTarget('es'); // Translate to Spanish

        $text = $tr->translate('Hello World!');
        dump($text); die();

        // Outputs "Hola Mundo!"
        */

        $io = new SymfonyStyle($input, $output);
        $this->io = $io;
        $arg1 = $input->getArgument('rootDir');

        $finder = new Finder();


// find all files in the current directory
        $finder->files()->name(['*.docx', '*.doc'])->in($arg1);

// check if there are any search results
        if ($finder->hasResults()) {
            // ...
        }


        $repo = $this->em->getRepository(Exhibit::class);

        $api_url = 'http://mw.localhost/api.php';
        # $api_url = 'http://138.128.242.220/api.php';
        $username = 'tac';
        $password = 'no2smoke';
        $wiki = new Wikimate($api_url);
        echo "Attempting to log in . . . ";
        if ($wiki->login($username, $password)) {
            echo "Success.\n";
        } else {
            $error = $wiki->getError();
            echo "\nWikimate error: ".$error['login']."\n";
            exit(1);
        }


        $i = 0;
        $filesByDir = [];

        foreach ($finder as $file) {

            $absoluteFilePath = $file->getRealPath();
            $fileNameWithExtension = $file->getRelativePathname();

            $dir = $file->getRelativePath();
            if (empty($dir)) {
                $dir = "Index";
            }

            // filename is code
            $ext = pathinfo($absoluteFilePath, PATHINFO_EXTENSION);
            $basename = basename($file->getBasename(), ".$ext");

            if (!$exhibit = $repo->findOneBy(['filename' => $basename])) {
                $exhibit = (new Exhibit())
                    ->setFilename($basename)
                ;
                $this->em->persist($exhibit);
                $this->em->flush();
            }

            // $filesByDir[$dir][$code] = $code;

            $io->note('Importing ' . $absoluteFilePath . " to " . $exhibit->getCode());

            if ($ext == 'docx') {
                $service = new DocxConversion($absoluteFilePath);
                $text =$service->convertToText();
            } else {
                $text = shell_exec($cmd = sprintf('catdoc "%s"', $absoluteFilePath));
            }

            // loose formatting to wikitext
            $text = preg_replace('/(“|”)/', "''", $text);

            $text = trim($text);

            $exhibit
                // ->setFilename($absoluteFilePath)
                ->setTranscript($text);
                ;

            if ( ($limit = $input->getOption('limit')) && (++$i >= $limit) ) {
                break;
            }

        }

        $this->em->flush();
    }

    /* composer require GGGGino\WordBundle for this to work */
    private function extractTextFromDocx($filename): string
    {


            $this->reader= $this->phpWord->createReader();
            $io = $this->io;


                if ($this->reader->canRead($filename)) {
                    /** @var PhpWord $obj */
                    try {
                        $obj = $this->reader->load($filename);
                    } catch (\Exception $e) {

                        $io->error($e->getMessage() . ' ' . $filename);
                        return $e;
                    }

                    $text = '';
                    $sections = $obj->getSections();

                    foreach ($sections as $s) {
                        $els = $s->getElements();
                        /** @var ElementTest $e */
                        foreach ($els as $e) {
                            $class = get_class($e);
                            switch ($class) {
                                case TextBreak::class: $text .= "\n"; break;
                                case TextRun::class:
                                    /** @var TextRun $e */
                                    foreach ($e->getElements() as $element) {
                                        if (method_exists($class, 'getText')) {
                                            $text .= $e->getText();
                                        }
                                        dump($element); die();
                                        $text .= sprintf("(%s)", $class);

                                    }
                                    $text .= "\n";
                                    break;
                                default:
                                    if (method_exists($class, 'getText')) {
                                        $text .= $e->getText();
                                    }
                                    $text .= sprintf("(%s)", $class);
                            }
                        }
                    }

                    /*
                    if (get_class($e) === TextRun::class ) {
                        $text .= $e->getText();
                    } elseif (in_array()get_class($e) === Text::class ) {
                        $text .= $e->getText();
                    } elseif (get_class($e) === TextBreak::class ) {
                        $text .= " \n";
                    } else {
                        throw new \Exception('Unknown class type ' . get_class($e));
                    }
                    */
                    return $text;

                    print $text;
                    return true;

                    foreach ($obj->getSections() as $section) {
                        dump($section->getElements()); die();
                    }

                    var_dump(get_class($obj)); die();
                } else {
                    die("Cannot read $filename");
                }
        }

}
