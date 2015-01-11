<?php
/**
 * Fol\Http\Sessions\Native
 *
 * Class to manage the PHP native session
 */
namespace Fol\Http\Sessions;

use Fol\Http\RequestHandler;
use Fol\Http\Response;

class Native extends Session
{
    /**
     * Construct and loads the session data
     *
     * @param RequestHandler     $handler
     * @param string|null $id
     * @param string|null $name
     */
    public function __construct(RequestHandler $handler, $id = null, $name = null)
    {
        if (!$name) {
            $name = session_name();
        }

        $request = $handler->getRequest();

        if (!$id && $request->cookies->get($name)) {
            $id = $request->cookies->get($name);
        }

        $this->id = $id;

        $this->start($handler);

        $handler->pushHandler([$this, 'handlerCallback']);
    }

    /**
     * Starts the session
     *
     * @param RequestHandler $handler
     * 
     * @throws \RuntimeException if session cannot be started
     */
    protected function start(RequestHandler $handler)
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new \RuntimeException('Native sessions are disabled');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Failed to start the session: already started by PHP.');
        }

        session_name($this->name);

        if ($this->id) {
            session_id($this->id);
        }

        //Configure session cookie
        ini_set('session.use_only_cookies', 1);

        $cookie = $handler->getCookiesDefaultConfig();

        $cookie['httponly'] = true;
        $cookie['expires'] = ini_get('session.cookie_lifetime');

        session_set_cookie_params($cookie['expires'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);

        //Start
        session_start();

        $this->id = session_id();
        $this->items =& $_SESSION;
    }

    /**
     * Regenerate the id for the current session
     */
    public function regenerate($destroy = false, $lifetime = null)
    {
        if ($lifetime !== null) {
            ini_set('session.cookie_lifetime', $lifetime);
        }

        $return = session_regenerate_id($destroy);
        $this->id = session_id();

        return $return;
    }

    /**
     * Close the session and save the data.
     */
    public function save()
    {
        session_write_close();
    }

    /**
     * Destroy the current session deleting the data
     */
    public function destroy()
    {
        $this->delete();

        $this->id = null;

        session_destroy();
    }


    /**
     * request handler callback
     *
     * @param RequestHandler $handler
     * @param Response $response
     */
    public function handlerCallback(RequestHandler $handler, Response $response)
    {
        if ((session_status() === PHP_SESSION_ACTIVE) && (session_name() === $this->name) && (session_id() === $this->id)) {
            session_write_close();
        }

        if (!$this->id) {
            $cookie = $handler->getCookiesDefaultConfig();
            $cookie['httponly'] = true;

            $response->cookies->setDelete($this->name, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
        }
    }
}
