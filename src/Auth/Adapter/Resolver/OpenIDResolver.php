<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 17:14
 */

namespace rollun\permission\Auth\Adapter\Resolver;

use rollun\api\Api\Google\Client\Web;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\dic\InsideConstruct;
use rollun\permission\Auth\Middleware\Factory\UserResolverFactory;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Result;

class OpenIDResolver implements ResolverInterface
{
    /** @var  DataStoreAbstract */
    protected $userDataStore;

    /** @var Web  */
    protected $webClient;

    /**
     * OpenID constructor.
     * @param Web $webClient
     * @param DataStoreAbstract|DataStoresInterface $userDataStore
     */
    public function __construct(Web $webClient = null, DataStoresInterface $userDataStore = null)
    {
        InsideConstruct::setConstructParams(['userDataStore' => UserResolverFactory::DEFAULT_USER_DS, 'webClient' => Web::class]);
        if(!isset($this->userDataStore)) {
            throw new \RuntimeException("userDataStore not set");
        }
        if(!isset($this->webClient)) {
            throw new \RuntimeException("webClient not set");
        }
    }

    /**
     * Resolve username/realm to password/hash/etc.
     *
     * @param  string $state Username
     * @param  string $realm Authentication Realm
     * @param  string $code Password (optional)
     * @return string|array|false User's shared secret as string if found in realm, or User's identity as array
     *         if resolved, false otherwise.
     */
    public function resolve($state, $realm, $code = null)
    {
        try {
            if($this->webClient->getResponseState() !== $state) {
                return new Result(
                    Result::FAILURE,
                    null,
                    ["State not equalse."]
                );
            }
            if ($this->webClient->authByCode($code)) {
                $userId = $this->webClient->getUserId();
                $user = $this->userDataStore->read($userId);
                if (!empty($user)) {
                    //unset($user['pass'])
                    return new Result(
                        Result::SUCCESS,
                        $user[$this->userDataStore->getIdentifier()],
                        ['Success credential']
                    );
                }
            }
            return new Result(
                Result::FAILURE,
                null,
                ['Fail credential']
            );
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE,
                null,
                [$e->getMessage()]
            );
        }
    }
}