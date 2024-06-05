<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Enum\AuthProviderEnum;
use App\Enum\EntityStatusEnum;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\User;

class UserFixtures extends Fixture
{
    public const USER_ID_PREFIX = 'user';
    public const USER_ID_1 = 1;
    public const USER_ID_2 = 2;
    public const USER_ID_3 = 3;
    public const USER_ID_4 = 4;
    public const USER_ID_5 = 5;
    public const USER_ID_6 = 6;

    public const USERS = [
        [
            'id' => self::USER_ID_1,
            'email' => 'Admin@local',
            'auth_provider' => AuthProviderEnum::OPEN_ID,
            'auth_uid' => '1',
            'status' => EntityStatusEnum::ENABLED,
        ],
        [
            'id' => self::USER_ID_2,
            'email' => 'user2@email.com',
            'auth_provider' => AuthProviderEnum::OPEN_ID,
            'auth_uid' => '1',
            'status' => EntityStatusEnum::ENABLED,
        ],
        [
            'id' => self::USER_ID_3,
            'email' => 'user3@email.com',
            'auth_provider' => AuthProviderEnum::OPEN_ID,
            'auth_uid' => '1',
            'status' => EntityStatusEnum::ENABLED,
        ],
        [
            'id' => self::USER_ID_4,
            'email' => 'user4@email.com',
            'auth_provider' => AuthProviderEnum::OPEN_ID,
            'auth_uid' => '1',
            'status' => EntityStatusEnum::ENABLED,
        ],
        [
            'id' => self::USER_ID_5,
            'email' => 'user5@email.com',
            'auth_provider' => AuthProviderEnum::OPEN_ID,
            'auth_uid' => '1',
            'status' => EntityStatusEnum::ENABLED,
        ],
        [
            'id' => self::USER_ID_6,
            'email' => 'user6@email.com',
            'auth_provider' => AuthProviderEnum::OPEN_ID,
            'auth_uid' => '1',
            'status' => EntityStatusEnum::ENABLED,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $this->deleteAllDataFromTable(User::class);

        foreach (self::USERS as $userData) {
            $user = new User();
            $user->email = $userData['email'];
            $user->setStatus($userData['status']->getValue());
            $user->setAuthProvider($userData['auth_provider']->getValue());
            $user->setAuthUid((string) $userData['auth_uid']);

            $this->setReference(sprintf('%s-%s', self::USER_ID_PREFIX, $userData['id']), $user);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
