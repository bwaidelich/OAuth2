<?php

namespace Refactory\OAuth\Domain\Model;

/*
 * This file is part of the Refactory.OAuth package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Refactory\OAuth\OAuthClients\AWSClient;
use Refactory\OAuth\OAuthClients\AzureClient;
use Refactory\OAuth\OAuthClients\GCPClient;

/**
 * @Flow\Entity
 */
class Provider
{
    const OAUTH_CLIENTS = [
        GCPClient::class => 'google',
        AzureClient::class => 'azure',
        AWSClient::class => 'amazon'
    ];

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected $name;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected $authenticationEndpoint;

    /**
     * @Flow\Identity()
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected $oauthClient;

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
    public function getOauthClient(): string
    {
        return $this->oauthClient;
    }

    /**
     * @param string $oauthClient
     */
    public function setOauthClient(string $oauthClient): void
    {
        $this->oauthClient = $oauthClient;
    }

    /**
     * @return string
     */
    public function getAuthenticationEndpoint(): string
    {
        return $this->authenticationEndpoint;
    }

    /**
     * @param string $authenticationEndpoint
     */
    public function setAuthenticationEndpoint(string $authenticationEndpoint): void
    {
        $this->authenticationEndpoint = $authenticationEndpoint;
    }
}