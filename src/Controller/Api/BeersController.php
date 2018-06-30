<?php

namespace App\Controller\Api;

use App\Entity\Beer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BeersController extends Controller
{
    
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

            $arrayBeer = [  "name" => $beer->getName(),
                            'brewer' => $beer->getBrewer()->getName(),
                            'country' => $beer->getCountry()->getName(),
                            'type' => $beer->getType()->getName(),
                            'price' => $beer->getPricePerLitre()];

            $jsonBeer = json_encode($arrayBeer);
            return new Response($jsonBeer, 200, ['Content-Type'=>'application/json','Accept'=>'application/json']);
        }
        else{
            throw new NotFoundHttpException("Beer not found");
        }


    }
}
