<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

use Throwable;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMException;

class UserController extends ApiController{
    #[Route('/api/register', name: 'create_User', methods: ['POST'])]
    public function addNewUser(ManagerRegistry $doctrine,Request $request,UserPasswordHasherInterface $passwordHasher): Response{

        $data = json_decode($request->getContent(), true);
        $em = $doctrine->getManager();
        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();
        
        $em->getConnection()->beginTransaction();
        try{
            if(!isset($data['email']) || !isset($data['password'])){
                return $this->respondValidationError("Invalid email or password !");
            }

            $findExistingUser = $em->getRepository(User::class)
                ->findOneBy([
                    'email' => $data['email']
                ]);
    
            if($findExistingUser){
                $message["response"]["failed"] = "Email telah digunakan !.";
            } else {
                $user = new User;
                $user->setEmail($data['email']);
                $plaintextPassword = $data['password'];
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword
                );
                $user->setUsername($data['username']);
                $user->setPassword($hashedPassword);

                if(!isset($data['roles'])){
                    $user->setRoles([
                        "ROLE_USER"
                    ]);
                }

                $em->persist($user);
                $em->flush();
                $em->getConnection()->commit();

                return $this->respondWithSuccess(sprintf('User %s successfully created', $user->getUsername()));
            }
        } catch (Exception $e) {
            $em->getConnection()->rollBack(); 
            throw $e;
        }

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data = $serializer->serialize($message, 'json');
        $response = new Response($data, 200);

        return $response;
    }

    #[Route('/api/login_check', name: 'login-check', methods: ['POST'])]
    public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        echo(['roles' => $user->getRoles()]);
        $response = ['token' => $JWTManager->create($user)];
        return new JsonResponse($response);
    }
}