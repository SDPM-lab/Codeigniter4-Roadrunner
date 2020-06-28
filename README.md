# Codeigniter4-Roadrunner

<p align="center">
  <img src="https://i.imgur.com/sCjeSTo.png" alt="logo" width="500" />
</p>

[![Latest Stable Version](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/v)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![Total Downloads](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/downloads)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![Latest Unstable Version](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/v/unstable)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner) [![License](https://poser.pugx.org/sdpmlab/codeigniter4-roadrunner/license)](//packagist.org/packages/sdpmlab/codeigniter4-roadrunner)

Make Codeigniter4 work on Roadrunner Server.

> This library is currently under development, and its functions are not yet stable. Please do not use it in a production environment.

## Install

### Prerequisites
1. CodeIgniter Framework 4.*
2. Composer

### Composer Install
Use "Composer" to download the library and its dependencies to the project
```
composer require monken/cli-create
```
Initialize Roadrunner and files using built-in commands in the library

```
php spark ciroad:init
```

## Run
Run the command in the root directory of the project:
1. Windows
  ```
  ./rr.exe serve -v -d
  ```
2. MacOS/Linux
  ```
  ./rr serve -v -d
  ```

## Server Settings
The server settings are all in the project root directory ".rr.yaml". The default file will look like this:
```
http:
  address:         0.0.0.0:80
  workers:
    command:  "php psr-worker.php"
    # pool:
    #   numWorkers: 50
    #   maxJobs:  10

static:
  enable:  true
  dir:   "public"
  forbid: [".php", ".htaccess"]
```
You can create your configuration file according to the [Roadrunner document](https://roadrunner.dev/docs).
