<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Exception;
use App\Entity\User;
use App\Utils\CryptUtils;
use OpenApi\Annotations\Tag;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 *@Route("/api/public/login" , name="api_public_login")
 */
class LoginController extends AbstractController
{
    /**
     * @Route("" , name="login",methods={"POST"})
     * @Tag(name="Login")
     * @OA\RequestBody(
     *     @Model(type=User::class,groups={"login"}),
     *     description="login"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @ParamConverter(
     *     "request",
     *     converter="fos_rest.request_body",
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request, EntityManagerInterface $em, UserRepository $userRepository, JWTTokenManagerInterface $jwtManager, AuthenticationUtils $authenticationUtils,  UserPasswordHasherInterface $passwordHasher)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        return $this->json(['message' => 'Email / Mot de passe incorrect', 'error' => $error], Response::HTTP_UNAUTHORIZED);
    }
}
