
![MieuxVoter](./public/mv-logo.png)

A client for MieuxVoter's OpenApi specifications focused on _accessibility_.

- experimental
- compatibility with CLI/vocal browsers (that means _no javascript_)
- no database (only PHP sessions and external OpenAPI calls)
- not storing the password, but a perishable token (JWT) for the OpenApi


## Features

- [x] Majority Judgment Polls
- [x] Consumable Invitation Links
- [x] Designed for keyboard usage

## Overview

- Symfony 4
- Spectre & SCSS
- No Javascript
- No Database (forwarding calls to OpenAPI)

## Roadmap

- [ ] Send Invitation Links by Email
- [ ] OpenGraph
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


## Build assets (CSS)

    yarn install
    npx encore

