<?php
namespace Fol\Http\Sessions;

use Fol\Bag;
use Fol\Http\Url;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Middlewares\Middleware;
use Fol\Http\Middlewares\MiddlewareInterface;

/**
 * Manage a session
 */
class Session extends Bag implements MiddlewareInterface
{
    protected $id;
    protected $name;

    /**
     * Construct and loads the session data.
     *
     * @param string $id
     * @param string $name
     */
    public function __construct($id = null, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Run the session as a middleware.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, Middleware $stack)
    {
        return $this->run($request, $response, $stack);
    }

    /**
     * Close the session and save the data.
     */
    public function save()
    {
    }

    /**
     * Destroy the current session deleting the data.
     */
    public function destroy()
    {
        $this->delete();
    }

    /**
     * Get the current session id.
     *
     * @return string The id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the session name.
     *
     * @return string The name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Regenerate the id for the current session.
     *
     * @param boolean $destroy  Set true to destroy the current data
     * @param integer $lifetime The new session duration
     */
    public function regenerate($destroy = false, $lifetime = null)
    {
        if ($destroy) {
            $this->delete();
        }

        $this->id = uniqid();
    }

    /**
     * Get a flash value (read only once).
     *
     * @param string $name The value name. If it is not defined, returns all stored variables
     *
     * @return string The value of the variable or the default value.
     * @return array  All stored variables in case no name is defined.
     */
    public function getFlash($name = null)
    {
        if ($name === null) {
            return isset($this->items['_flash']) ? $this->items['_flash'] : [];
        }

        if (isset($this->items['_flash'][$name])) {
            $value = $this->items['_flash'][$name];
            unset($this->items['_flash'][$name]);

            return $value;
        }
    }

    /**
     * Set a new flash value.
     *
     * @param string|array $name  The variable name or an array of variables
     * @param string       $value The value of the variable
     */
    public function setFlash($name, $value = null)
    {
        if (!isset($this->items['_flash'])) {
            $this->items['_flash'] = [];
        }

        if (is_array($name)) {
            $this->items['_flash'] = array_replace($this->items['_flash'], $name);
        } else {
            $this->items['_flash'][$name] = $value;
        }
    }

    /**
     * Check if a flash variable is defined or not (but does not remove it).
     *
     * @param string $name The variable name.
     *
     * @return boolean True if it's defined, false if not
     */
    public function hasFlash($name)
    {
        return (isset($this->items['_flash']) && array_key_exists($name, $this->items['_flash']));
    }

    /**
     * Run the session.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function run(Request $request, Response $response, Middleware $stack)
    {
        if (!$this->name) {
            $this->name = 'PHPSESSID';
        }

        if (!$this->id && $request->cookies->get($this->name)) {
            $this->id = $request->cookies->get($this->name);
        }

        $request->attributes->set('SESSION', $this);

        $stack->next();

        $baseUrl = $request->attributes->get('BASE_URL') ?: new Url('');

        $cookie = [
            'domain' => $baseUrl->getHost(),
            'path' => $baseUrl->getPath(false),
            'secure' => ($baseUrl->getScheme() === 'https'),
            'httponly' => true,
        ];

        if (!$this->id) {
            $response->cookies->setDelete($this->name, $cookie);
        } else {
            $cookie['value'] = $this->id;
            $response->cookies->set($this->name, $cookie);
        }
    }
}
