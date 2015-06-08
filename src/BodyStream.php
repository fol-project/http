<?php
namespace Fol\Http;

/**
 * Class store a message body stream
 */
class BodyStream extends Stream
{
    protected $sendPosition = 0;

    /**
     * Send this body to the client
     */
    public function send()
    {
        if ($this->sendPosition !== false) {
            $this->seek($this->sendPosition);
        }

        while (!$this->eof()) {
            echo $this->read(1024);
            flush();
        }

        $this->sendPosition = $this->tell();
    }
}
