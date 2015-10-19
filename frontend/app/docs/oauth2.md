# GLPi Plugin Directory API

## Audience

The target audience for this document is mainly constitued by :
  + GLPi Plugin Directory users which may be
    + Service/System administrators using GLPi
    + GLPi Developers (you might want to implement a check-for-updates button for your GLPi plugin, or an update alert, using this api per example)
  + Terminal fans (you might want to code a script that fetches last updates of some plugins)
  + Other persons (you might want to develop a web-service that, per example, search through the plugin directory for x reason you have)

## Usage made

The usage that is made of the API can be composed of various tasks,
but the API can be useful to develop your own terminal (shell.) scripts,
or for services you want to develop using our API.



## Authorization

In order to use this API, you must be Authorized to do so.
You're lucky, it's not very hard to get Authorized to use GLPi Plugins API.

The GLPi Plugins API access, is limited by the authorization system of GLPi Plugins which was developped according to [The OAuth 2 Authorization framework](https://tools.ietf.org/html/rfc6749).
The GLPi Plugins API is respectful to the OAuth2 standard for authorization over resources between behind services over HTTP.

In our case the OAuth2 framework is used to provide an Authentication and Authorization system to the API.

## Glossary

### GLPi Plugin Directory

You are currently using the GLPi Plugins service.
The GLPi Plugins service has an open-source backend, which is called [glpi-plugin-directory](https://github.com/glpi-project/plugins), whose source-code is available [on Github](https://github.com/glpi-project/plugins).

### API Endpoint

The GLPi Plugins API offers multiple endpoints, the term endpoint can be understood as "data source".
The API offers multiple kind of data (plugin, author, tag, ..., plugin list, ..., and much more).
Each final url, specified in this document, as the url of an endpoint, is the URI that represents this endpoint, or data source, over HTTP. One endpoint is often related to one specific type of data (to give an example /plugin/room will send description of the 'room' plugin over HTTP when requested, if Authorization was previously given using OAuth2).

### OAuth2 Application / API Key

You, as a registered user of GLPi Plugins, are allowed to create/delete OAuth2 Applications, each of them associated with one OAuth2 API Key. The terms OAuth 2 Application and API Key are often OK to be confused.

### OAuth2 Grant

When requesting an Access-Token, you are using a Grant supported by OAuth2 which are :
  + Authorization Code Grant
  + Implicit Grant
  + Resource Owner Password Credentials Grant
  + Client Credentials Grant
  + Refresh Token Grant

### OAuth2 Client-Credentials Grant

In fact, when using this API, you will essentially only be using the Client-Credentials Grant.
The Client-Credentials Grant needs two informations to be satisfied, client_id, and client_secret.
Concerning your OAuth2 App/API Key, those two credentials are given to you in the "API Keys" section of your GLPi Plugins User Panel, where you can create, view, and edit API Keys/Oauth2 Apps for your usage of the API.

### OAuth2 Access-Token

When you used the OAuth2 Client-Credentials Grant to request usage of the API, what is going to be given to you in case of success, is an Access-Token, that will be given by you or your app/script/client with each of the multiple HTTP requests you will make to our API Service Endpoints.