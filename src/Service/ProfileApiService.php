<?php

namespace App\Service;

use OpenAPI\Server\Api\ProfileApiInterface;
use OpenAPI\Server\Model\ProfileResponse;
use OpenAPI\Server\Model\Profile as OpenAPIProfile;
use OpenAPI\Server\Model\Request as OpenAPIRequest;
use App\Entity\User;
use App\Entity\Profile as DbProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProfileApiService implements ProfileApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly ParameterBagInterface $parameterBag,
        private readonly RequestStack $requestStack
    ) {}

    public function setbearerAuth(?string $value): void
    {
        $this->bearerToken = $value ?? '';
    }

    public function profileGet(int &$responseCode, array &$responseHeaders): array|object|null
    {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $responseCode = 401;
            return null;
        }

        $dbProfile = $currentUser->getProfile();
        $openApiProfile = new OpenAPIProfile([
            'id' => $currentUser->getId(),
            'firstName' => $dbProfile ? $dbProfile->getFirstName() : '',
            'lastName' => $dbProfile ? $dbProfile->getLastName() : '',
            'phoneNumber' => $dbProfile ? $dbProfile->getPhoneNumber() : '',
            'avatarUrl' => $dbProfile ? $dbProfile->getAvatarUrl() : null,
            'email' => $currentUser->getEmail(),
            'isAdmin' => $currentUser->getRole()?->getName() === 'ROLE_ADMIN',
            'activeRequestsCount' => $currentUser->getRequests()->filter(fn($r) => $r->getStatus() !== 'Решена')->count()
        ]);

        $openApiRequests = [];
        foreach ($currentUser->getRequests() as $dbRequest) {
            $openApiRequests[] = new OpenAPIRequest([
                'id' => $dbRequest->getId(),
                'carName' => $dbRequest->getCarName(),
                'lot' => null,
                'callTime' => $dbRequest->getCallTime()?->format('Y-m-d H:i:s'),
                'comment' => $dbRequest->getComment(),
                'isSolved' => $dbRequest->getStatus() === 'Решена',
                'createdAt' => $dbRequest->getCreatedAt() ? \DateTime::createFromImmutable($dbRequest->getCreatedAt()) : null
            ]);
        }

        $responseCode = 200;
        return new ProfileResponse(['user' => $openApiProfile, 'requests' => $openApiRequests]);
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
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $responseCode = 401;
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();

        $fName = $request->request->get('first_name') ?? $first_name;
        $lName = $request->request->get('last_name') ?? $last_name;
        $mail  = $request->request->get('email') ?? $email;
        $phone = $request->request->get('phone_number') ?? $phone_number;
        $file  = $request->files->get('avatar') ?? $avatar;

        $dbProfile = $currentUser->getProfile();
        if (!$dbProfile) {
            $dbProfile = new DbProfile();
            $dbProfile->setAccount($currentUser);
            $this->entityManager->persist($dbProfile);
        }

        if ($mail) {
            $currentUser->setEmail($mail);
        }

        if ($fName !== null) $dbProfile->setFirstName($fName);
        if ($lName !== null) $dbProfile->setLastName($lName);
        if ($phone !== null) $dbProfile->setPhoneNumber($phone);

        if ($file instanceof UploadedFile) {
            $uploadsDirectory = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/avatars';
            $newFilename = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($uploadsDirectory, $newFilename);
                $dbProfile->setAvatarUrl('/uploads/avatars/' . $newFilename);
            } catch (\Exception $e) {
                $responseCode = 500;
                return null;
            }
        }

        $this->entityManager->flush();

        $responseCode = 200;
        return new OpenAPIProfile([
            'id' => $currentUser->getId(),
            'firstName' => $dbProfile->getFirstName(),
            'lastName' => $dbProfile->getLastName(),
            'phoneNumber' => $dbProfile->getPhoneNumber(),
            'avatarUrl' => $dbProfile->getAvatarUrl(),
            'email' => $currentUser->getEmail(),
            'isAdmin' => $currentUser->getRole()?->getName() === 'ROLE_ADMIN',
            'activeRequestsCount' => $currentUser->getRequests()->count()
        ]);
    }
}
