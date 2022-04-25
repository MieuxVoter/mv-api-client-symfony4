
![MieuxVoter](./public/mv-logo.png)

A client for MieuxVoter's OpenApi specifications focused on _accessibility_.

- experimental
- compatibility with CLI/vocal browsers (that means _no javascript_)
- no database (only PHP sessions and external OpenAPI calls)
- not storing the password, but a perishable token (JWT) for the OpenApi


## Features

- [x] Majority Judgment Polls
- [x] Consumable Invitation Links
- [x] Inclusive Design (hopefully)
- [x] Designed for blazing fast keyboard usage

## Overview

- Symfony 4
- Spectre & SCSS
- No Javascript
- No Database (forwarding calls to OpenAPI)

## Roadmap

- [ ] Send Invitation Links by Email
- [ ] OpenGraph
- [ ] Proxy pass IP
- [ ] Follow another user
- [ ] Suggest opinion from followed users
- [ ] Handle 404 errors


## Work in Progress

This is not ready for production.
It is used as a tool for API debugging.


## Things to Know

Run-of-the-mill Symfony 4 project,
using the generated PHP lib from the OpenApi specs.

We mostly pipe requests and forms to the API and try to display the response in browsable HTML.

There's no javascript in the website, but we do use _Encore_ for asset compilation during development.


## Things of Note

This project exposes the need for API keys in addition to the JWT.


## Installation instructions

Clone this project, and run [composer](https://getcomposer.org/):

```shell script
git clone https://github.com/MieuxVoter/mv-api-client-symfony4.git
cd mv-api-client-symfony4
composer install
```

It may ask for php extra dependencies if you don't have them.

> todo: provide the php package list for debian at least

## Run locally

```shell script
symfony serve
```

if you have the [symfony do-it-all](https://symfony.com/download), or :

```shell script
bin/console server:run
```

> The latter is deprecated, so it will be removed at some point

### CLI Toolkit

```shell script
bin/console
```

## Build assets (CSS)

    yarn install
    npx encore

