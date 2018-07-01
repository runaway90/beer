<?php

namespace App\Controller\Api;

use App\Entity\Type;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TypesController extends Controller
{
    /**
     *
     * Get all beer types JSON
     *
     * @return Response
     *
     * @Route("/api/types", name="api_types")
     */
    public function getAllTypes()
    {
        // Getting all beer types
        $repository = $this->getDoctrine()->getRepository(Type::class);
        $types = $repository->findAll();

        $arrayTypes = [];

        foreach ($types as $type){
            $arrayType = ['id'=> $type->getId(),
                'name' => $type->getName()];

            $arrayTypes[] = $arrayType;
        }

        // Encoding $arrayTypes to JSON
        $jsonTypes = json_encode($arrayTypes);

        return new Response($jsonTypes, 200, ['Content-Type'=>'application/json','Accept'=>'application/json', 'Access-Control-Allow-Origin'=>'*']);
    }
}
