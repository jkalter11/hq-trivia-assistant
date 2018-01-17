# HQ trivia assistant

![Software License][ico-license]

A small bot to help aide you in picking the right answer in the HQ app trivia game. This small app hooks into HQ's websocket stream and automatically searches Google and Yahoo! for the correct answer using a variety of searches. 


## Install

``` bash
$ git clone https://github.com/mikealmond/hq-trivia-assistant .
$ cd hq-trivia-assistant
$ composer install
$ cp .env.dist .env
```
After you've created the `.env` file, fill in your HQ user ID and bearer token. You can find your ID and token by sniffing the web traffic from your phone using a tool such as [Charles Proxy](https://www.charlesproxy.com/).

Note: installation assumes that you have installed [Composer](https://getcomposer.org/doc/00-intro.md#globally) already.

## Usage

``` bash
$ php run.php
```


## Contributing

All contributions welcome.

## Credits

- [Mike Almond][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT)

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[link-author]: https://github.com/mikealmond
[link-contributors]: ../../contributors
