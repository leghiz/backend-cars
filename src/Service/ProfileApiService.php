<?php

namespace App\Service;

use OpenAPI\Server\Api\ProfileApiInterface;
use OpenAPI\Server\Model\ProfileResponse;
use OpenAPI\Server\Model\Profile as OpenAPIProfile;
use OpenAPI\Server\Model\Request as OpenAPIRequest;
use OpenAPI\Server\Model\RequestLot;
use App\Entity\User;
use App\Entity\Profile as DbProfile;
use App\Entity\Review as DbReview;
use App\Entity\Lot; // Сущность Lot уже импортирована
// use App\Entity\CarModel; // Больше не требуется
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class ProfileApiService implements ProfileApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly ParameterBagInterface $parameterBag,
        private readonly RequestStack $requestStack,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {}

    public function setbearerAuth(?string $value): void
    {
        $this->bearerToken = $value ?? '';
    }

    public function profileGet(int &$responseCode, array &$responseHeaders): array|object|null
    {
        $user = $this->security->getUser();
        if (!$user) {
            $responseCode = 401;
            return null;
        }

        $profile = $user->getProfile();
        $openApiProfile = new OpenAPIProfile([
            'id' => $user->getId(),
            'firstName' => $profile?->getFirstName() ?? '',
            'lastName' => $profile?->getLastName() ?? '',
            'phoneNumber' => $profile?->getPhoneNumber() ?? '',
            'avatarUrl' => $profile?->getAvatarUrl(),
            'email' => $user->getEmail(),
            'isAdmin' => $user->getRole()?->getName() === 'ROLE_ADMIN',
            'activeRequestsCount' => $user->getRequests()->filter(fn($r) => $r->getStatus() !== 'Решена')->count()
        ]);

        $requests = [];
        foreach ($user->getRequests() as $req) {
            $lotShort = null;

            if ($req->getLot()) {
                $lot = $req->getLot();

                $existingReview = $this->entityManager->getRepository(DbReview::class)->findOneBy([
                    'lot' => $lot->getId()
                ]);

                if (!$existingReview) {
                    $productionYear = $lot->getModification()?->getProductionYear();

                    if ($productionYear instanceof \DateTimeInterface) {
                        $yearValue = (int)$productionYear->format('Y');
                    }
                    $modelName = method_exists($lot, 'getCarModel') && $lot->getCarModel()
                        ? $lot->getCarModel()->getName()
                        : $req->getCarName();

                    $lotShort = new RequestLot([
                        'id' => $lot->getId(),
                        'manufacturer' => $lot->getModification()->getModel()->getManufacturer()->getName(),
                        'model' => $modelName,
                        'year' => $yearValue,
                        'bodyNumber' => $lot->getBodyNumber(),
                    ]);
                }
            }

            $requests[] = new OpenAPIRequest([
                'id' => $req->getId(),
                'carName' => $req->getCarName(),
                'lot' => $lotShort,
                'callTime' => $req->getCallTime()?->format('Y-m-d H:i:s'),
                'comment' => $req->getComment(),
                'isSolved' => $req->getStatus() === 'Решена',
                'createdAt' => $req->getCreatedAt() ? \DateTime::createFromImmutable($req->getCreatedAt()) : null
            ]);
        }

        $responseCode = 200;
        return new ProfileResponse(['user' => $openApiProfile, 'requests' => $requests]);
    }

    public function profilePost(
        ?string $first_name = null,
        ?string $last_name = null,
        ?string $email = null,
        ?string $phone_number = null,
        ?UploadedFile $avatar = null,
        int &$responseCode = 0,
        array &$responseHeaders = []
    ): array|object|null {
        $user = $this->security->getUser();
        if (!$user) {
            $responseCode = 401;
            return null;
        }

        $req = $this->requestStack->getCurrentRequest();
        $fName = $req->request->get('first_name') ?? $first_name;
        $lName = $req->request->get('last_name') ?? $last_name;
        $mail = $req->request->get('email') ?? $email;
        $phone = $req->request->get('phone_number') ?? $phone_number;
        $file = $req->files->get('avatar') ?? $avatar;

        $profile = $user->getProfile() ?: new DbProfile();
        if (!$user->getProfile()) {
            $profile->setAccount($user);
            $this->entityManager->persist($profile);
        }

        $oldEmail = $user->getEmail();
        if ($mail) $user->setEmail($mail);
        if ($fName !== null) $profile->setFirstName($fName);
        if ($lName !== null) $profile->setLastName($lName);
        if ($phone !== null) $profile->setPhoneNumber($phone);

        if ($file instanceof UploadedFile) {
            $dir = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/avatars';
            $filename = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($dir, $filename);
                $profile->setAvatarUrl('/uploads/avatars/' . $filename);
            } catch (\Exception) {
                $responseCode = 500;
                return null;
            }
        }

        $this->entityManager->flush();

        if ($mail && $mail !== $oldEmail) {
            $responseHeaders['X-New-Auth-Token'] = $this->jwtManager->create($user);
        }

        $responseCode = 200;
        return new OpenAPIProfile([
            'id' => $user->getId(),
            'firstName' => $profile->getFirstName(),
            'lastName' => $profile->getLastName(),
            'phoneNumber' => $profile->getPhoneNumber(),
            'avatarUrl' => $profile->getAvatarUrl(),
            'email' => $user->getEmail(),
            'isAdmin' => $user->getRole()?->getName() === 'ROLE_ADMIN',
            'activeRequestsCount' => $user->getRequests()->count()
        ]);
    }
}