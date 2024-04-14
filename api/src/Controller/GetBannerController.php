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
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class GetBannerController extends AbstractController
{
    public function __construct(protected BannerService $bannerService)
    {
    }

    #[Route('/user_banner', name: 'get_user_banner', methods: 'GET')]
    public function index(Request $request): JsonResponse|Response
    {
        try {
            $content = $this->bannerService->getBanner($request, $this->isGranted('ROLE_ADMIN'));
        } catch (NotFoundHttpException $e) {
            return new Response(status: 404);
        } catch (BadDataException|NotNormalizableValueException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(['content' => $content], 200);
    }
}
