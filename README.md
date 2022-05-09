
![MieuxVoter](./public/mv-logo.png)

A client for MieuxVoter's OpenApi specifications focused on _accessibility_.

- experimental (nearing the prototype phase)
- compatibility with CLI/vocal browsers (that means _no javascript_)
- no database (only PHP sessions and external OpenAPI calls)
- not storing the password, but a perishable token (JWT) for the OpenApi


## Features

- [x] Majority Judgment Polls
- [x] Designed for blazing fast keyboard usage
- [x] Inclusive Design _(hopefully)_
- [x] _Web0_ Compatibility
    - [x] No JavaScript
    - [x] Graceful degradation without CSS


## Roadmap

- [ ] Acquisition
    - [x] Consumable Invitation Links
    - [ ] Invitations Management
    - [ ] Send Invitation Links by Email
- [ ] Compatibility
    - [ ] OpenGraph
    - [ ] Proxy pass IP (upstream)
- [ ] Defense
    - [ ] Security Review (help wanted)
    - [ ] Spam Countermeasures
    - [ ] Community Self Moderation
        - [ ] Game Design (upstream)
- [ ] Liquidity
    - [ ] Fellowships (suggestions)
    - [ ] Delegations (default grades)
- [ ] User Experience
    - [ ] OAS Token Automatic Renewal _(we don't store the password !?)_
    - [ ] Graceful `40X`/`50X` errors

> Some will take years.  Fell free to work on anything, be it in this list or not.
> We welcome community contributions.

## Work in Progress

This is not ready for production.
It is used as a tool for API debugging.


## Stack Overview

- PHP
- Symfony 4
- SCSS & Spectre bootstrap
- No Javascript (except in dev for building assets and toolbar)
- No Database (forwarding calls to OpenAPI, some in PHP sessions)


## Things to Know

Run-of-the-mill Symfony 4 project,
using the generated PHP lib from the OpenApi specs.

We mostly pipe requests and forms to the API and try to display the response in browsable HTML.

By design, there's no javascript in the website, but we do use _Encore_ for asset compilation during development.


## Things of Note

This project exposes the need for API keys in addition to the JWT.


## Installation instructions

Clone this project:

```shell script
git clone https://github.com/MieuxVoter/mv-api-client-symfony4.git
cd mv-api-client-symfony4
```

Run [composer](https://getcomposer.org/):

```shell script
composer install
```

It may ask for php extra dependencies if you don't have them.

> todo: provide the package lists for some OSes at least.
> (You can look into `docker/php/Dockerfile` for a debian sample)


## Run locally

You'll need the [symfony do-it-all](https://symfony.com/download), and then :
```shell script
symfony serve
```

It'll use a random port, but you can choose one by running instead: 

```shell script
symfony serve --verbose --port 8000
```

Finally, visit https://localhost:8000/


### CLI Toolkit

```shell script
bin/console
```

Lots of tools in here, and we can add our own as well.


## Build assets (CSS)

`node-sass` does not play well with node > 14 for now.
We use `nvm` to set the `node` version:

    nvm install 14
    nvm alias default 14

Then you can:

    yarn install

And then run the watcher:

    npx encore dev --watch

To generate static files for prod:

    npx encore production


# Deploy

Create `.env.local` or `.env.prod.local` with your secrets.
You can copy `.env` as a boilerplate:

    cp .env .env.local
    vi .env.local

Once it's done, you're all set for docker compose:

    docker-compose up

