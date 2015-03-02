<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\Utils;

/**
 * Middleware to create a basic authentication
 */
class BasicAuthentication extends Authentication
{
    protected $users;
    protected $realm;

    /**
     * Constructor. Defines de users
     *
     * @param array $users [username => password]
     * @param string $realm
     */
    public function __construct(array $users = null, $realm = 'Login')
    {
        $this->users = $users;
        $this->realm = $realm;
    }

    /**
     * {@inheritdoc}
     */
    protected function onError(Request $request, Response $response, MiddlewareStack $stack)
    {
        $response->headers->set('WWW-Authenticate', 'Basic realm="'.$this->realm.'"');

        parent::onError($request, $response, $stack);
    }

    /**
     * {@inheritdoc}
     */
    protected function login(Request $request)
    {
        $authorization = static::parseAuthorizationHeader($request->headers->get('Authorization'));

        if (empty($authorization) || !$this->checkAuthentication($authorization['username'], $authorization['password'])) {
            return false;
        }

        return true;
    }

    /**
     * Validate the user and password
     * 
     * @param string $username
     * @param string $password
     * 
     * @return boolean
     */
    protected function checkAuthentication($username, $password)
    {
        if (!isset($this->users[$username]) || $this->users[$username] !== $password) {
            return false;
        }

        return true;
    }

    /**
     * Parses the authorization header
     *
     * @param string $authorization
     *
     * @return boolean|array
     */
    protected static function parseAuthorizationHeader($authorization)
    {
        if (strpos($authorization, 'Basic') !== 0) {
            return false;
        }
        
        $authorization = explode(':', base64_decode(substr($authorization, 6)), 2);

        return [
            'username' => $authorization[0],
            'password' => isset($authorization[1]) ? $authorization[1] : null
        ];
    }
}
