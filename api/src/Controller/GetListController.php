<?php

namespace App\Controller;

use App\Service\BannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetListController extends AbstractController
{
    public function __construct(protected BannerService $bannerService)
    {
    }

    #[Route('/banner', name: 'get_list_banners', methods: 'GET')]
    public function index(Request $request): JsonResponse|Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new Response(status: 403);
        }

        try {
            $banners = $this->bannerService->getList($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse($banners, 200);
    }
}
