
A client for MieuxVoter's OpenApi specifications focused on accessibility.

- experimental
- compatibility with CLI/vocal browsers (that means _no javascript_)
- no database (only PHP sessions)
- not storing the password, but a perishable token (JWT) for the OpenApi


## Features

- [x] Browse Public Polls
- [x] Create a Poll
- [x] Participate to a Public Poll
- [x] Participate to a Private Poll
- [ ] View Results of a Poll
- [ ] At least *some* Resilience


## Work in Progress

This is not ready for production.
It is used as a tool for API debugging.


## Things to Know

Run-of-the-mill Symfony 4 project,
using the generated PHP lib from the OpenApi specs.

We mostly pipe requests and forms to the API and try to display the response in browsable HTML.

There's no javascript in the website, but we do use Encore for asset compilation during development.


## Things of Note

This exposes the need for API keys.

