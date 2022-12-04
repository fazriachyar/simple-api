<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Doctrine\Persistence\ManagerRegistry;

class ProductController extends AbstractController
{
    #[Route('/product/view', name: 'view_product', methods: ['GET', 'HEAD'])]
    public function viewAllProduct(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $viewAllProduct = $em->getRepository(Product::class)
            ->findAll();
        if(!$viewAllProduct){
            $response = new Response("Not Found", Response::HTTP_OK);
        }

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($viewAllProduct, 'json');
        $response = new Response($data, Response::HTTP_OK);

        return $response;
    }

    #[Route('/product/{id}', name: 'add_product', methods: ['GET'])]
    public function addProduct(ManagerRegistry $doctrine,int $id): Response
    {
        $em = $doctrine->getManager();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $findProductById = $em->getRepository(Product::class)
            ->findOneBy([
                'id' => $id
            ]);
        
        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($findProductById, 'json');
        $response = new Response($data, Response::HTTP_OK);

        return $response
    }
}
