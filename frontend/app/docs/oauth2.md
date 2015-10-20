# GLPi Plugin Directory API

## Audience

The target audience for this document is mainly constitued by :
  + GLPi Plugins users which may be
    + GLPi Developers (you might want to implement a check-for-updates button for your GLPi plugin, or an update alert, using this api per example)
    + but also Service/System administrators using GLPi if they want to script their queries to the GLPi Plugins service
  + Terminal fans using GLPi (you might want to code a script that fetches last updates of some plugins)
  + Other persons (you might want to develop a web-service that, per example, search through the plugin directory for x reason you have)

## Possible specific usages

The API allows you to :
  + Get informations on a specific GLPi Plugin
  + Get the list of all GLPi Plugins
  + Search Plain-Text through the GLPi Plugins
  + Get the list of "trending" Plugins (which were recently significantly downloaded)
  + Get the list of "popular" Plugins (which were downloaded more that any other one)
  + Get the list of "updated" Plugins (which were recently updated at the XML level)
  + Get the list of "new" Plugins (which were added recently to the catalogue)

## Glossary

### GLPi Plugin Directory

You are currently using the GLPi Plugins service.
The GLPi Plugins service has an open-source backend, which is called [glpi-plugin-directory](https://github.com/glpi-project/plugins), whose source-code is available [on Github](https://github.com/glpi-project/plugins). 

### API Endpoint

The GLPi Plugins API offers multiple endpoints, the term endpoint can be understood as "data source".
The API offers multiple type of data (plugin, author, tag, ..., plugin list, ..., and much more).
Each endpoint is associated with an URL, and each specific endpoint offers a data source,
available over HTTP.

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

## Pagination

Each endpoint declared in this document is mentionned with a `Paginated` boolean characteristic.

Anytime 'Paginated' is mentionned as true,  it means that this endpoint serves a collection, and also means that the request needs the X-Range Header to be specified with the start and end indexes specifying the range to take from the collection.

## Localization

The X-Lang header can be given with each request, mentionning a specific language code.
If no X-Lang header is given, or an unknown language code, the language will default to English.  
Language codes are conventionally two-letter codes on GLPi Plugins.
Example:

 + en
 + es
 + fr
 + ...

## Authorization

### What is Authorization and how is it done in GLPi Plugins ?

In order to use this API, you must be Authorized to do so.  
You're lucky, it's not very hard to get what is called an Access-Token,  
in order to use the GLPi Plugins API.

The GLPi Plugins API access, is limited by the authorization system of GLPi Plugins which was developped following as closely as possible [The OAuth 2 Authorization framework](https://tools.ietf.org/html/rfc6749) specification.  
The GLPi Plugins API is respectful to the OAuth2 standard for authorization over resources behind services over HTTP.

In our case [The OAuth 2 Authorization framework](https://tools.ietf.org/html/rfc6749) specification is used to provide an Authentication and Authorization system to the API.

### How does a typical session creation process looks like ?

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

Key             |          Value
----------------|----------------
URI             |     /api/oauth/authorize
`grant_type`    | client_crendentials (Client Credentials)
`client_id`     | a7nbqKopiTrLWtvgLTpO
`client_secret` | WLvUAipaR7dOUPuqqGSTrHapJd95n0djRxNlv3To
`scope`         | plugins plugins:search plugin:card plugin:star plugin:submit plugin:download tags tag authors author version message

The 4 last entries of this table are the distinct variables that were "URL-Encoded" into the long string that was previously splitted in the last paragraph.

### How does the reply looks like ?

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

Key             |          Value
----------------|----------------
`access_token`  | the access token you will use for your api usage
`token_type`    | The type of the token being given (Will be Bearer anytime)
`expires_in`    | The time period for which the access_token token is valid (3600 seconds here)

With the given access_token, you are able to make API requests during the number of seconds  
mentionned by `expires_in`. On GLPi Plugins the default `expires_in` value for the Access-Tokens  
we deliver is 3600, equivalent to an hour.

### What happens after Access-Token expiration

After the `expires_in` number of second happened, the server will  
reply to one of your requests a response like :

```http
HTTP/1.1 401 Unauthorized
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

{
  "error": "ACCESS_DENIED"
}
```

### Summary: how your app is going to stay authed

To summarize, all your app/script has to do, in order to stay authed on GLPi Plugins API, and keeping being able to make requests, is to detect the HTTP code that is sent at each response,  

This is an example scenario :

  1. Your app fetches an `access_token`, via HTTP, if it hasn't got anyone saved in it's storage
  2. Your app makes API requests for as long as it is possible
  3. Your app detects a 401 HTTP Response with ACCESS_DENIED as error code
  4. Your app calls a subroutine that uses HTTP to get a new Access Token with the client credentials (`client_id` and `client_secret`)
  5. Your app now saves the `access_token` in it's storage and use it to retry the request that failed
  6. Back to (2) with that new access_token as long as this one works too

## Endpoints

### Contributors

#### Contributor list

Key           |     Value
--------------|-------------
URL           |     /author
Method        |     GET
Description   |     List of known GLPi contributors
Paginated        |     true (answers 206 Partial Content or 200 if all the data is in the response)

##### Example usage (HTTP Session)

```http
GET /api/author HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
X-Lang: en
X-Range: 0-3
```

##### Example usage (cURL call)

```sh
curl -X  -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' -H 'X-Lang: en' -H 'X-Range: 0-3' http://plugins.glpi-project.com/api/author
```

##### Example response

```http
HTTP/1.1 206 Partial Content
Content-Type: application/json
Accept-Range: model 63
Content-Range: 0-3/63

[
    {
        "id": "3",
        "name": "Xavier Caillaud",
        "plugin_count": "41"
    },
    {
        "id": "49",
        "name": "Infotel",
        "plugin_count": "18"
    },
    {
        "id": "14",
        "name": "Walid Nouh",
        "plugin_count": "14"
    }
]
```

#### Contributor card

Key             |     Value
----------------|-------------
URL             |     /author/:id
Method          |     GET
Nature of Data  |     Descriptive Card of a GLPi Contributor
Paginated       |     false

##### Example usage (HTTP Session)

```http
GET /api/author/1 HTTP/1.1
Host: plugins.glpi-project.org
Authorization: Bearer yOuRAccesSTokeNhEre
Accept: application/json
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Authorization: Bearer youRAccesSTokeNhEre' http://plugins.glpi-project.com/api/author/1
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

{
  "id":"1",
  "name":"Julien Dombre",
  "plugin_count":"1"
}
```

### Plugins

#### Plugin list

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Plugin.  
This endpoint returns all the known plugins in the GLPi open-source community.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /plugin
Method           |     GET
Data Nature      |     The collection of all GLPi known plugins
Paginated        |     true (answers 206 Partial Content or 200 if all the data is in the response)

##### Example usage (HTTP Session)

```http
GET /api/plugin HTTP/1.1
Host: plugins.glpi-project.org
Authorization: Bearer yOuRAccesSTokeNhEre
Accept: application/json
X-Range: 0-14
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Authorization: Bearer youRAccesSTokeNhEre' -H 'Accept: application/json' -H 'X-Range: 0-14' http://plugins.glpi-project.com/api/plugin
```

##### Example reponse

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json
Content-Range: 0-14/111
Accept-Range: model 111

[
    {
        "id": "1",
        "name": "room",
        "key": "room",
        "logo_url": "https://forge.glpi-project.org/svn/room/logo.png",
        "xml_url": "https://forge.glpi-project.org/svn/room/room.xml?format=raw",
        "homepage_url": "https://forge.indepnet.net/projects/show/room",
        "download_url": "https://forge.indepnet.net/projects/room/files",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-08-17",
        "download_count": "2813",
        "note": 3.5,
        "short_description": "... (localized)",
        "authors": [
            {
                "id": "1",
                "name": "Julien Dombre"
            },
            {
                "id": "2",
                "name": "Pascal Marier-Dionne"
            }
        ],
        "versions": [
            {
                "num": "3.0.2",
                "compatibility": "0.80.7"
            },
            {
                "num": "3.0.1",
                "compatibility": "0.80"
            },
            {
                "num": "2.1",
                "compatibility": "0.72"
            },
            {
                "num": "1.0",
                "compatibility": "0.71"
            }
        ],
        "descriptions": [
            {
                "short_description": "...",
                "long_description": "...",
                "lang": "fr"
            },
            {
                "short_description": "...",
                "long_description": "...",
                "lang": "en"
            }
        ]
    },
    {
        "id": "2",
        "name": "Additional Alerts",
        "key": "additionalalerts",
        "logo_url": "https://raw.githubusercontent.com/InfotelGLPI/additionalalerts/master/additionalalerts.png",
        "xml_url": "https://raw.githubusercontent.com/InfotelGLPI/additionalalerts/master/additionalalerts.xml",
        "homepage_url": "https://github.com/InfotelGLPI/additionalalerts",
        "download_url": "https://github.com/InfotelGLPI/additionalalerts/releases",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-10-20",
        "download_count": "24018",
        "note": 0,
        "short_description": "... (localized)",
        "authors": [
            {
                "id": "3",
                "name": "Xavier Caillaud"
            }
        ],
        "versions": [
            {
                "num": "1.7.0",
                "compatibility": "0.85"
            },
            {
                "num": "1.6.0",
                "compatibility": "0.84"
            },
            {
                "num": "1.6.1",
                "compatibility": "0.84"
            },
            {
               "...": "..."
            }
        ],
        "descriptions": [
            {
                "short_description": "...",
                "long_description": "...",
                "lang": "fr"
            },
            {
                "short_description": "...",
                "long_description": "...",
                "lang": "en"
            }
        ]
    },
    {
      "id": "3",
      "...": "..."
    },
    {
      "...": "..."
    }
]
```

#### Contributor Plugin List

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Plugin.  
This endpoint returns all the known plugins a specific contributor authored on.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /author/&lt;id&gt;/plugin
Method           |     GET
Data Nature      |     The collection of all GLPi known plugins
Paginated        |     true (answers 206 Partial Content or 200 if all the data is in the response)

##### Example usage (HTTP Session)

```http
GET /api/author/3/plugin HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' http://plugins.glpi-project.com/api/author/3/plugin
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

[
    {
        "id": "2",
        "name": "Additional Alerts",
        "key": "additionalalerts",
        "logo_url": "https://raw.githubusercontent.com/InfotelGLPI/additionalalerts/master/additionalalerts.png",
        "xml_url": "https://raw.githubusercontent.com/InfotelGLPI/additionalalerts/master/additionalalerts.xml",
        "homepage_url": "https://github.com/InfotelGLPI/additionalalerts",
        "download_url": "https://github.com/InfotelGLPI/additionalalerts/releases",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-10-20",
        "download_count": "24020",
        "note": 0,
        "short_description": "... (localized)",
        "versions": [
            {
                "num": "1.7.0",
                "compatibility": "0.85"
            },
            {
                "num": "1.6.1",
                "compatibility": "0.84"
            },
            {
                "num": "1.6.0",
                "compatibility": "0.84"
            },
            {
               "...": "..."
            }
        ],
        "authors": [
            {
                "id": "3",
                "name": "Xavier Caillaud"
            }
        ],
        "descriptions": [
            {
                "short_description": "...",
                "long_description": "...",
                "lang": "fr"
            },
            {
                "short_description": "...",
                "long_description": "...",
                "lang": "en"
            }
        ]
    },
    {
        "id": "3",
        "name": "IP Report",
        "key": "addressing",
        "logo_url": "https://forge.glpi-project.org/svn/addressing/addressing.png",
        "xml_url": "https://forge.glpi-project.org/svn/addressing/addressing.xml?format=raw",
        "homepage_url": "https://forge.glpi-project.org/projects/addressing",
        "download_url": "https://forge.glpi-project.org/projects/addressing/files",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-10-20",
        "download_count": "37813",
        "note": 4,
        "short_description": "... (localized)",
        "versions": [
            {
                "num": "2.2.0",
                "compatibility": "0.85.3"
            },
            {
                "num": "2.1.0",
                "compatibility": "0.84"
            },
            {
                "num": "2.0.1",
                "compatibility": "0.83.3"
            },
            {
               "...": "..."
            }
        ],
        "authors": [
            {
                "id": "4",
                "name": "Gilles Portheault"
            },
            {
                "id": "3",
                "name": "Xavier Caillaud"
            },
            {
                "id": "5",
                "name": "Remi Collet"
            },
            {
                "id": "6",
                "name": "Nelly Mahu-Lasson"
            }
        ],
        "descriptions": [
            {
                "short_description": "...",
                "long_description": "...",
                "lang": "fr"
            },
            {
                "short_description": "...",
                "long_description": "...",
                "lang": "en"
            }
        ]
    },
    {"id": "4"},
    {"...": "..."}
]
```

#### Trending Plugins List

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Plugin.  
This endpoint returns a top 10 of all the plugins that were recently significantly downloaded.
There is a output difference between this endpoint and the "Plugin list" one,  
This endpoint gives only summaries of each plugin, containing id, name, key,  
total number of downloads and number of recent downloads (within the last month)

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /plugin/trending
Method           |     GET
Data Nature      |     The collection of all GLPi trending plugins
Paginated        |     false

##### Example usage (HTTP Session)

```http
GET /api/plugin/trending HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' http://plugins.glpi-project.com/api/plugin/trending
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

[
    {
        "id": "74",
        "name": "OCS Inventory NG",
        "key": "ocsinventoryng",
        "download_count": "47289",
        "recent_downloads": "1892"
    },
    {
        "id": "8",
        "name": "Fusioninventory for GLPI (ex Tracker)",
        "key": "fusioninventory",
        "download_count": "1200",
        "recent_downloads": "1127"
    },
    {
        "id": "81",
        "name": "Dashboard",
        "key": "dashboard",
        "download_count": "19117",
        "recent_downloads": "1072"
    },
    {"...": "..."}
]
```

#### Popular Plugins List

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Plugin.  
This endpoint returns a top 10 of all the plugins that were the most significantly downloaded.
There is a output difference between this endpoint and the "Plugin list" one,  
This endpoint gives only summaries of each plugin, containing id, name, key,  
total number of downloads, number of votes, and current average note.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /plugin/popular
Method           |     GET
Data Nature      |     The collection of all GLPi popular plugins
Paginated        |     false

##### Example usage (HTTP Session)

```http
GET /api/plugin/popular HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' http://plugins.glpi-project.com/api/plugin/popular
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

[
    {
        "id": "45",
        "name": "OCS Import",
        "key": "massocsimport",
        "download_count": "95104",
        "n_votes": "5",
        "note": 2.9
    },
    {
        "id": "23",
        "name": "reports",
        "key": "reports",
        "download_count": "76253",
        "n_votes": "0",
        "note": null
    },
    {
        "id": "38",
        "name": "PDF",
        "key": "pdf",
        "download_count": "50378",
        "n_votes": "7",
        "note": 4.35714
    },
    {"...": "..."}
]
```

#### Updated Plugins List

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Plugin.  
This endpoint returns a top 10 of all the plugins that were recently updated at the XML level.  
This endpoint gives only summaries of each plugin, containing id, name, key,  
and the date of the last update that occured on that plugin at the XML level.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /plugin/trending
Method           |     GET
Data Nature      |     The collection of all GLPi popular plugins
Paginated        |     false

##### Example usage (HTTP Session)

```http
GET /api/plugin/trending HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' http://plugins.glpi-project.com/api/plugin/trending
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

[
    {
        "id": "2",
        "key": "additionalalerts",
        "name": "Additional Alerts",
        "date_updated": "2015-10-20"
    },
    {
        "id": "3",
        "key": "addressing",
        "name": "IP Report",
        "date_updated": "2015-10-20"
    },
    {
        "id": "9",
        "key": "appliances",
        "name": "Appliances Inventory",
        "date_updated": "2015-10-20"
    },
    {"...": "..."}
]
```

#### New Plugins List

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Plugin.  
This endpoint returns a top 10 of all the plugins that were recently added in the GLPi Plugins Directory. 
This endpoint gives only summaries of each plugin, containing id, name, key,  
and the date of insertion of that plugin in the GLPi Plugins Directory.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /plugin/new
Method           |     GET
Data Nature      |     top 10 of all the plugins that were recently added in the GLPi Plugins Directory
Paginated        |     false

##### Example usage (HTTP Session)

```http
GET /api/plugin/new HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' http://plugins.glpi-project.com/api/plugin/new
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

[
    {
        "id": "117",
        "name": "Seasonality",
        "date_added": "2015-10-20",
        "key": "seasonality"
    },
    {
        "id": "116",
        "name": "Processmaker",
        "date_added": "2015-10-09",
        "key": "processmaker"
    },
    {
        "id": "114",
        "name": "Simcard",
        "date_added": "2015-10-03",
        "key": "simcard"
    },
    {"...": "..."}
]
```

#### Plugin card

This endpoint's data source yields a JSON serialized objects which describes a single Plugin.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /plugin/&lt;key&gt;
Parameter #1     |     `key`: the key of the plugin you request card of
Method           |     GET
Data Nature      |     Descriptive card of a single plugin
Paginated        |     false

##### Example usage (HTTP Session)

```http
GET /api/plugin/mantis HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' http://plugins.glpi-project.com/api/plugin/mantis
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

{
    "id": "82",
    "name": "MantisBT",
    "key": "mantis",
    "logo_url": "https://forge.indepnet.net/svn/mantis/mantis.png",
    "xml_url": "https://forge.glpi-project.org/svn/mantis/mantis.xml?format=raw",
    "homepage_url": "https://github.com/TECLIB/mantis",
    "download_url": "https://github.com/TECLIB/mantis/releases",
    "issues_url": "",
    "readme_url": "",
    "license": "GPLv3",
    "date_added": "2014-07-01",
    "date_updated": "2015-08-17",
    "download_count": "352",
    "note": 0,
    "n_votes": "0",
    "descriptions": [
        {
            "short_description": "...",
            "long_description": "...",
            "lang": "fr"
        },
        {
            "short_description": "...",
            "long_description": "...",
            "lang": "en"
        }
    ],
    "authors": [
        {
            "id": "24",
            "name": "TECLIB'"
        }
    ],
    "versions": [
        {
            "num": "1.0",
            "compatibility": "0.84"
        }
    ],
    "screenshots": [],
    "tags": [
        {
            "key": "helpdesk",
            "tag": "Helpdesk",
            "lang": "fr"
        },
        {
            "key": "donn-es",
            "tag": "donn\u00e9es",
            "lang": "fr"
        },
        {
            "key": "export",
            "tag": "Export",
            "lang": "fr"
        },
        {
            "key": "helpdesk",
            "tag": "Helpdesk",
            "lang": "en"
        },
        {
            "key": "data",
            "tag": "data",
            "lang": "en"
        },
        {
            "key": "export",
            "tag": "Export",
            "lang": "en"
        }
    ]
}
```

### Tags

#### Tag list

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Tag.  
This endpoint returns all the known tags the GLPi Plugins service know about.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /tags
Method           |     GET
Data Nature      |     The collection of known plugin tags
Paginated        |     true

##### Example usage (HTTP Session)

```http
GET /api/author/tags HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
X-Range: 0-14
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' -H 'X-Range: 0-14' http://plugins.glpi-project.com/api/tags
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json
Accept-Range:model 160
Content-Range: 0-14/160

[
    {
        "key": "inventory",
        "tag": "Inventory",
        "lang": "en",
        "plugin_count": "34"
    },
    {
        "key": "helpdesk",
        "tag": "Helpdesk",
        "lang": "en",
        "plugin_count": "19"
    },
    {
        "key": "management",
        "tag": "Management",
        "lang": "en",
        "plugin_count": "18"
    },
    {"...": "..."}
]
```

#### Tag plugin list

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Plugin.  
This endpoint returns all the known plugins in the GLPi open-source community which have
the requested tag.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /tag/&lt;key&gt;/plugin
Parameter #1     |     `key` : key of the tag you request associated plugins of
Method           |     GET
Data Nature      |     The collection of all individual plugins which have the specified tag
Paginated        |     true (answers 206 Partial Content or 200 if all the data is in the response)

##### Example usage (HTTP Session)

```http
GET /api/tag/management/plugin HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
X-Range: 0-14
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' -H 'X-Range: 0-14' http://plugins.glpi-project.com/api/tag/management/plugin
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Accept-Range: model 18
Content-Range: 0-14/18

[
    {
        "id": "4",
        "name": "Racks / Bays Management",
        "key": "racks",
        "logo_url": "https://raw.githubusercontent.com/InfotelGLPI/racks/master/racks.png",
        "xml_url": "https://raw.githubusercontent.com/InfotelGLPI/racks/master/racks.xml",
        "homepage_url": "https://github.com/InfotelGLPI/racks",
        "download_url": "https://github.com/InfotelGLPI/racks/releases",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-10-09",
        "download_count": "22966",
        "note": 0,
        "short_description": "Bay (racks) management. This plugin allows you to create bays. Manage the placement of your materials in your bays. And so know the space and its power consumption and heat dissipation.",
        "versions": [
            {
                "num": "1.6.1",
                "compatibility": "0.90"
            },
            {
                "num": "1.6.0",
                "compatibility": "0.90"
            },
            {
                "num": "1.5.0",
                "compatibility": "0.85"
            },
            {"...", "..."}
        ],
        "authors": [
            {
                "id": "3",
                "name": "Xavier Caillaud"
            },
            {
                "id": "49",
                "name": "Infotel"
            }
        ]
    },
    {
        "id": "5",
        "name": "Entities Management",
        "key": "manageentities",
        "logo_url": "https://forge.indepnet.net/svn/manageentities/manageentities.png",
        "xml_url": "https://raw.githubusercontent.com/InfotelGLPI/manageentities/master/manageentities.xml",
        "homepage_url": "https://forge.indepnet.net/projects/manageentities",
        "download_url": "https://forge.indepnet.net/projects/manageentities/files",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-08-17",
        "download_count": "16794",
        "note": 0,
        "short_description": "Entities management. This plugin allows you to manage entities. Link with documents, contacts, contracts. You can also create intervention reports and do contract management of your entities",
        "versions": [
            {
                "num": "1.8.1",
                "compatibility": "0.83.3"
            },
            {
                "num": "1.8.0",
                "compatibility": "0.83"
            },
            {
                "num": "1.7.0",
                "compatibility": "0.80"
            },
            {"...": "..."}
        ],
        "authors": [
            {
                "id": "3",
                "name": "Xavier Caillaud"
            }
        ]
    },
    {
        "id": "7",
        "name": "Accounts Inventory",
        "key": "accounts",
        "logo_url": "https://raw.githubusercontent.com/InfotelGLPI/accounts/master/wiki/accounts.png",
        "xml_url": "https://raw.githubusercontent.com/InfotelGLPI/accounts/master/accounts.xml",
        "homepage_url": "https://github.com/InfotelGLPI/accounts",
        "download_url": "https://github.com/InfotelGLPI/accounts/releases",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-10-09",
        "download_count": "19517",
        "note": 0,
        "short_description": "... (localized)",
        "versions": [
            {
                "num": "2.1.0",
                "compatibility": "0.90"
            },
            {
                "num": "2.1.1",
                "compatibility": "0.90"
            },
            {
                "num": "2.0.1",
                "compatibility": "0.85.3"
            },
            {"...": "..."}
        ],
        "authors": [
            {
                "id": "3",
                "name": "Xavier Caillaud"
            },
            {
                "id": "49",
                "name": "Infotel"
            }
        ]
    },
    {"...": "..."}
]
```

#### Most used tags

This endpoint's data source yields a JSON Array containing a list of JSON serialized objects.  
Each of these objects describes a single Tag.  
This endpoint returns a top-10 of all the most used tags.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /tags/top
Method           |     GET
Data Nature      |     top-10 of all the most used tags
Paginated        |     false

##### Example usage (HTTP Session)

```http
GET /api/tags/top HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' -H 'X-Range: 0-14' http://plugins.glpi-project.com/api/tags/top
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

[
    {
        "id": "4",
        "name": "Racks / Bays Management",
        "key": "racks",
        "logo_url": "https://raw.githubusercontent.com/InfotelGLPI/racks/master/racks.png",
        "xml_url": "https://raw.githubusercontent.com/InfotelGLPI/racks/master/racks.xml",
        "homepage_url": "https://github.com/InfotelGLPI/racks",
        "download_url": "https://github.com/InfotelGLPI/racks/releases",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-10-09",
        "download_count": "22966",
        "note": 0,
        "short_description": "Bay (racks) management. This plugin allows you to create bays. Manage the placement of your materials in your bays. And so know the space and its power consumption and heat dissipation.",
        "versions": [
            {
                "num": "1.6.1",
                "compatibility": "0.90"
            },
            {
                "num": "1.6.0",
                "compatibility": "0.90"
            },
            {
                "num": "1.5.0",
                "compatibility": "0.85"
            },
            {"...", "..."}
        ],
        "authors": [
            {
                "id": "3",
                "name": "Xavier Caillaud"
            },
            {
                "id": "49",
                "name": "Infotel"
            }
        ]
    },
    {
        "id": "5",
        "name": "Entities Management",
        "key": "manageentities",
        "logo_url": "https://forge.indepnet.net/svn/manageentities/manageentities.png",
        "xml_url": "https://raw.githubusercontent.com/InfotelGLPI/manageentities/master/manageentities.xml",
        "homepage_url": "https://forge.indepnet.net/projects/manageentities",
        "download_url": "https://forge.indepnet.net/projects/manageentities/files",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-08-17",
        "download_count": "16794",
        "note": 0,
        "short_description": "Entities management. This plugin allows you to manage entities. Link with documents, contacts, contracts. You can also create intervention reports and do contract management of your entities",
        "versions": [
            {
                "num": "1.8.1",
                "compatibility": "0.83.3"
            },
            {
                "num": "1.8.0",
                "compatibility": "0.83"
            },
            {
                "num": "1.7.0",
                "compatibility": "0.80"
            },
            {"...": "..."}
        ],
        "authors": [
            {
                "id": "3",
                "name": "Xavier Caillaud"
            }
        ]
    },
    {
        "id": "7",
        "name": "Accounts Inventory",
        "key": "accounts",
        "logo_url": "https://raw.githubusercontent.com/InfotelGLPI/accounts/master/wiki/accounts.png",
        "xml_url": "https://raw.githubusercontent.com/InfotelGLPI/accounts/master/accounts.xml",
        "homepage_url": "https://github.com/InfotelGLPI/accounts",
        "download_url": "https://github.com/InfotelGLPI/accounts/releases",
        "issues_url": "",
        "readme_url": "",
        "license": "GPL v2+",
        "date_added": "2009-08-07",
        "date_updated": "2015-10-09",
        "download_count": "19517",
        "note": 0,
        "short_description": "... (localized)",
        "versions": [
            {
                "num": "2.1.0",
                "compatibility": "0.90"
            },
            {
                "num": "2.1.1",
                "compatibility": "0.90"
            },
            {
                "num": "2.0.1",
                "compatibility": "0.85.3"
            },
            {"...": "..."}
        ],
        "authors": [
            {
                "id": "3",
                "name": "Xavier Caillaud"
            },
            {
                "id": "49",
                "name": "Infotel"
            }
        ]
    },
    {"...": "..."}
]
```

#### Tag card

This endpoint's data source yields a JSON serialized objects which describes a single Tag.

Key              |     Value
-----------------|-----------------------------------------------
URL              |     /tags/&lt;key&gt;
Parameter #1     |     `key`: the key of the tag you request card of
Method           |     GET
Data Nature      |     Descriptive card of a single tag
Paginated        |     false

##### Example usage (HTTP Session)

```http
GET /api/tags/management HTTP/1.1
Host: plugins.glpi-project.org
Accept: application/json
Authorization: Bearer yOuRAccesSTokeNhEre
```

##### Example usage (cURL call)

```sh
curl -X GET -H 'Accept: application/json' -H 'Authorization: Bearer youRAccesSTokeNhEre' -H 'X-Range: 0-14' http://plugins.glpi-project.org/api/tags/management
```

##### Example response

```http
HTTP/1.1 200 OK
Server: Apache/2.4.10 (Ubuntu)
Content-Type: application/json

{
    "key": "management",
    "tag": "Management",
    "lang": "en"
}
```