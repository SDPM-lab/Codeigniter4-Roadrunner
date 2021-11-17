<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class BasicTest extends BaseController
{
	use ResponseTrait;

	protected $format = "json";

	/**
	 * load view
	 */
	public function loadView()
	{
		return view('welcome_message');
	}

	/**
	 * echo text(test php output)
	 */
	public function echoText()
	{
		echo "testText";
	}

	/**
	 * test query($_GET)
	 */
	public function urlqyery()
	{
		$text1 = $this->request->getGet("texts")[0];
		$text2 = $this->request->getGet("texts")[1];
		$text3 = $this->request->getGet("text3");
		return md5($text1 . $text2 . $text3);
	}

	/**
	 * test x-www-form-urlencoded($_POST)
	 */
	public function formparams()
	{
		$text1 = $this->request->getPost("texts")[0];
		$text2 = $this->request->getPost("texts")[1];
		$text3 = $this->request->getPost("text3");
		return md5($text1 . $text2 . $text3);
	}

	/**
	 * x-www-form-urlencoded and query mix test
	 */
	public function formparamsandquery()
	{
		$text1 = $this->request->getGet("text1");
		$text2 = $this->request->getPost("text2");
		return md5($text1 . $text2);
	}

	/**
	 * read header
	 */
	public function readHeader()
	{
		$token = $this->request->getHeader("X-Auth-Token")->getValueLine();
		return $this->respond(["X-Auth-Token" => $token]);
	}

	/**
	 * send header
	 */
	public function sendHeader()
	{
		$this->response->setHeader("X-Set-Auth-Token", uniqid());
		return $this->respond(["status" => true]);
	}
}
