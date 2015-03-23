<?php
namespace Fol\Http;

/**
 * Interface used by all kinds of bodies
 */
interface BodyInterface
{
    /**
     * Converts the body to a string
     */
    public function __toString();

    /**
     * Closed the body
     */
    public function close();

    /**
     * Returns the size of the body if it's know
     *
     * @return integer|null
     */
    public function getSize();

    /**
     * Returns the current cursor position
     *
     * @return integer|false
     */
    public function tell();

    /**
     * Returns whether the cursor position is in the end
     *
     * @return boolean
     */
    public function eof();

    /**
     * Returns whether the body is seekable or not
     *
     * @return boolean
     */
    public function isSeekable();

    /**
     * Seek the cursor
     *
     * @param integer $offset
     * @param integer $whence
     *
     * @return boolean
     */
    public function seek($offset, $whence = SEEK_SET);

    /**
     * Returns whether the body is writable or not
     *
     * @return boolean
     */
    public function isWritable();

    /**
     * Push content to body
     *
     * @param string $string
     */
    public function write($string);

    /**
     * Returns whether the body is readable or not
     *
     * @return boolean
     */
    public function isReadable();

    /**
     * Read an amound of content from the body
     *
     * @param integer $length
     */
    public function read($length);

    /**
     * Gets the whole content of the body
     *
     * @return string
     */
    public function getContents();

    /**
     * Send the content to the output
     *
     * @return string
     */
    public function send();
}
