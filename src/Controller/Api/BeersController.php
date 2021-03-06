<?php

namespace App\Controller\Api;

use App\Entity\Beer;
use App\Entity\Country;
use App\Entity\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BeersController extends Controller
{
    /**
     * Get all or only filtered beers JSON
     *
     * @param $request
     * @return Response
     *
     * @Route("/api/beers", name="api_beers")
     */
    public function getAllOrFilteredBeers(Request $request){

        // GET Parameters from request
        $limit = $request->query->get('limit');
        $offset =  $request->query->get('offset');

        $brewer =  $request->query->get('brewer');
        $name =  $request->query->get('name');
        $price =  $request->query->get('price');
        $country =  $request->query->get('country');
        $type =  $request->query->get('type');

        // Checking for provided parameters
        $findByAttr =[];

        if($brewer){
            $findByAttr['brewer'] = $brewer;
        }
        if($name){
            $findByAttr['name'] = $name;
        }
        if($price){
            $prices = explode(',', $price);

            $findByAttr['pricePerLitre']['min'] =floatval($prices[0])?floatval($prices[0]):-1;
            $findByAttr['pricePerLitre']['max'] =floatval($prices[1])?floatval($prices[1]):-1;

        }
        if($country){
            $repository = $this->getDoctrine()->getRepository(Country::class);
            $countryEntity = $repository->findOneBy(['name' => $country]);

            if($countryEntity){
                $findByAttr['country'] = $countryEntity->getId();
            }

        }
        if($type){
            $repository = $this->getDoctrine()->getRepository(Type::class);
            $typeEntity = $repository->findOneBy(['name' => $type]);

            if($typeEntity){
                $findByAttr['type'] = $typeEntity->getId();
            }

        }

        // Getting filtered beers
        $repository = $this->getDoctrine()->getRepository(Beer::class);
        $beers = $repository->findAllFilteredAndPaginated($findByAttr, $limit, $offset);

        $arrayBeers= [];

        foreach ($beers as $beer){

            $arrayBeer = [  'id' => $beer->getBeerId(),
                            'name' => $beer->getName(),
                            'brewer_id' => $beer->getBrewer()->getId(),
                            'brewer' => $beer->getBrewer()->getName(),
                            'country' => $beer->getCountry()->getName(),
                            'type' => $beer->getType()->getName(),
                            'price' => $beer->getPricePerLitre()];

            $arrayBeers[] = $arrayBeer;

        }

        // Encoding $arrayBeers to JSON
        $jsonBeers = json_encode($arrayBeers);

        return new Response($jsonBeers, 200, ['Content-Type'=>'application/json','Accept'=>'application/json', 'Access-Control-Allow-Origin'=>'*']);

    }

    /**
     * Get single beer JSON
     *
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     *
     * @Route("/api/beer/{id}", name="api_beer")
     */
    public function getSingleBeer($id){

        $repository = $this->getDoctrine()->getRepository(Beer::class);
        $beer = $repository->findOneBy(['beerId'=>$id]);

        if ($beer){

            $arrayBeer = [  'id' => $beer->getBeerId(),
                            'name' => $beer->getName(),
                            'brewer_id' => $beer->getBrewer()->getId(),
                            'brewer' => $beer->getBrewer()->getName(),
                            'country' => $beer->getCountry()->getName(),
                            'type' => $beer->getType()->getName(),
                            'price' => $beer->getPricePerLitre(),
                            'imageUrl' => $beer->getImageURL()];

            $jsonBeer = json_encode($arrayBeer);
            return new Response($jsonBeer, 200, ['Content-Type'=>'application/json','Accept'=>'application/json', 'Access-Control-Allow-Origin'=>'*']);
        }
        else{
            throw new NotFoundHttpException("Beer not found");
        }


    }
}
