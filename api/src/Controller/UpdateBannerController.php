<?php

namespace App\Controller;

use App\Exception\BadDataException;
use App\Service\BannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class UpdateBannerController extends AbstractController
{
    public function __construct(protected BannerService $bannerService)
    {
    }

    #[Route('/banner/{id}', name: 'update_banner', methods: 'PATCH', requirements: ['id' => '\d+'])]
    public function index(Request $request, int $id): JsonResponse|Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new Response(status: 403);
        }

        try {
            $this->bannerService->update($request, $id);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (BadDataException|NotEncodableValueException|NotNormalizableValueException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse();
    }
}
