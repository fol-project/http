<?php
/**
 * Fol\Http\RequestFiles
 *
 * Class to store the incoming files ($_FILES)
 */
namespace Fol\Http;

class RequestFiles extends RequestParameters
{
    public static $errors = [
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder'
    ];

    /**
     * Check if an uploaded file has any error
     *
     * @param string $name The name of the uploaded file
     *
     * @return boolean True if has an error, false if not
     */
    public function hasError($name)
    {
        $file = $this->get($name);

        if (isset($file['error']) && $file['error'] > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns the error code
     *
     * @param string $name The name of the uploaded file
     *
     * @return int The error code or null if the file doesn't exist
     */
    public function getErrorCode($name)
    {
        $file = $this->get($name);

        if (isset($file['error'])) {
            return $file['error'];
        }

        return null;
    }


    /**
     * Returns the error message
     *
     * @param string $name The name of the uploaded file
     *
     * @return string The error message or null if the file doesn't exist
     */
    public function getErrorMessage($name)
    {
        $code = $this->getErrorCode($name);

        if (($code === null) || !isset(self::$errors[$code])) {
            return null;
        }

        return self::$errors[$code];
    }
}
