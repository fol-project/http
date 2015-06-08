<?php
namespace Fol\Http;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Class to represent and manipulate uploaded files
 */
class UploadedFile implements UploadedFileInterface
{
    protected $file;

    /**
     * @see UploadedFileInterface
     * 
     * {inheritDoc}
     */
    public function getStream()
    {
        return new Stream($this->file['tmp_name'], 'r');
    }

    /**
     * @see UploadedFileInterface
     * 
     * {inheritDoc}
     */
    public function moveTo($targetPath)
    {
        move_uploaded_file($this->file['tmp_name'], $targetPath);
    }

    /**
     * @see UploadedFileInterface
     * 
     * {inheritDoc}
     */
    public function getSize()
    {
        return $this->file['size'];
    }

    /**
     * @see UploadedFileInterface
     * 
     * {inheritDoc}
     */
    public function getError()
    {
        return $this->file['error'];
    }

    /**
     * @see UploadedFileInterface
     * 
     * {inheritDoc}
     */
    public function public function getClientFilename()
    {
        return $this->file['name'];
    }

    /**
     * @see UploadedFileInterface
     * 
     * {inheritDoc}
     */
    public function public function getClientMediaType()
    {
        return $this->file['type'];
    }
}
