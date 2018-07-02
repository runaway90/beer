<?php

namespace App\Controller\Api;

use App\Entity\Brewer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BrewersController extends Controller
{
    /**
     *
     * Get all brewers JSON 
     *
     * @return Response
     *
     * @Route("/api/brewers", name="api_brewers")
     */
    public function getAllBrewers()
    {
        // Getting all brewers
        $repository = $this->getDoctrine()->getRepository(Brewer::class);
        $brewers = $repository->findAll();

        $arrayBrewers = [];

        foreach ($brewers as $brewer){
            $arrayBrewer = ['id'=> $brewer->getId(),
                            'name' => $brewer->getName(),
                            'numberOfBeersAssigned' => count($brewer->getBeers())];

            $arrayBrewers[] = $arrayBrewer;
        }

        // Encoding $arrayBrewers to JSON
        $jsonBrewers = json_encode($arrayBrewers);

        return new Response($jsonBrewers, 200, ['Content-Type'=>'application/json','Accept'=>'application/json', 'Access-Control-Allow-Origin'=>'*']);
    }
}
