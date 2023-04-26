<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Utils\CryptUtils;
use OpenApi\Annotations\Tag;
use OpenApi\Annotations as OA;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use PhpParser\Node\Expr\New_;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/users", name="app_users")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/all" , name="list",methods={"GET"})
     * @Tag(name="Users")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function index(UserRepository $repository): Response
    {
        $list = $repository->findAll();
        foreach ($list as $user) {
            $user["id"] = CryptUtils::cryptId($user["id"]);
        }
        return $this->json(['list' => $list], 200, [], ['groups' => ['users', 'id']]);
    }

    /**
     * @Route("/create" , name="create",methods={"POST"})
     * @Tag(name="Users")
     * @OA\RequestBody(
     *     @Model(type=User::class,groups={"users"}),
     *     description="fields"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @ParamConverter(
     *     "user",
     *     converter="fos_rest.request_body",
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function create(User $user, UserRepository $repository, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        //$existant = $repository->findOneBy(["email" => $user->getEmail()]);
        // if ($existant > 0) {
        //     throw new Exception('Cet email est déja utilisé.');
        // };
       /*  $entity = new User();
        $entity = $user; */
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        );
        $user->setPassword($hashedPassword);
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            throw new Exception($errors[0]->getMessage());
        }
        $repository->save($user, true);
        return $this->json(['entity' => $user], 200, [], ['groups' => ['users', 'id']]);
    }

    /**
     * @Route("/read/{id}" , name="read",methods={"GET"})
     * @Tag(name="Users")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="path"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function read($id, UserRepository $repository): Response
    {
        $id = CryptUtils::decryptId($id);
        $user = $repository->findOneBy(["id" => $id]);
        $user["id"] = CryptUtils::cryptId($user["id"]);

        /* $count = $repository->countByQueryParam($queryParameter, $this->getUser()); */
        return $this->json(['user' => $user], 200, [], ['groups' => ['users', 'id']]);
    }

    /**
     * @Route("/update/{id}" , name="update",methods={"PUT"})
     * @Tag(name="Users")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="path"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function update($id, User $user, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $entity = $repository->findOneBy(["id" => $id]);
        $entity = $user;
        $em->persist($entity);
        $em->flush();

        /* $count = $repository->countByQueryParam($queryParameter, $this->getUser()); */
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['users', 'id']]);
    }

    /**
     * @Route("/delete/{id}" , name="delete",methods={"DELETE"})
     * @Tag(name="Users")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function delete($id, UserRepository $repository, EntityManagerInterface $em): Response
    {
        $id = CryptUtils::decryptId($id);
        $entity = $repository->findOneBy(["id" => $id]);
        $em->remove($entity);
        $em->flush();
        return $this->json(['entity ' . $id . ' deleted'], 200, []);
    }
}
