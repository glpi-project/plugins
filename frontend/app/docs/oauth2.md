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

### What is Authorization and how it is done in GLPi Plugins ?

In order to use this API, you must be Authorized to do so.
You're lucky, it's not very hard to get Authorized to use the GLPi Plugins API.

The GLPi Plugins API access, is limited by the authorization system of GLPi Plugins which was developped according to [The OAuth 2 Authorization framework](https://tools.ietf.org/html/rfc6749) specification.  
The GLPi Plugins API is respectful to the OAuth2 standard for authorization over resources behind services over HTTP.

In our case [The OAuth 2 Authorization framework](https://tools.ietf.org/html/rfc6749) specification is used to provide an Authentication and Authorization system to the API.

### How does a typical session creation looks like

What you need is an Access-Token, for this purpose, we are going to request a Client-Credentials Grant to the API.  
We are using the /oauth/authorize endpoint of glpi-plugin-directory which is the OAuth2 authorization endpoint.  
This is the kind of HTTP request that your script/webapp/thing needs to do in order to get an Access-Token.

```http
GET /api/oauth/authorize HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials
&client_id=a7nbqKopiTrLWtvgLTpO
&client_secret=WLvUAipaR7dOUPuqqGSTrHapJd95n0djRxNlv3To
&scope=plugins+plugins%3Asearch+plugin%3Acard+plugin%3Astar+plugin%3Asubmit+plugin%3Adownload+tags+tag+authors+author+version+message
```
 
(Please note the line starting by "grant_type[...]" has been truncated in multiple parts here, in fact you never add carriage returns in the body when it is x-www-form-urlencoded data, but this is for readability.  
The original text is written in a single line, without any carriage return)

### What happened ?

/api/oauth/authorize is the only POST endpoint we serve that doesn't expect the client to send JSON data in the HTTP Request body. This endpoint expect the traditional www-form-urlencoded string for encoded form values.

Let's analyze is are the real content of this request

Key           |          Value
--------------|----------------
URI           |     /api/oauth/authorize
grant_type    | client_crendentials (Client Credentials)
client_id     | a7nbqKopiTrLWtvgLTpO
client_secret | WLvUAipaR7dOUPuqqGSTrHapJd95n0djRxNlv3To
scope         | plugins plugins:search plugin:card plugin:star plugin:submit plugin:download tags tag authors author version message

The 4 last entries of this table are the distinct variables that were "URL-Encoded" into the long string that was previously splitted in the last paragraph.

### What does the reply looks like ?

After that HTTP-Request was sent, there is the HTTP-Response if everything went all right :

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

{
  "access_token": "xKGGWXhTCwJByv4Qp9gkO0wKmTB4pZdHnPe9ZVz2",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

This is a JSON object. Let's analyze that data too:

Key           |          Value
--------------|----------------
access_token  | the access token you will use for you api calls
token_type    | The type of the token being given (Will be Bearer anytime)
expires_in    | The time period for which the access_token token is valid (3600 seconds here)

With the given access_token, you are able to make API requests during the next hour.

### What happens after Access-Token expiration

After that period, the server will reply you this :

```http
HTTP/1.1 401 Unauthorized
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

{
  "error": "ACCESS_DENIED"
}
```

### Summary, how your app is going to stay authed

To summarize, all your app/script has to do, in order to stay authed on GLPi Plugins API, and keeping being able to make requests, is to detect the HTTP code that is sent at each response,  

This is an example scenario :

  1. Your app fetches an access_token, via HTTP, if it hasn't got anyone saved in it's storage
  2. Your app makes API requests for as long as it is possible
  3. Your app detects a 401 HTTP Response with ACCESS_DENIED as error code
  4. Your app subroutine uses HTTP to get a new Access Token with the client credentials (client_id and client_secret)
  5. Your app is now able to retry the request that failed
  6. Back to (2) with that new access_token as long as this one works too


## Endpoints

### Contributors

#### Contributor list

Key          |     Value
-------------|-------------
URL          |     /author
Method       |     GET
Description  |     List of known GLPi contributors
Paginated    |     true

#### Example usage (HTTP Session)

```http
GET /api/author HTTP/1.1
Host: plugins.glpi-project.org
Authorization: Bearer yOuRAccesSTokeNhEre
X-Lang: en
X-Range: 0-14
```

#### Example usage (cURL call)

```sh
curl -X GET -H 'Authorization: Bearer youRAccesSTokeNhEre' -H 'X-Lang: en' -H 'X-Range: 0-14' http://plugins.glpi-project.com/api/author
```

### Plugins

### Tags

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