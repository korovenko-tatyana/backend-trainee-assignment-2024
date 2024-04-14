<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Banner;
use App\Entity\SearchBanner;
use App\Repository\BannerRepository;
use App\Repository\SearchBannerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CreateBannerTest extends ApiTestCase
{
    private string $token;
    private string $adminToken;
    private string $content;
    private string $isActive;
    private array $tags;
    private int $feature;
    private int $id;
    public FilesystemAdapter $cache;
    private EntityManagerInterface $em;
    private SearchBannerRepository $searchBannerRepository;
    private BannerRepository $bannerRepository;

    public function setUp(): void
    {
        $test['json'] = ['email' => 'test@test.com', 'password' => '1234'];
        $response = static::createClient()->request('POST', '/login', $test, [], ['CONTENT_TYPE' => 'application/json']);
        $data = \json_decode($response->getContent(), true);
        $this->token = $data['token'];

        $test['json'] = ['email' => 'admin@test.com', 'password' => '12341234'];
        $response = static::createClient()->request('POST', '/login', $test);
        $data = \json_decode($response->getContent(), true);
        $this->adminToken = $data['token'];

        $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $this->searchBannerRepository = $this->em->getRepository(SearchBanner::class);
        $this->bannerRepository = $this->em->getRepository(Banner::class);

        $this->content = '{"test": "some_test", "text": "some_text", "url": "some_url"}';
        $this->tags = [1, 2];
        $this->feature = 1;
        $this->isActive = true;

        $banner = $this->create($this->tags, $this->feature, $this->isActive, $this->content);
        $this->id = $banner->getBanner()->getId();
        $this->cache = new FilesystemAdapter();
    }

    public function setDown(): void
    {
        $this->delete($this->id, $this->feature, $this->tags);
        $search1 = $this->tags[0].' '.$this->feature;
        $search2 = $this->tags[1].' '.$this->feature;
        $this->cache->delete($search1);
        $this->cache->delete($search2);
    }

    private function create(array $tags, int $feature, bool $isActive, string $content): object
    {
        $banner = new Banner();
        $banner->setContent($content)
            ->setIsActive($isActive)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($banner);

        foreach ($tags as $value) {
            $searchBanner = new SearchBanner();

            $searchBanner->setBanner($banner)
                ->setTagId($value)
                ->setFeatureId($feature);

            $this->em->persist($searchBanner);
        }

        $this->em->flush();

        $banner = $this->searchBannerRepository->findOneBy(['feature_id' => $feature, 'tag_id' => $tags[0]]);

        return $banner;
    }

    public function updateBanner(array $tags, int $feature, bool $isActive, string $content)
    {
        $this->searchBannerRepository->delete($this->feature, $this->tags);
        $banner = $this->bannerRepository->findOneBy(['id' => $this->id]);

        $banner->setContent($content)
            ->setIsActive($isActive)
            ->setUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($banner);

        foreach ($tags as $value) {
            $searchBanner = new SearchBanner();

            $searchBanner->setBanner($banner)
                ->setTagId($value)
                ->setFeatureId($feature);

            $this->em->persist($searchBanner);
        }

        $this->em->flush();
    }

    private function delete(int $id, int $feature, array $tags)
    {
        $this->searchBannerRepository->delete($feature, $tags);
        $this->bannerRepository->delete($id);
        $this->em->flush();
    }

    // успешная проверка для user-token
    public function testSuccessUserGet(): void
    {
        $response = static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');
        $arrayResponse = $response->toArray();

        $this->setDown();
        $this->assertResponseIsSuccessful();
        $this->assertSame($arrayResponse['content'], $this->content);
    }

    // успешная проверка для admin-token
    public function testSuccessAdminGet(): void
    {
        $response = static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->adminToken]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');
        $arrayResponse = $response->toArray();

        $this->setDown();

        $this->assertResponseIsSuccessful();
        $this->assertSame($arrayResponse['content'], $this->content);
    }

    // успешная проверка для необязательности параметра use_last_revision
    public function testSuccessWithoutUseLastRevisionGet(): void
    {
        $response = static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $arrayResponse = $response->toArray();
        $this->setDown();
        $this->assertResponseIsSuccessful();
        $this->assertSame($arrayResponse['content'], $this->content);
    }

    // неуспешный ответ для отсутсвия токена в хедере
    public function testFailedWithoutTokenGet(): void
    {
        static::createClient()
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');
        $this->setDown();
        $this->assertResponseStatusCodeSame(401);
    }

    // неуспешный ответ для неверного токена в хедере
    public function testFailedWithWrongTokenGet(): void
    {
        $tokenStr = $this->token;
        $tokenStr[0] = 'y';

        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$tokenStr]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');
        $this->setDown();
        $this->assertResponseStatusCodeSame(401);
    }

    // неуспешный ответ для неверного формата данных - тег
    public function testFailedWithWrongParamTagsGet(): void
    {
        $error = 'test';
        $response = static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->adminToken]])
            ->request('GET', "/user_banner?tag_id=$error&feature_id=1&use_last_revision=true");

        $arrayResponse = $response->toArray(false);
        $this->setDown();

        $this->assertResponseStatusCodeSame(400);
        $this->assertNotNull($arrayResponse['error']);
    }

    // неуспешный ответ для неверного формата данных - фича
    public function testFailedWithWrongParamFeatureGet(): void
    {
        $error = 'test';
        $response = static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->adminToken]])
            ->request('GET', "/user_banner?tag_id=1&feature_id=$error&use_last_revision=true");

        $arrayResponse = $response->toArray(false);
        $this->setDown();

        $this->assertResponseStatusCodeSame(400);
        $this->assertNotNull($arrayResponse['error']);
    }

    // неуспешный ответ для несуществующего баннера по введенным параметрам - не найдено по тегу
    public function testFailedWithNotFoundTagGet(): void
    {
        $notExistTag = '9';
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->adminToken]])
            ->request('GET', "/user_banner?tag_id=$notExistTag&feature_id=1&use_last_revision=true");
        $this->setDown();

        $this->assertResponseStatusCodeSame(404);
    }

    // неуспешный ответ для несуществующего баннера по введенным параметрам - не найдено по фиче
    public function testFailedWithNotFoundFeatureGet(): void
    {
        $notExistFeature = '9';
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->adminToken]])
            ->request('GET', "/user_banner?tag_id=1&feature_id=$notExistFeature&use_last_revision=true");
        $this->setDown();

        $this->assertResponseStatusCodeSame(404);
    }

    // успешная проверка кеша - было изменение в бд контента, но вернулся старый контент баннера (так как параметр use_last_revision не был передан)
    public function testSucccessWithCacheChangeContent(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $newContent = '{"new": "some_test", "text": "some_text", "url": "some_url"}';
        $this->updateBanner($this->tags, $this->feature, $this->isActive, $newContent);

        $response2 = static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $arrayResponse = $response2->toArray();
        $this->setDown();

        $this->assertResponseIsSuccessful();
        $this->assertSame($arrayResponse['content'], $this->content);
    }

    // успешная проверка без кеша - было изменение в бд контента, и вернулся новый контент баннера (так как параметр use_last_revision был передан)
    public function testSucccessWithoutCacheChangeContent(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $newContent = '{"new": "some_test", "text": "some_text", "url": "some_url"}';
        $this->updateBanner($this->tags, $this->feature, $this->isActive, $newContent);

        $response2 = static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');
        $arrayResponse = $response2->toArray();
        $this->setDown();

        $this->assertResponseIsSuccessful();
        $this->assertSame($arrayResponse['content'], $newContent);
    }

    // успешная проверка кеша - было изменение в бд тега, но баннер по старым параметрам все равно найден (так как параметр use_last_revision не был передан)
    public function testSucccessWithCacheChangeTags(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $newTags = [7, 8];
        $this->updateBanner($newTags, $this->feature, $this->isActive, $this->content);

        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');

        $this->setDown();

        $this->searchBannerRepository->delete($this->feature, $newTags);
        $this->em->flush();

        $search1 = $newTags[0].' '.$this->feature;
        $search2 = $newTags[1].' '.$this->feature;
        $this->cache->delete($search1);
        $this->cache->delete($search2);

        $this->assertResponseIsSuccessful();
    }

    // проверка кеша - было изменение в бд тега, и баннер по старым параметрам НЕ найден - так как запрос был не из кеша
    public function testFailedWithoutCacheChangeTags(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $newTags = [7, 8];
        $this->updateBanner($newTags, $this->feature, $this->isActive, $this->content);

        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');

        $this->setDown();

        $this->searchBannerRepository->delete($this->feature, $newTags);
        $this->em->flush();

        $search1 = $newTags[0].' '.$this->feature;
        $search2 = $newTags[1].' '.$this->feature;
        $this->cache->delete($search1);
        $this->cache->delete($search2);

        $this->assertResponseStatusCodeSame(404);
    }

    // успешная проверка кеша - было изменение в бд фичи, но баннер по старым параметрам все равно найден (так как параметр use_last_revision не был передан)
    public function testSucccessWithCacheChangeFeature(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $newFeature = 0;
        $this->updateBanner($this->tags, $newFeature, $this->isActive, $this->content);

        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');

        $this->setDown();

        $this->searchBannerRepository->delete($newFeature, $this->tags);
        $this->em->flush();

        $search1 = $this->tags[0].' '.$newFeature;
        $search2 = $this->tags[1].' '.$newFeature;
        $this->cache->delete($search1);
        $this->cache->delete($search2);

        $this->assertResponseIsSuccessful();
    }

    // проверка кеша - было изменение в бд фичи, и баннер по старым параметрам НЕ найден - так как запрос был не из кеша
    public function testFailedWithoutCacheChangeFeature(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $newFeature = 0;
        $this->updateBanner($this->tags, $newFeature, $this->isActive, $this->content);

        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');

        $this->setDown();

        $this->searchBannerRepository->delete($newFeature, $this->tags);
        $this->em->flush();

        $search1 = $this->tags[0].' '.$newFeature;
        $search2 = $this->tags[1].' '.$newFeature;
        $this->cache->delete($search1);
        $this->cache->delete($search2);

        $this->assertResponseStatusCodeSame(404);
    }

    // успешная проверка кеша - было изменение в бд параметра active, но баннер для токена user-token по все равно найден (так как параметр use_last_revision не был передан)
    public function testSucccessWithCacheChangeActive(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $this->updateBanner($this->tags, $this->feature, false, $this->content);

        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');

        $this->setDown();

        $this->assertResponseIsSuccessful();
    }

    // проверка логики, что только админ может получить отключенный баннер
    public function testFailedWithoutCacheChangeActive(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $this->updateBanner($this->tags, $this->feature, false, $this->content);

        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');

        $this->setDown();

        $this->assertResponseStatusCodeSame(404);
    }

    // проверка логики, что только админ может получить отключенный баннер - успешная для админа
    public function testSuccessWithoutCacheChangeActive(): void
    {
        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->adminToken]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1');
        $this->updateBanner($this->tags, $this->feature, false, $this->content);

        static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->adminToken]])
            ->request('GET', '/user_banner?tag_id=1&feature_id=1&use_last_revision=true');

        $this->setDown();

        $this->assertResponseIsSuccessful();
    }
}
