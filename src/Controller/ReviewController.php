<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\GalleryRepository;
use App\Repository\ReviewRepository;
use App\Repository\SiteRepository;
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
     * @param Request $request
     * @return JsonResponse
     */

    // ParamConverter("review",converter="fos_rest.request_body")

    public function create(EntityManagerInterface $em, Request $request, SiteRepository $siteRepository, GalleryRepository $galleryRepository): Response
    {
        $values = json_decode($request->getContent(), true);
        $review = new Review();
        $user = $this->getUser();
        $today = new DateTimeImmutable();

        if (!empty($values['site'])) {
            $site = $values['site'];
            $idSite = CryptUtils::decryptId($site);
            $site = $siteRepository->find($idSite);
            $review->setSite($site);
        }

        if (!empty($values['gallery'])) {
            $gallery = $values['gallery'];
            $idSite = CryptUtils::decryptId($gallery);
            $gallery = $galleryRepository->find($idSite);
            $review->setGallery($gallery);
        }

        $review->setAuthor($user)
        ->setCreatedAt($today)
        ->setRate($values["rate"])
        ->setTitle($values["title"])
        ->setComment($values["comment"]);

        $em->persist($review);
        $em->flush();
        $review->cryptId($review->getId());
        return $this->json(['entity' => $review], 200, [], ['groups' => ['idcrypt', 'review']]);
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
        return $this->json(['entity' => $review], 200, [], ['groups' => ['idcrypt', 'review']]);
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
        $entity = $repository->findOneBy(["id" => $id]);
        if ($entity) {
            $entity->setTitle($review->getTitle());
            $entity->setRate($review->getRate());
            $entity->setComment($review->getComment());
            $em->persist($entity);
            $em->flush();
            $entity->cryptId($id);
        } else {
            return $this->json(['error' => 'No entity found with given id']);
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
        $user = $this->getUser();
        $entity = $repository->findOneBy(["id" => $id]);
        if(in_array("ROLE_ADMIN", $user->getRoles()) || $entity->getAuthor() === $user){
            $em->remove($entity);
            $em->flush();
            return $this->json(['entity ' . $id . ' deleted'], 200, []);
        }
        return $this->json(['error', 'You are not the author or admin'], 400, []);
    }

        /**
     * @Route("/myreviews" , name="myreviews",methods={"GET"})
     * @Tag(name="Reviews")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function myReviews(ReviewRepository $repository){
        $user = $this->getUser();
        $list = $repository->findBy(["author" => $user]);
        foreach ($list as $review) {
            $review->cryptId($review->getId());
            if($review->getSite() != null){
                $review->cryptId($review->getSite()->getId());
            } else {
                $review->getGallery()->getIdCrypt();
            }
        }
        return $this->json(['list' => $list], 200, [], ['groups' => ['idcrypt', 'review', 'my-review']]);
    }
}
