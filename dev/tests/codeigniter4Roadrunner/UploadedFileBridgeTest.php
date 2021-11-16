<?php namespace SDPMlab\Ci4Roadrunner\Test;

use SDPMlab\Ci4Roadrunner\UploadedFileBridge;
use SDPMlab\Ci4Roadrunner\UploadedFile;

class UploadedFileBridgeTest extends \CodeIgniter\Test\CIUnitTestCase
{

    protected function setUp(): void
	{
		parent::setUp();
        $_FILES = [];
        UploadedFileBridge::reset();
    }

    public function testUploadedFileBridge()
	{
		$_FILES = [
			'userfile' => [
				'name'     => 'someFile.txt',
				'type'     => 'text/plain',
				'size'     => '124',
				'tmp_name' => '/tmp/myTempFile.txt',
				'error'    => 0,
			],
        ];
        
        $files = UploadedFileBridge::getPsr7UploadedFiles();

		$this->assertCount(1, $files);

		$file = array_shift($files);
		$this->assertInstanceOf(UploadedFile::class, $file);

		$this->assertEquals('someFile.txt', $file->getClientFilename());
		$this->assertEquals(124, $file->getSize());
    }
    
    public function testGetFileMultiple()
	{
		$_FILES = [
			'userfile' => [
				'name'     => [
					'someFile.txt',
					'someFile2.txt',
				],
				'type'     => [
					'text/plain',
					'text/plain',
				],
				'size'     => [
					'124',
					'125',
				],
				'tmp_name' => [
					'/tmp/myTempFile.txt',
					'/tmp/myTempFile2.txt',
				],
				'error'    => [
					0,
					0,
				],
			],
		];

        $gotit = UploadedFileBridge::getPsr7UploadedFiles()["userfile"];
		$this->assertEquals(124, $gotit[0]->getSize());
		$this->assertEquals(125, $gotit[1]->getSize());
	}

}
