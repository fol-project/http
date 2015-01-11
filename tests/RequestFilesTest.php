<?php
use Fol\Http\RequestFiles;

require_once dirname(__DIR__).'/src/autoload.php';

class RequestFilesTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $files = new RequestFiles();
        $files->set([
            'images' => [
                [
                    'name' => 'file1.jpg',
                    'tmp_name' => 'abcdef',
                    'size' => 2300,
                    'type' => 'image/jpeg',
                    'error' => 0
                ],[
                    'name' => 'file2.png',
                    'tmp_name' => 'efghij',
                    'size' => 2555,
                    'type' => 'image/png',
                    'error' => 1
                ]
            ],
            'avatar' => [
                'name' => 'avatar.png',
                'tmp_name' => '12345',
                'size' => 1000,
                'type' => 'image/png',
                'error' => 3
            ]
        ]);

        $this->assertEquals(2, $files->length());
        $this->assertEquals('file1.jpg', $files->get('images[0][name]'));
        $this->assertTrue($files->hasError('images[1]'));
        $this->assertSame(1, $files->getErrorCode('images[1]'));
        $this->assertEquals('The uploaded file exceeds the upload_max_filesize directive in php.ini', $files->getErrorMessage('images[1]'));

        $this->assertEquals('avatar.png', $files->get('avatar[name]'));
        $this->assertTrue($files->hasError('avatar'));
        $this->assertSame(3, $files->getErrorCode('avatar'));
        $this->assertEquals('The uploaded file was only partially uploaded', $files->getErrorMessage('avatar'));
    }
}
