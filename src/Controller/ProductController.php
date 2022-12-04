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
    #[Route('/product', name: 'view_product', methods: ['GET', 'HEAD'])]
    public function viewAllProduct(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();
        
        $serializer = new Serializer([$normalizer], [$encoder]);

        $viewAllProduct = $em->getRepository(Product::class)
            ->findAll();

        $data = $serializer->serialize($viewAllProduct, 'json');
        $response = new Response($data, Response::HTTP_OK);

        return $response;
    }
}
