<?php

namespace App\Controller;

use App\Exception\BadDataException;
use App\Service\BannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class CreateBannerController extends AbstractController
{
    public function __construct(protected BannerService $bannerService)
    {
    }

    #[Route('/banner', name: 'create_banner', methods: 'POST')]
    public function index(Request $request): JsonResponse|Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new Response(status: 403);
        }

        try {
            $bannerId = $this->bannerService->add($request);
        } catch (BadDataException|NotEncodableValueException|NotNormalizableValueException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(['banner_id' => $bannerId], 201);
    }
}
