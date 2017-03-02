<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 14:55
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\Adapter\LogOutInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stratigility\MiddlewareInterface;

class LogoutAction implements MiddlewareInterface
{
    /** @var AuthenticationServiceInterface  */
    protected $authenticationService;

    /**
     * LogoutAction constructor.
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        if($this->authenticationService->hasIdentity()) {
            $this->authenticationService->clearIdentity();
        }

        $request = $request->withAttribute('responseData', ['status' => 'Logout complete!']);
        if (isset($out)) {
            return ($out($request, $response));
        }
        return $response;
    }
}
