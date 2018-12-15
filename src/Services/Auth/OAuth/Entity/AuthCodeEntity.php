<?php
/**
 * Created by PhpStorm.
 * User: Oarga-Tamas
 * Date: 2018. 09. 14.
 * Time: 15:03
 */

namespace MedevAuth\Services\Auth\OAuth\Entity;


interface AuthCodeEntity extends EntityInterface
{
    public function getCode();

    public function setCode();

    public function getClient();

    public function setClient(ClientEntityInterface $client);
}