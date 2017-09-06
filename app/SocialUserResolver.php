<?php

namespace App;

use Adaojunior\Passport\SocialGrantException;
use Adaojunior\Passport\SocialUserResolverInterface;
use App\User;

class SocialUserResolver implements SocialUserResolverInterface
{

    /**
     * Resolves user by given network and access token.
     *
     * @param string $network
     * @param string $accessToken
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function resolve($network, $accessToken, $accessTokenSecret = null)
    {

        try {
            return User::where([
                ['social.name',  $network],
                ['social.token', $accessToken],
            ])->first();

        } catch (Exception $e) {
            throw SocialGrantException::invalidNetwork();
        }


        /*switch ($network) {
            case 'facebook':
                return $this->authWithFacebook($network,$accessToken);
                break;
            default:
                throw SocialGrantException::invalidNetwork();
                break;
        }*/
    }


    /**
     * Resolves user by pro access token.
     *
     * @param string $accessToken
     * @return \App\User
     */
    /*protected function authWithFacebook($network,$accessToken)
    {
        return User::where([
                ['social.name',  $network],
                ['social.token', $accessToken],
            ])->first();
    }*/
}

