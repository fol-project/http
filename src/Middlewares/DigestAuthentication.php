<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;

/**
 * Middleware to create a digest authentication.
 */
class DigestAuthentication extends Authentication
{
    protected $users;
    protected $realm;
    protected $nonce;

    /**
     * Constructor. Defines de users.
     *
     * @param array $users [username => password]
     */
    public function __construct(array $users = null, $realm = 'Login', $nonce = null)
    {
        $this->users = $users;
        $this->realm = $realm;
        $this->nonce = $nonce ?: uniqid();
    }

    /**
     * {@inheritdoc}
     */
    protected function onError(Request $request, Response $response, Middleware $stack)
    {
        $response->headers->set('WWW-Authenticate', 'Digest realm="'.$this->realm.'",qop="auth",nonce="'.$this->nonce.'",opaque="'.md5($this->realm).'"');

        parent::onError($request, $response, $stack);
    }

    /**
     * Login.
     *
     * @param Request $request
     *
     * @return boolean
     */
    protected function login(Request $request)
    {
        $authorization = static::parseAuthorizationHeader($request->headers->get('Authorization'));

        if (!$authorization) {
            return false;
        }

        if (($password = $this->getPassword($authorization['username'])) === null) {
            return false;
        }

        if (!$this->checkAuthentication($authorization, $request->getMethod(), $password)) {
            return false;
        }

        return true;
    }

    /**
     * Detect and return the format.
     *
     * @param string $user
     *
     * @return string|null
     */
    protected function getPassword($user)
    {
        return isset($this->users[$user]) ? $this->users[$user] : null;
    }

    /**
     * Validate the user and password.
     *
     * @param array  $authorization
     * @param string $method
     * @param string $password
     *
     * @return boolean
     */
    protected function checkAuthentication(array $authorization, $method, $password)
    {
        $A1 = md5("{$authorization['username']}:{$this->realm}:{$password}");
        $A2 = md5("{$method}:{$authorization['uri']}");

        $validResponse = md5("{$A1}:{$authorization['nonce']}:{$authorization['nc']}:{$authorization['cnonce']}:{$authorization['qop']}:{$A2}");

        return ($authorization['response'] === $validResponse);
    }

    /**
     * Parses the authorization header.
     *
     * @param string $authorization
     *
     * @return boolean|array
     */
    protected static function parseAuthorizationHeader($authorization)
    {
        if (strpos($authorization, 'Digest') !== 0) {
            return false;
        }

        $needed_parts = ['nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1];
        $data = [];

        preg_match_all('@('.implode('|', array_keys($needed_parts)).')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', substr($authorization, 7), $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return empty($needed_parts) ? $data : false;
    }
}
