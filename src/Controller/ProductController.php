<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
    #[Route('/product/view', name: 'view_AllProduct', methods: ['GET', 'HEAD'])]
    public function viewAllProduct(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $viewAllProduct = $em->getRepository(Product::class)
            ->viewAllProduct();

        if(!$viewAllProduct){
            $message['response']['failed'] = 'Product belum tersedia.';
            $serializer = new Serializer([$normalizer], [$encoder]);
            $data = $serializer->serialize($message, 'json');
            $response = new Response($data, Response::HTTP_OK);
        }

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($viewAllProduct, 'json');
        $response = new Response($data, Response::HTTP_OK);

        return $response;
    }

    #[Route('/product/{id}', name: 'view_productById', methods: ['GET'])]
    public function viewProductById(ManagerRegistry $doctrine,int $id): Response
    {
        $em = $doctrine->getManager();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $findProductById = $em->getRepository(Product::class)
            ->findOneBy([
                'id' => $id,
                'action' => ['U','I']
            ]);

        if(!$findProductById){
            $message['response']['failed'] = 'Product tidak tersedia.';
            $serializer = new Serializer([$normalizer], [$encoder]);
            $data = $serializer->serialize($message, 'json');
            $response = new Response($data, Response::HTTP_OK);
        }else {
            $serializer = new Serializer([$normalizer], [$encoder]);
            $data = $serializer->serialize($findProductById, 'json');
            $response = new Response($data, Response::HTTP_OK);
        }

        return $response;
    }

    #[Route('/product/add', name: 'add_product', methods: ['POST'])]
    public function addProduct(ManagerRegistry $doctrine,Request $request): Response
    {   
        $data = json_decode($request->getContent(), true);
        $em = $doctrine->getManager();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $findProductByCode = $em->getRepository(Product::class)
            ->findOneBy([
                'productCode' => $data['productCode'],
                'action' => ['U','I']
            ]);

        if($findProductByCode){
            $message['response']['failed'] = 'Product Code telah tersedia.';
        } else {
            $product = new Product();
            $product->setName($data['name']);
            $product->setQuantity($data['quantity']);
            $product->setCategory($data['category']);
            $product->setBrand($data['brand']);
            $product->setPrice($data['price']);
            $product->setProductCode($data['productCode']);
            $product->setAction('I');

            $em->persist($product);
            $em->flush();
            
            $message['response']['success'] = 'Product berhasil ditambahkan..';
        }
        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($message, 'json');
        $response = new Response($data, Response::HTTP_OK);

        return $response;
    }

    #[Route('/product/delete', name: 'delete_product', methods: ['POST'])]
    public function deleteProduct(ManagerRegistry $doctrine,Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $em = $doctrine->getManager();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();

        $findProductById = $em->getRepository(Product::class)
            ->findOneBy([
                'id' => $data['id'],
                'action' => ['U','I']
            ]);

        if(!$findProductById){
            $message['response']['failed'] = 'Product tidak tersedia.';
        } else {
            $findProductById->setAction('D');
            $em->persist($findProductById);
            $em->flush();
            $message['response']['success'] = 'Product telah dihapus.';
        }

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($message, 'json');
        $response = new Response($data, Response::HTTP_OK);

        return $response;
    }
}

