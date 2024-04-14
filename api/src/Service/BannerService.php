<?php

namespace App\Service;

use App\DTO\CreateBannerDTO;
use App\DTO\GetBannerDTO;
use App\DTO\GetListDTO;
use App\DTO\UpdateBannerDTO;
use App\Entity\Banner;
use App\Entity\SearchBanner;
use App\Exception\BadDataException;
use App\Repository\BannerRepository;
use App\Repository\FeatureRepository;
use App\Repository\SearchBannerRepository;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Message\DeleteNotification;

class BannerService
{
    public FilesystemAdapter $cache;

    public function __construct(
        protected EntityManagerInterface $em,
        protected SerializerInterface $serializer,
        protected BannerRepository $bannerRepository,
        protected SearchBannerRepository $searchBannerRepository,
        protected TagRepository $tagRepository,
        protected FeatureRepository $featureRepository,
        protected ValidatorInterface $validator,
    ) {
        $this->cache = new FilesystemAdapter();
    }

    public function add(Request $request)
    {
        /** @var CreateBannerDTO $bannerDTO */
        $bannerDTO = $this->validateDTO($request->getContent(), CreateBannerDTO::class);

        $this->transactionCreate($bannerDTO);

        $bannerInfo = $this->get($bannerDTO->getFeatureId(), $bannerDTO->getTagIds()[0]);

        return $bannerInfo->getBanner()->getId();
    }

    public function update(Request $request, int $id)
    {
        /** @var UpdateBannerDTO $bannerDTO */
        $bannerDTO = $this->validateDTO($request->getContent(), UpdateBannerDTO::class);

        /** @var Banner $findBanner */
        $findBanner = $this->getById($id);

        if (!$findBanner) {
            throw new NotFoundHttpException("Не найден баннер с id = $id");
        }

        $this->transactionUpdate($bannerDTO, $findBanner);
    }

    public function getBanner(Request $request, bool $isAdmin)
    {
        /** @var GetBannerDTO $getBannerDTO */
        $getBannerDTO = $this->validateDTO(json_encode($request->query->all()), GetBannerDTO::class);

        $tagId = $getBannerDTO->getTagId();
        $featureId = $getBannerDTO->getFeatureId();
        $useLastRevision = $getBannerDTO->getUseLastRevision();

        $content = $this->getContent($useLastRevision, $tagId, $featureId);

        if ($content) {
            return ($content['isActive'] || !$content['isActive'] && $isAdmin)
                ? $content['content']
                : throw new NotFoundHttpException('По введенным параметрам баннер не найден');
        }

        $banner = $this->get($featureId, $tagId);

        if (!$banner || !$banner->getBanner()->getIsActive() && !$isAdmin) {
            throw new NotFoundHttpException('По введенным параметрам баннер не найден');
        }

        $content = $banner->getBanner()->getContent();

        $this->setCache($content, $banner->getBanner()->getIsActive(), $tagId, $featureId);

        return $content;
    }

    public function getList(Request $request)
    {
        /** @var GetListDTO $getBannerDTO */
        $getBannerDTO = $this->validateDTO(json_encode($request->query->all()), GetListDTO::class);

        $result = $this->bannerRepository->search($getBannerDTO);

        return $this->presenter($result);
    }

    public function delete(int $id)
    {
        /** @var Banner $findBanner */
        $findBanner = $this->getById($id);

        if (!$findBanner) {
            throw new NotFoundHttpException("Не найден баннер с id = $id");
        }

        $this->transactionDelete($findBanner);
    }

    public function deleteMany(int $feature_id, MessageBusInterface $bus)
    {
        $bus->dispatch(new DeleteNotification("$feature_id"));
    }

    private function getContent(bool $useLastRevision, int $tagId, int $featureId)
    {
        if ($useLastRevision) {
            return null;
        }

        $cacheItem = $this->cacheItem($tagId, $featureId);

        if (!$cacheItem->isHit()) {
            return null;
        }

        return $cacheItem->get();
    }

    private function presenter(array $banners)
    {
        $arrResult = [];

        foreach ($banners as $value) {
            $banner = [
                'banner_id' => $value->getId(),
                'tag_ids' => $this->getTagsFromBanner($value->getSearchBanners()),
                'feature_id' => $value->getSearchBanners()[0]->getFeatureId(),
                'content' => $value->getContent(),
                'is_active' => $value->getIsActive(),
                'created_at' => $value->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $value->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];

            array_push($arrResult, $banner);
        }

        return $arrResult;
    }

    private function setCache(string $content, bool $isActive, int $tagId, int $featureId)
    {
        $cacheItem = $this->cacheItem($tagId, $featureId)
            ->set(['content' => $content, 'isActive' => $isActive])
            ->expiresAfter(60 * 5);

        $this->cache->save($cacheItem);
    }

    private function cacheItem(int $tagId, int $featureId)
    {
        $search = $tagId.' '.$featureId;

        return $this->cache->getItem($search);
    }

    private function get(int $featureId, int $tagId)
    {
        return $this->searchBannerRepository->findOneBy(['feature_id' => $featureId, 'tag_id' => $tagId]);
    }

    private function getById(int $bannerId)
    {
        return $this->bannerRepository->findOneBy(['id' => $bannerId]);
    }

    private function transactionCreate(CreateBannerDTO $bannerDTO)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $banner = new Banner();
            $banner->setContent($bannerDTO->getContent())
                ->setIsActive($bannerDTO->getIsActive())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->em->persist($banner);

            $tags = $this->tagRepository->findManyTags($bannerDTO->getTagIds());
            if (!$tags || count($tags) !== count($bannerDTO->getTagIds())) {
                throw new BadDataException('Переданы id несуществующих тегов');
            }

            $feature = $this->featureRepository->findOneBy(['id' => $bannerDTO->getFeatureId()]);
            if (!$feature) {
                throw new BadDataException('Передан id несуществующей фичи');
            }

            foreach ($tags as $value) {
                $searchBanner = new SearchBanner();

                $searchBanner->setBanner($banner)
                    ->setTagId($value->getId())
                    ->setFeatureId($feature->getId());

                $this->em->persist($searchBanner);
                $this->em->flush();
            }

            $this->em->getConnection()->commit();
        } catch (UniqueConstraintViolationException $e) {
            $this->em->getConnection()->rollBack();
            throw new BadDataException("Нарушение условия 'Фича и тег однозначно определяют баннер' - баннер по набору фича + тег уже существует");
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    private function transactionUpdate(UpdateBannerDTO $bannerDTO, Banner $findBanner)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $findBanner->setContent($bannerDTO->getContent() ?? $findBanner->getContent())
                ->setIsActive($bannerDTO->getIsActive() ?? $findBanner->getIsActive())
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->em->persist($findBanner);

            $featureId = $bannerDTO->getFeatureId() ?? $findBanner->getSearchBanners()[0]->getFeatureId();
            $tagIds = $bannerDTO->getTagIds() ?? $this->getTagsFromBanner($findBanner->getSearchBanners());

            if ($bannerDTO->getTagIds()) {
                $tags = $this->tagRepository->findManyTags($bannerDTO->getTagIds());

                if (!$tags || count($tags) !== count($bannerDTO->getTagIds())) {
                    throw new BadDataException('Переданы id несуществующих тегов');
                }
            }

            if ($bannerDTO->getFeatureId() && $bannerDTO->getFeatureId() !== $findBanner->getSearchBanners()[0]->getFeatureId()) {
                $feature = $this->featureRepository->findOneBy(['id' => $bannerDTO->getFeatureId()]);

                if (!$feature) {
                    throw new BadDataException('Передан id несуществующей фичи');
                }
            }

            // Delete old rows in search_banner table
            $this->searchBannerRepository->delete($findBanner->getSearchBanners()[0]->getFeatureId(), $this->getTagsFromBanner($findBanner->getSearchBanners()));

            // Create new rows in search_banner table
            foreach ($tagIds as $value) {
                $searchBanner = new SearchBanner();

                $searchBanner->setBanner($findBanner)
                    ->setTagId($value)
                    ->setFeatureId($featureId);

                $this->em->persist($searchBanner);
                $this->em->flush();
            }

            $this->em->getConnection()->commit();
        } catch (UniqueConstraintViolationException $e) {
            $this->em->getConnection()->rollBack();
            throw new BadDataException("Нарушение условия 'Фича и тег однозначно определяют баннер' - баннер по набору фича + тег уже существует");
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    private function transactionDelete(Banner $findBanner)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $this->searchBannerRepository->delete($findBanner->getSearchBanners()[0]->getFeatureId(), $this->getTagsFromBanner($findBanner->getSearchBanners()));
            $this->em->remove($findBanner);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    private function getTagsFromBanner(Collection $arrTags)
    {
        $tags = [];

        foreach ($arrTags as $value) {
            array_push($tags, $value->getTagId());
        }

        return $tags;
    }

    private function validateDTO(string $content, string $nameOfClass): object
    {
        $DTO = $this->serializer->deserialize($content, $nameOfClass, 'json');

        $this->checkErrors($DTO);

        return $DTO;
    }

    private function checkErrors(object $DTO): void
    {
        $errors = $this->validator->validate($DTO);

        if ($errors->count() > 0) {
            $errorMessage = [];

            foreach ($errors as $error) {
                $errorMessage[] = sprintf('%s : %s', $error->getPropertyPath(), $error->getMessage());
            }

            throw new BadDataException(json_encode($errorMessage));
        }
    }
}
