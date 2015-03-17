<?php
namespace Fol\Http\Sessions;

use Fol\Http\MiddlewareStack;
use Fol\Http\Request;
use Fol\Http\Response;

/**
 * Manage the PHP native session
 */
class Native extends Session
{
    /**
     * Regenerate the id for the current session.
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
     * Destroy the current session deleting the data.
     */
    public function destroy()
    {
        $this->delete();

        $this->id = null;

        session_destroy();
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
    public function run(Request $request, Response $response, MiddlewareStack $stack)
    {
        if (!$this->name) {
            $this->name = session_name();
        }

        if (!$this->id && $request->cookies->get($this->name)) {
            $this->id = $request->cookies->get($this->name);
        }

        $baseUrl = $request->attributes->get('BASE_URL') ?: new Url('');

        $cookie = [
            'domain' => $baseUrl->getHost(),
            'path' => $baseUrl->getPath(false),
            'secure' => ($baseUrl->getScheme() === 'https'),
            'httponly' => true,
            'expires' => ini_get('session.cookie_lifetime'),
        ];

        $this->start($cookie);

        $request->attributes->set('SESSION', $this);

        $stack->next();

        if ((session_status() === PHP_SESSION_ACTIVE) && (session_name() === $this->name) && (session_id() === $this->id)) {
            session_write_close();
        }

        if (!$this->id) {
            $response->cookies->setDelete($this->name, $cookie);
        }
    }

    /**
     * Starts the session.
     *
     * @param array $cookie
     *
     * @throws \RuntimeException if session cannot be started
     */
    protected function start(array $cookie)
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

        session_set_cookie_params($cookie['expires'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);

        //Start
        session_start();

        $this->id = session_id();
        $this->items = & $_SESSION;
    }
}
