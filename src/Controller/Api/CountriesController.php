<?php

namespace App\Controller\Api;

use App\Entity\Country;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CountriesController extends Controller
{
    /**
     *
     * Get all brewers JSON
     *
     * @return Response
     *
     * @Route("/api/countries", name="api_countries")
     */
    public function getAllCountries()
    {
        // Getting all countries
        $repository = $this->getDoctrine()->getRepository(Country::class);
        $countries = $repository->findAll();

        $arrayCountries = [];

        foreach ($countries as $country){
            $arrayCountry = ['id'=> $country->getId(),
                'name' => $country->getName()];

            $arrayCountries[] = $arrayCountry;
        }

        // Encoding $arrayCountries to JSON
        $jsonCountries = json_encode($arrayCountries);

        return new Response($jsonCountries, 200, ['Content-Type'=>'application/json','Accept'=>'application/json', 'Access-Control-Allow-Origin'=>'*']);
    }
}
