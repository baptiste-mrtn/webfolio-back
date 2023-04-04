<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Utils\CryptUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Tag;

/**
 * @Route("/api/sites", name="app_sites")
 */
class SiteController extends AbstractController
{
    /**
     * @Route("/all" , name="list",methods={"POST"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function index(SiteRepository $repository): Response
    {
        $list = $repository->findAll();
        foreach ($list as $site) {
            $site["id"] = CryptUtils::cryptId($site["id"]);
        }
        return $this->json(['list' => $list], 200, [], ['groups' => ['id', 'site']]);
    }

    /**
     * @Route("/create" , name="create",methods={"POST"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function create(Site $site, SiteRepository $repository, EntityManagerInterface $em): Response
    {
        $entity = new Site();
        $entity = $site;
        $em->persist($entity);
        $em->flush();
        $entity['id'] = CryptUtils::cryptId($entity["id"]);
        /* $count = $repository->countByQueryParam($queryParameter, $this->getUser()); */
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['id', 'site']]);
    }

    /**
     * @Route("/read/{id}" , name="read",methods={"GET"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function read($id, SiteRepository $repository): Response
    {
        $id = CryptUtils::decryptId($id);
        $site = $repository->findOneBy(["id" => $id]);
        $site["id"] = CryptUtils::cryptId($site["id"]);

        /* $count = $repository->countByQueryParam($queryParameter, $this->getUser()); */
        return $this->json(['site' => $site], 200, [], ['groups' => ['', 'id']]);
    }

    /**
     * @Route("/update/{id}" , name="update",methods={"PUT"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function update($id, Site $site, SiteRepository $repository, EntityManagerInterface $em): Response
    {
        $id = CryptUtils::decryptId($id);
        $entity = $repository->findOneBy(["id" => $id]);
        $entity = $site;
        $em->persist($entity);
        $em->flush();
        /* $count = $repository->countByQueryParam($queryParameter, $this->getUser()); */
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['', 'id']]);
    }

    /**
     * @Route("/delete/{id}" , name="delete",methods={"DELETE"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function delete($id, SiteRepository $repository, EntityManagerInterface $em): Response
    {
        $id = CryptUtils::decryptId($id);
        $entity = $repository->findOneBy(["id" => $id]);
        $em->remove($entity);
        $em->flush();
        return $this->json(['entity ' . $id . ' deleted'], 200, []);
    }
}
