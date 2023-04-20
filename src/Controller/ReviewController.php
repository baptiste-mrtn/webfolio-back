<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\CryptUtils;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\PropertyAccess\PropertyAccess;
/**
 * @Route("/api/reviews", name="app_reviews")
 */
class ReviewController extends AbstractController
{
    /**
     * @Route("/all" , name="list",methods={"GET"})
     * @Tag(name="Reviews")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function index(ReviewRepository $repository): Response
    {
        $list = $repository->findAll();
        foreach ($list as $review) {
            $review->cryptId($review->getId());
        }
        return $this->json(['list' => $list], 200, [], ['groups' => ['idcrypt', 'review']]);
    }

    /**
     * @Route("/create" , name="create",methods={"POST"})
     * @Tag(name="Reviews")
     * @OA\RequestBody(
     *     @Model(type=Review::class,groups={"review"}),
     *     description="fields"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @ParamConverter(
     *     "review",
     *     converter="fos_rest.request_body",
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function create(Review $review, ReviewRepository $repository, EntityManagerInterface $em, Request $request): Response
    {
        $entity = new Review();
        $entity = $review;
        $em->persist($entity);
        $em->flush();
        $review->cryptId($review->getId());
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['idcrypt', 'review']]);
    }

    /**
     * @Route("/read/{id}" , name="read",methods={"GET"})
     * @Tag(name="Reviews")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function read($id, ReviewRepository $repository): Response
    {
        $id = CryptUtils::decryptId($id);
        $review = $repository->findOneBy(["id" => $id]);
        $review->cryptId($id);
        return $this->json(['entity' => $review], 200, [], ['groups' => ['idcrypt','review']]);
    }

    /**
     * @Route("/update/{id}" , name="update",methods={"PUT"})
     * @Tag(name="Reviews")
     * @OA\RequestBody(
     *     @Model(type=Review::class,groups={"review"}),
     *     description="fields"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @ParamConverter(
     *     "review",
     *     converter="fos_rest.request_body",
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function update($id, Review $review, ReviewRepository $repository, EntityManagerInterface $em): Response
    {
        $id = CryptUtils::decryptId($id);
        $today = new DateTimeImmutable();
        $entity = $repository->findOneBy(["id" => $id]);
        if($entity){
            $entity->setTitle($review->getTitle());
            $entity->setRate($review->getRate());
            $entity->setCreatedAt($today);
            $entity->setComment($review->getComment());
            $em->persist($entity);
            $em->flush();
            $entity->cryptId($id);
        } else {
            return $this->json(['error'=> 'No entity found with given id']);
        }
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['idcrypt', 'review']]);
    }

    /**
     * @Route("/delete/{id}" , name="delete",methods={"DELETE"})
     * @Tag(name="Reviews")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function delete($id, ReviewRepository $repository, EntityManagerInterface $em): Response
    {
        $id = CryptUtils::decryptId($id);
        $entity = $repository->findOneBy(["id" => $id]);
        $em->remove($entity);
        $em->flush();
        return $this->json(['entity ' . $id . ' deleted'], 200, []);
    }
}
