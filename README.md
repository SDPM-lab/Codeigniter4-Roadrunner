# Codeigniter4-Roadrunner

<p align="center">
  <img src="https://i.imgur.com/sCjeSTo.png" alt="logo" width="500" />
</p>

[![Latest Stable Version](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/v)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![Total Downloads](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/downloads)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![Latest Unstable Version](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/v/unstable)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![License](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/license)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner)

Codeigniter4-RoadRunner provides the synchroniztion of the Request and Response object between Roadrunner-Worker and Codeigniter4. Since Codeigniter4 doesn't implement  [PSR-7 standard](https://codeigniter.tw/user_guide/intro/psr.html) completely, you need to use this library to allow your Codeigniter4 project to run using RoadRunner Server.

> This library is currently under development, and its functions are not yet stable. Do not use it in production environment.

[正體中文說明書](https://github.com/SDPM-lab/Codeigniter4-Roadrunner/blob/dev/README_zh-TW.md)

## Install

### Prerequisites
1. CodeIgniter Framework 4.*
2. Composer
3. Enable `php-curl` extension
4. Enable `php-zip` extension

### Composer Install
Use "Composer" to download the library and its dependencies to the project
```
composer require sdpmlab/codeigniter4-roadrunner "v1.0.0"
```
Initialize Roadrunner and files using built-in commands in the library

```
php spark ciroad:init
```

## Run
Run the command in the root directory of your project:
1. Use Codeigniter4 spark command
  ```
  php spark ciroad:start -v -d
  ```
2. Use Roadrunner command in Windows
  ```
  rr.exe serve -v -d
  ```
3. Use Roadrunner command in MacOS/Linux
  ```
  ./rr serve -v -d
  ```

## Server Settings
The server settings are all in the project root directory ".rr.yaml". The default file will look like this:
```yaml
http:
  address:         0.0.0.0:8080
  workers:
    command:  "php psr-worker.php"
    # pool:
    #   numWorkers: 50
    #   maxJobs:  500

static:
  enable:  true
  dir:   "public"
  forbid: [".php", ".htaccess"]
```
You can create your configuration file according to the [Roadrunner document](https://roadrunner.dev/docs/intro-config).

## Development Suggestions

### Automatic reload

In the default circumstance of RoadRunner, you must restart the server everytime after you revised any PHP files so that your revision will effective.
It seems not that friendly during development.

You can revise your `.rr.yaml` configuration file, add the settings below and start the development mode with `-v -d`.
RoadRunner Server will detect if the PHP files were revised or not, automatically, and reload the Worker instantly.

```yaml
# reload can reset rr servers when files change
reload:
  #refresh interval (default 1s)
  interval: 1s
  #file extensions to watch, defaults to [.php]
  patterns: [".php"]
```

The `reload` function is very resource-intensive, please do not activate the option in the formal environment.

### Using Codeigniter4 Request and Response object

Codeigniter4 does not implement the complete [HTTP message interface](https://www.php-fig.org/psr/psr-7/), hence this library focuses on the synchronize of `PSR-7 interface` and `Codeigniter4 HTTP interface`.

Base on the reasons above, You should use `$this->request`, provided by Codeigniter4, or the global function `/Config/Services::('request')` to fetch the correct request object; Use `$this->response` or `/Config/Services::('response')` to fetch the correct response object.

Please be noticed, while constructing response for the users during developing, you should prevent using PHP built-in methods to conduct `header` or `set-cookies` settings. Using the `setHeader()` and `setCookie()`, provided by the [Codeigniter4 Response Object](https://codeigniter.com/user_guide/outgoing/response.html), to conduct setting.

### Use return to stop controller logic

Inside the Controller, try using return to stop the controller logic. No matter the response of view or API, reduce the `echo` output usage can avoid lets of errors, just like ths:

```php
<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    //Don't use :
    //echo view('welcome_message');
    return view('welcome_message');
  }

  /**
   * send header
   */
   public function sendHeader()
   {
     $this->response->setHeader("X-Set-Auth-Token", uniqid());
     return $this->respond(["status"=>true]);
   }

}
```

### Use the built-in Session library

We only focus on supporting the Codeigniter4 built-in [Session library](https://codeigniter.com/user_guide/libraries/sessions.html), and do not guarantee if using `session_start()` and `$_SEEEION` can work as normal. So, you should avoid using the PHP built-in Session method, change to the Codeigniter4 framework built-in library.

### Developing and debugging in a environment with only one Worker

Since the RoadRunner has fundamentally difference with other server software(i.e. Nginx, Apache), every Codeigniter4 will persist inside RAMs as the form of Worker, HTTP requests will reuse these Workers to process. Hence, we have better develop and test stability under the circumstance with only one Worker to prove it can also work properly under serveral Workers in the formal environment.

You can reference the `.rr.yaml` settings below to lower the amount of Worker to the minimum:

```yaml
http:
  address:         0.0.0.0:8080
  workers:
    command:  "php psr-worker.php"
    pool:
      numWorkers: 1
      # maxJobs:  500
```

### 資料庫連線

我們只針對 Codeigniter4 內建 [Database 程式庫](https://codeigniter.com/user_guide/database/index.html) 進行支援，並不保證 PHP 內鍵的方法是否能照常運作。所以，你應該避免使用內建的 PHP 資料庫連線方法，而是以 Codeigniter4 框架內建的程式庫為主。

預設的情況下，在 Worker 中的 DB 連線是持久的，並會在連線失效時自動重新連線。所有進入 Worker 的 Request 都使用同一個 DB 連線實體。如果你不想要這個預設設定，希望每個進入 Worker 的 Request 都使用重新連線的 DB 連線實體。你可以在專案根目錄下的 `.env` 檔案加入以下設定。

```env
CIROAD_DB_AUTOCLOSE = true
```

# Global Methods

We offer some Global methods to help you develop your projects more smoothly.

### Dealing with the file uploading

Since the RoadRunner Worker can not transfer the correct `$_FILES` context, the Codeigniter4 file upload class will not be able to work properly. To solve this, we offered a file upload class corresponding the PSR-7 standard for you to deal with file uploading correctly within RoadRunner. Even if you switched your project to another server environment(i.e. spark serve, Apache, Nginx), this class can still work properly, and doesn't need any code modification.

You can fetch the uploaded files by means of `SDPMlab\Ci4Roadrunner\UploadedFileBridge::getPsr7UploadedFiles()` in the controller (or any other places). This method will return an array, consist of Uploaded File objects. The available methods of this object is identical as the regulation of [PSR-7 Uploaded File Interface](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface).

```php
<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use SDPMlab\Ci4Roadrunner\UploadedFileBridge;

class FileUploadTest extends BaseController
{
	use ResponseTrait;

	protected $format = "json";
	/**
	 * form-data 
	 */
	public function fileUpload(){
		$files = UploadedFileBridge::getPsr7UploadedFiles();
		$data = [];
		foreach ($files as $file) {
			$fileEx = array_pop(
				explode('.', $file->getClientFilename())
			);
			$newFileName = uniqid(rand()).".".$fileEx;
			$newFilePath = WRITEPATH.'uploads'.DIRECTORY_SEPARATOR.$newFileName;
			$file->moveTo($newFilePath);
			$data[$file->getClientFilename()] = md5_file($newFilePath);
		}
		return $this->respondCreated($data);	
	}

	/**
	 * form-data multiple upload
	 */
	public function fileMultipleUpload(){
		$files = UploadedFileBridge::getPsr7UploadedFiles()["data"];
		$data = [];
		foreach ($files as $file) {
			$fileEx = array_pop(
				explode('.', $file->getClientFilename())
			);
			$newFileName = uniqid(rand()).".".$fileEx;
			$newFilePath = WRITEPATH.'uploads'.DIRECTORY_SEPARATOR.$newFileName;
			$file->moveTo($newFilePath);
			$data[$file->getClientFilename()] = md5_file($newFilePath);
		}
		return $this->respondCreated($data);	
	}
```

### Dealing with thrown errors

If you encountered some variables or object content that needed to be confirmed in `-v -d` development mode, you can use the global function `dump()` to throw errors onto the terminal no matter where the program is.

```php
 /**
  * Dump given value into target output.
  *
  * @param mixed $value Variable
  * @param string $target Possible options: OUTPUT, RETURN, ERROR_LOG, LOGGER.
  * @return string|null
  */
function dump($value,string $target = "ERROR_LOG") : ?string;
```

## Avaliable commands

### ciroad:init

Initiallize RoadRunner and its needed files.

* Use
    ```
    $ php spark ciroad:init
    ```

### ciroad:start

Start RoadRunner Server

* Use
    ```
    $ php spark ciroad:start [Options]
    ```

* Options:
    ```
    -d      During debugging mode, HTTP requests details will be listed on the terminal
    -b      run in the background
    -v      output details
    ```

### ciroad:stop

Kill the RoadRunner running in the background.

* Use
    ```
    $ php spark ciroad:stop
    ```

### ciroad:reset

Force reload all the HTTP Workers.

* Use
    ```
    $ php spark ciroad:reset
    ```

### ciroad:status

Check the current Worker operating status

* Use
    ```
    $ php spark ciroad:status [Options]
    ```

* Options:
    ```
    -i      output status continuously per second
    ```
