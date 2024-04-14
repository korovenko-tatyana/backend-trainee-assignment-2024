<?php

namespace App\Controller;

use App\Service\BannerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;

class DeleteManyBannersController extends AbstractController
{
    public function __construct(protected BannerService $bannerService)
    {
    }

    #[Route('/banners/{feature_id}', name: 'delete_many_banners', methods: 'DELETE', requirements: ['feature_id' => '\d+'])]
    public function index(int $feature_id, MessageBusInterface $bus): JsonResponse|Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new Response(status: 403);
        }

        try {
            $this->bannerService->deleteMany($feature_id, $bus);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(status: 204);
    }
}
