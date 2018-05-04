<?php

namespace UniteCMS\CoreBundle\Security;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use UniteCMS\CoreBundle\Entity\ApiKey;
use UniteCMS\CoreBundle\Service\UniteCMSManager;

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     * @var UniteCMSManager $uniteCMSManager
     */
    private $uniteCMSManager;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    public function __construct(UniteCMSManager $uniteCMSManager, EntityManager $entityManager)
    {
        $this->uniteCMSManager = $uniteCMSManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface|ApiKey
     *
     * @throws TokenNotFoundException if the token is not found
     */
    public function loadUserByUsername($username)
    {
        if (($token = $this->entityManager->getRepository('UniteCMSCoreBundle:ApiKey')->findOneBy(['token' => $username]))) {
            if($this->uniteCMSManager->getOrganization()->getId() === $token->getOrganization()->getId()) {
                return $token;
            }
        }
        throw new TokenNotFoundException("An API Key with token $username was not found for the current organization");
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the user is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof ApiKey) {
            throw new UnsupportedUserException('This provider ony supports API Key');
        }

        return $this->loadUserByUsername($user->getToken());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === ApiKey::class;
    }
}