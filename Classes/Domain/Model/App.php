<?php
namespace Refactory\OAuth\Domain\Model;

/*
 * This file is part of the Refactory.OAuth package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class App
{

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected $name;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected $clientId;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @ORM\ManyToOne()
     * @var \Refactory\OAuth\Domain\Model\Provider
     */
    protected $provider;

    /**
     * @var array
     * @ORM\Column(type="json", nullable = true)
     */
    protected $notes;

    /**
     *
     * @var string
     */
    protected $authorizationId = '';

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected $apiUri;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return void
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }
    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return void
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }
    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     * @return void
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }
    /**
     * @return \Refactory\OAuth\Domain\Model\Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param \Refactory\OAuth\Domain\Model\Provider $provider
     * @return void
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getAuthorizationId(): string
    {
        return $this->authorizationId;
    }

    /**
     * @param string $authorizationId
     */
    public function setAuthorizationId(string $authorizationId): void
    {
        $this->authorizationId = $authorizationId;
    }

    /**
     * @return string
     */
    public function getApiUri(): string
    {
        return $this->apiUri;
    }

    /**
     * @param string $apiUri
     */
    public function setApiUri(string $apiUri): void
    {
        $this->apiUri = $apiUri;
    }

    /**
     * @return array
     */
    public function getNotes(): array
    {
        return $this->notes ?? [];
    }

    /**
     * @param array $notes
     */
    public function setNotes(array $notes): void
    {
        $this->notes = $notes;
    }
}