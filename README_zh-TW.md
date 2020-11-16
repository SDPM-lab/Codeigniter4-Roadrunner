# Codeigniter4-Roadrunner

<p align="center">
  <img src="https://i.imgur.com/sCjeSTo.png" alt="logo" width="500" />
</p>

[![Latest Stable Version](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/v)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![Total Downloads](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/downloads)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![Latest Unstable Version](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/v/unstable)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![License](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/license)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner)

Codeigniter4-RoadRunner 提供的是 Roadrunner-Worker 與 Codeigniter4 在 Request 與 Response 物件上的同步，因為 Codeigniter4 並沒有完整實作 [PSR-7 規範](https://codeigniter.tw/user_guide/intro/psr.html)。所以你需要使用這個程式庫才能讓你的 Codeigniter4 專案透過 RoadRunner Server 運作。

> 這個程式庫目前處於開發版，功能尚未穩定。請不要將它運用在正式環境中。

## 安裝

### 需求
1. CodeIgniter Framework 4.*
2. Composer
3. 安裝並開啟 php-curl 擴充套件
4. 安裝並開啟 php-zip 擴充套件

### Composer 安裝

於專案根目錄下，使用 Composer 下載程式庫與其所需之依賴。

```
composer require sdpmlab/codeigniter4-roadrunner "v1.0.0"
```

使用程式庫提供的內建指令初始化 Roadrunner 與其所需的檔案。

```
php spark ciroad:init
```

## 運作伺服器

你可以選擇使用不同的指令在專案根目錄中運作 RoadRunner Server：

1. 使用 Codeigniter4 spark 指令
  ```
  php spark ciroad:start -v -d
  ```
2. 在 Windows-cmd 中直使用 RoadRunner 指令
  ```
  rr.exe serve -v -d
  ```
3. 在 MacOS/Linux 中直接使用 RoadRunner 指令
  ```
  ./rr serve -v -d
  ```

## 伺服器組態設定

伺服器組態設定應置於專案根目錄下，並命名為 `.rr.yaml` 。程式庫初始化後產出的預設檔案看起來會像這樣子：

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

當然，你可以參考 [Roadrunner 手冊](https://roadrunner.dev/docs/intro-config) 建立符合專案需求的組態設定檔。

## 開發建議

### 自動重新載入

RoadRunner 預設的情況下，必須在每次修改 php 檔案後重啟伺服器，你所做的修改才會生效，這在開發上似乎不那麼友善。

你可以修改你的 `.rr.yaml` 組態設定檔案，加入以下設定後以 `-v -d` 開發模式啟動 RoadRunner Server，它將會自動偵測 PHP 檔案是否修改，並即時重新載入 Worker 。

```yaml
# reload can reset rr servers when files change
reload:
  # refresh interval (default 1s)
  interval: 1s
  # file extensions to watch, defaults to [.php]
  patterns: [".php"]
```

`reload` 是非常耗費資源的，請不要在正式環境中打開這個選項。

### 使用 Codeigniter4 Request 與 Response 物件

Codeigniter4 並沒有實作完整的 [HTTP message 介面](https://www.php-fig.org/psr/psr-7/)，所以這個程式庫著重於 `PSR-7 介面` 與 `Codeigniter4 HTTP 介面` 的同步。

基於上述原因，在開發上，你應該使用 Codeigniter4 所提供的 `$this->request` 或是使用全域函數 `\Config\Services::('request')` 取得正確的 request 物件；使用 `$this->response` 或是 `\Config\Services::('response')` 取得正確的 response 物件。

請注意，在建構給予使用者的響應時，不論是 `header` 或 `set-cookies` 應該避免使用 PHP 內建的方法進行設定。而是使用 [Codeigniter4 響應物件](https://codeigniter.tw/user_guide/outgoing/response.html) 提供的 `setHeader()` 與 `setCookie()` 進行設定。 

### 以 return 結束控制器邏輯

在 Controller 中，盡量使用 return 結束程式邏輯，不論是視圖的響應或是 API 響應，減少使用 `echo` 輸出內容可以避免很多錯誤，就像這個樣子。

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
		$this->response->setHeader("X-Set-Auth-Token",uniqid());
		return $this->respond(["status"=>true]);
	}

}
```

### 使用內建 Session 程式庫

我們只針對 Codeigniter4 內建 [Session 程式庫](https://codeigniter.tw/user_guide/libraries/sessions.html) 進行支援，並不保證使用 `session_start()` 與 `$_SESSION` 是否能照常運作。所以，你應該避免使用 PHP 內建的 Session 方法，而是以 Codeigniter4 框架內建的程式庫為主。

### 在只有一個 Worker 的環境中開發與除錯

因為 RoadRunner 與其他伺服器軟體（Nginx、Apache）有著根本上的不同，每個 Codeigniter4 將會以 Worker 的形式持久化於記憶體中，HTTP 的請求會重複利用到這些 Worker 進行處裡。所以，我們最好在只有單個 Worker 的情況下開發軟體並測試是否穩定，以證明在多個 Woker 的實際環境中能正常運作。 

你可以參考以下 `.rr.yaml` 設定將 Worker 的數量降到最低：

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

我們只針對 Codeigniter4 內建 [Database 程式庫](https://codeigniter.tw/user_guide/database/index.html) 進行支援，並不保證 PHP 內建的方法是否能照常運作。所以，你應該避免使用內建的 PHP 資料庫連線方法，而是以 Codeigniter4 框架內建的程式庫為主。

預設的情況下，在 Worker 中的 DB 連線是持久的，並會在連線失效時自動重新連線。所有進入 Worker 的 Request 都使用同一個 DB 連線實體。如果你不想要這個預設設定，希望每個進入 Worker 的 Request 都使用重新連線的 DB 連線實體。你可以在專案根目錄下的 `.env` 檔案加入以下設定。

```env
CIROAD_DB_AUTOCLOSE = true
```

## 全域方法

我們提供了一些全域方法，幫助你更加順利地開發你的專案。

### 處裡檔案上傳

因為 RoadRunner Worker 無法傳遞正確的 `$_FILES` 內容，所以 Codeingiter4 的 [檔案上傳類別](https://codeigniter.tw/user_guide/libraries/uploaded_files.html) 將無法正確運作。對此，我們提供了符合 PSR-7 規範的檔案上傳類別，讓你可以正確地在 RoadRunner 中處理檔案上傳。就算你將專案切換到了其他伺服器環境（spark serve、Apache、Nginx）運作，這個類別依舊可以正常使用，並且不需要修改任何程式碼。

你可以在控制器（或任何地方），以 `SDPMlab\Ci4Roadrunner\UploadedFileBridge::getPsr7UploadedFiles()` 取得使用者上傳的檔案。這個方法將回傳以 Uploaded File 物件組成的陣列。此物件可用的方法與 [PSR-7 Uploaded File Interface](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) 中規範的一樣。

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

### 處理錯誤拋出

如果你在 `-v -d` 開發模式中碰到了一些需要確認的變數、或物件內容，無論在程式的何處，你都可以使用全域函數 `dump()` 來將錯誤拋出到終端機上。

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

## 可用指令

### ciroad:init

初始化 Roadrunner 與其所需的檔案。

* Use
    ```
    $ php spark ciroad:init
    ```

### ciroad:start

啟動 RoadRunner 伺服器。

* Use
    ```
    $ php spark ciroad:start [Options]
    ```

* Options:
    ```
    -d      在除錯模式中執行，HTTP 請求詳情將會羅列在終端機。
    -b      在背景中執行。
    -v      輸出詳細資料。
    ```

### ciroad:stop

停止在背景運作的 RoadRunner 伺服器。

* Use
    ```
    $ php spark ciroad:stop
    ```

### ciroad:reset

強制重新載入所有 HTTP Worker。

* Use
    ```
    $ php spark ciroad:reset
    ```

### ciroad:status

查閱目前 Worker 運作狀態。

* Use
    ```
    $ php spark ciroad:status [Options]
    ```

* Options:
    ```
    -i      每秒持續輸出狀態。
    ```