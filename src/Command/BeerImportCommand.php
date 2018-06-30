<?php

namespace App\Command;

use App\Entity\Beer;
use App\Entity\Brewer;
use App\Entity\Country;
use App\Entity\Type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Unirest;

class BeerImportCommand extends Command
{
    protected static $defaultName = 'beer:import';
    private $container;

    /**
     * BeerImportCommand constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {

        $this->container = $container;
        parent::__construct();
    }

    /**
     * beer:import configure method
     */
    protected function configure()
    {
        $this
            ->setDescription('Imports beers from a remote API (http://ontariobeerapi.ca/)')
            ->setHelp('Imports beers from a remote API (http://ontariobeerapi.ca/)')

        ;
    }

    /**
     * beer:import execute method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int, return 1 in case of error
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln("Importing beers...");

        try {
            // Get list of beers from remote API
            $response = Unirest\Request::get('http://ontariobeerapi.ca/beers');
        } catch (Unirest\Exception $e) {
            $output->writeln('<fg=red>' . $e->getMessage() . '</>');
            return 1;
        }

        // Decode JSON to array
        $beersArray = json_decode($response->raw_body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $output->writeln('<fg=red>' . json_last_error_msg() . '</>');
            return 1;
        }


        $entityManager = $this->container->get('doctrine')->getManager();

        //Begin transaction
        $entityManager->getConnection()->beginTransaction();

        //Counters initialized
        $beersCounter = 0;
        $beersDuplicates = 0;
        $countriesCounter = 0;
        $typesCounter = 0;
        $brewersCounter = 0;

        try {

            //Iterate over beers array
            foreach ($beersArray as $beer) {

                // Check for beer duplicate and if there is none create new
                $beerRepository = $this->container->get('doctrine')->getRepository(Beer::class);
                $beerDuplicate = $beerRepository->findOneBy(['beerId' => $beer['beer_id']]);

                if (!$beerDuplicate) {
                    $currentBeer = new Beer();

                    $currentBeer->setName($beer['name']);
                    $currentBeer->setImageURL($beer['image_url']);
                    $currentBeer->setBeerId($beer['beer_id']);

                    // get values needed to calculate price per liter from size element
                    // split string by spaces and non-breakable spaces
                    $size = preg_split('/\xA0|\x20/', $beer['size']);

                    $cansCount = floatval($size[0]);
                    $canSizeInL = floatval($size[5])/1000;

                    $pricePerLitre = $beer['price']/(($cansCount*$canSizeInL));
                    $pricePerLitre = number_format($pricePerLitre, 4, '.', '' );
                    $currentBeer->setPricePerLitre($pricePerLitre);

                    // Check for brewer duplicate and if there is none create new
                    $brewerRepository = $this->container->get('doctrine')->getRepository(Brewer::class);
                    $brewerDuplicate = $brewerRepository->findOneBy(['name' => $beer['brewer']]);

                    if (!$brewerDuplicate) {
                        $currentBrewer = new Brewer();
                        $currentBrewer->setName($beer['brewer']);
                        $entityManager->persist($currentBrewer);
                        $entityManager->flush();

                        $brewersCounter++;

                    } else {
                        $currentBrewer = $brewerDuplicate;
                    }
                    $currentBeer->setBrewer($currentBrewer);

                    // Check for country duplicate and if there is none create new
                    $countryRepository = $this->container->get('doctrine')->getRepository(Country::class);
                    $countryDuplicate = $countryRepository->findOneBy(['name' => $beer['country']]);

                    if (!$countryDuplicate) {

                        $currentCountry = new Country();
                        $currentCountry->setName($beer['country']);
                        $entityManager->persist($currentCountry);
                        $entityManager->flush();

                        $countriesCounter++;

                    } else {
                        $currentCountry = $countryDuplicate;
                    }
                    $currentBeer->setCountry($currentCountry);

                    // Check for beer type duplicate and if there is none create new
                    $typeRepository = $this->container->get('doctrine')->getRepository(Type::class);
                    $typeDuplicate = $typeRepository->findOneBy(['name' => $beer['type']]);

                    if (!$typeDuplicate) {

                        $currentType = new Type();
                        $currentType->setName($beer['type']);
                        $entityManager->persist($currentType);
                        $entityManager->flush();

                        $typesCounter++;

                    } else {
                        $currentType = $typeDuplicate;
                    }
                    $currentBeer->setType($currentType);

                    $entityManager->persist($currentBeer);
                    $entityManager->flush();
                    $beersCounter++;

                } else {
                    if ($input->getOption('verbose')) {
                        // Write to output beer duplicates names in verbose mode
                        $output->writeln('Beer ' . $beer['name'] . ' already exists in DB.');
                    }
                    $beersDuplicates++;
                }

            }
            //Commit transaction
            $entityManager->getConnection()->commit();

            //Show import statistics
            $output->writeln('<fg=green>Import completed</>');
            $output->writeln('<fg=green>Beers imported: '.$beersCounter.'</>');
            $output->writeln('<fg=green>Beers duplicates found: '.$beersDuplicates.'</>');
            $output->writeln('<fg=green>Brewers imported: '.$brewersCounter.'</>');
            $output->writeln('<fg=green>Types imported: '.$typesCounter.'</>');
            $output->writeln('<fg=green>Countries imported: '.$countriesCounter.'</>');


        } catch (\Exception $e) {
            $output->writeln('<fg=red>' . $e->getMessage() . ' in line '.$e->getLine().'</>');
            $output->writeln('<fg=red>' . $e->getTraceAsString() . '</>');
            $output->writeln('<fg=red>Import canceled</>');

            // Roll back transaction in case of exception
            $entityManager->getConnection()->rollBack();
            return 1;
        }


    }
}
