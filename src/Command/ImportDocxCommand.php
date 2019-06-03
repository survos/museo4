<?php

namespace App\Command;

use App\Entity\Exhibit;
use App\Services\DocxConversion;
use Doctrine\ORM\EntityManagerInterface;
use GGGGino\WordBundle\Factory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Reader\ReaderInterface;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Writer\Word2007\ElementTest;
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
            ->addArgument('rootDir', InputArgument::OPTIONAL, 'AudioGuias Directory', "/var/www/data/zac-guias")
            ->addOption('purge', null, InputOption::VALUE_NONE, 'Purge pages first')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Page limmit', 0)
            ->addOption('search', null, InputOption::VALUE_OPTIONAL, 'filename search', null )
        ;
    }

    public function __construct(Factory $phpWord, EntityManagerInterface $em, Environment $twig, string $name = null)
    {
        parent::__construct($name);
        $this->phpWord = $phpWord;
        $this->em = $em;
        $this->twig = $twig;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $tr = new GoogleTranslate();

        $tr->setSource('en'); // Translate from English
        $tr->setTarget('es'); // Translate to Spanish

        $text = $tr->translate('Hello World!');
        dump($text); die();

        // Outputs "Hola Mundo!"

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

        if ($input->getOption('purge'))
        {


            while (true) { // until there's only one page left
                $result = $wiki->query(['action' => 'query', 'list' => 'allpages', 'format' => 'php']);
                foreach ($result['query']['allpages'] as $pageArray) {
                    $title = $pageArray['title'];
                    if ($title !== 'Main Page') {
                        $io->warning("Deleting $title");
                        $p = $wiki->getPage($title);
                        $p->delete('Deleted before re-import');
                    }
                }
                if (empty($result['continue'])) {
                    break;
                }
            }
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
            $code = basename($file->getFilename(), ".$ext");
            $io->note('Importing ' . $absoluteFilePath . " to $code");

            $filesByDir[$dir][$code] = $code;

            if (!$exhibit = $repo->findOneBy(['code' => $code])) {
                $exhibit = (new Exhibit())
                    ->setCode($code);
                $this->em->persist($exhibit);
            }

            if ($ext == 'docx') {
                $service = new DocxConversion($absoluteFilePath);
                $text =$service->convertToText();
            } else {
                $text = shell_exec($cmd = sprintf('catdoc "%s"', $absoluteFilePath));
            }

            // old way with PhpOffice $text = $this->extractTextFromDocx($absoluteFilePath);


            $exhibit
                ->setFilename($absoluteFilePath)
                ->setTranscript($text);
                ;


            $page = $wiki->getPage($code);

            $text = "==Texto==\n\n" . $exhibit->getTranscript();

            $text .= "\n\n==Misc==\n\nArchivo Original: f" . $exhibit->getFilename();

            $page->setText($text);
            if ( ($limit = $input->getOption('limit')) && ($i++ > $limit) ) {
                break;
            }

        }

        $page = $wiki->getPage("Index");

        $indexContent = $this->twig->render("indexPage.wiki.twig", [
            'filesByDir' => $filesByDir
        ]);
        print $indexContent;
        $page->setText($indexContent);

        if ($page->exists()) {

        } else {
        }

        dump($filesByDir); die();
        $this->em->flush();
    }

    private function publishToWiki(Exhibit $exhibit)
    {

#$wiki->setDebugMode(TRUE);




        echo "Fetching 'Sausages'...\n";
        $page = $wiki->getPage('Sausages');




// check if the page exists or not
        if (!$page->exists() ) {
            echo "'Sausages' doesn't exist.\n";
            /** @todo: add content and save page */
        } else {
            // get the page title
            echo "Title: ".$page->getTitle()."\n";
            // get the number of sections on the page
            echo "Number of sections: ".$page->getNumSections()."\n";
            // get an array of where each section starts and its length
            echo "Section offsets:".print_r($page->getSectionOffsets(), true)."\n";

        }
    }

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
