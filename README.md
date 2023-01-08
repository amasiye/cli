# Assegai CLI

## Requirements
- PHP 8.1 (minimum)
- Composer 2.x.x

## Description

The Assegai CLI is a command-line interface tool that helps you to initialize, develop and maintain your Assegai applications. It assists in multiple ways, including scaffolding the project, serving it in development mode and building the application for production. It embodies best-practice architectural patterns to encourage well-structured apps.

The CLI works with [schematics][schematics] and provides built in support from the schematics' collection at [@assegaiphp/schematics](https://github.com/assegai-php/schematics)

## Installation
### Windows
Before we create a new Assegai application on your Windows machine, make sure to install Docker Desktop. Next, you should ensure that Windows Subsystem for Linux 2 (WSL2) is installed and enabled. WSL allows you to run Linux binary executables natively on Windows 10. Information on how to install and enable WSL2 can be found within Microsoft's developer environment documentation.

> After installing and enabling WSL2, you should ensure that Docker Desktop is configured to use the WSL2 backend.

Next, you are ready to create your first Assegai project. Launch Windows Terminal and begin a new terminal session for your WSL2 Linux operating system. Next, you can use a simple terminal command to create a new Assegai project. For example, to create a new Assegai application in a directory named "example-app", you may run the following command in your terminal:

```
$ composer require assegaiphp/cli
```

## Usage

Learn more in the [official documentation](https://assegaiphp.com/guide/cli/overview).

## Stay in touch

* Author - [Andrew Masiye](https://twitter.com/feenix11), [Daniel Kaluba](https://twitter.com/ZombieKlassic)
* Website - [https://atatusoft.com](https://atatusoft.com/)
* Twitter - [@assegaiphp](https://twitter.com/assegaiphp)

## License

Assegai CLI is [MIT Licensed](LICENSE)

[schematics]: https://github.com/angular/angular-cli/tree/master/packages/angular_devkit/schematics