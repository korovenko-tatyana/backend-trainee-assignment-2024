<?php

namespace App\Controller;

use App\Service\BannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DeleteBannerController extends AbstractController
{
    public function __construct(protected BannerService $bannerService)
    {
    }

    #[Route('/banner/{id}', name: 'delete_banner', methods: 'DELETE', requirements: ['id' => '\d+'])]
    public function index(int $id): JsonResponse|Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new Response(status: 403);
        }

        try {
            $this->bannerService->delete($id);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(status: 204);
    }
}
