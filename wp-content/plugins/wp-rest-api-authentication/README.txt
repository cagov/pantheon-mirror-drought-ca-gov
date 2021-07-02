=== WordPress REST API Authentication ===
Contributors: cyberlord92
Tags: rest-api, jwt(JSON Web Token), wp rest api access,wp-json, oauth
Requires at least: 3.0.1
Tested up to: 5.7.2
Stable tag: 1.5.2
Requires PHP: 5.6
License: MIT/Expat
License URI: https://docs.miniorange.com/mit-license

Protect your WordPress REST APIs from public or unauthorized access. Secure it with WordPress REST API Authentication. [24/7 SUPPORT]


== Description ==

WordPress REST API Authentication secures rest API access for unauthorized users or from public access using API Key Authentication, JWT Token Authentication, Basic Authentication, OAuth 2.0 Authentication or Third Party OAuth 2.0 provider's Token authentication Methods. Also, It allows you to login and register to WordPress REST APIs using any authentication method from the other applications like mobile, desktop application etc.
It also extends the JWT(JSON Web tokens) authentication to validate the REST APIs access on the basis of the JWT token. If the JWT validation is successful, it will allow to access the requested resource else if the JWT validation is unsuccessful then it will not allow to access the requested resource.


= Usecases =

* Block the public access to your WordPress REST APIs like /pages, /posts
* Authorize the API access for all users with authentication methods provided in the plugin
* Integrate with Third party plugin APIs like Woocommerce and secure/ access their APIs
* Authenticate WordPress APIs with the token / jwt token from other IDPs
* Login and register into Mobile or other client applications using REST APIs

The plugin provides an interface for applications to interact with your WordPress REST APIs by sending and receiving data as JSON (JavaScript Object Notation) objects. Also, It provides user friendly user interface of the plugin to configure the methods and implement it.

There are multiple ways to secure a REST APIs e.g. basic auth, OAuth, JWT token etc. but one thing is sure that RESTful APIs should be stateless – so request authentication/authorization should not depend on cookies or sessions. Instead, each API request should come with some sort authentication credentials which must be validated on the server for each and every request.

== REST API Authentication Methods ==

= Basic Authentication =

*	If you want to protect your WP REST APIs(eg. post, pages and other REST APIs) with users login credentials or client-id:client-secret, then you can opt for the basic authentication method. It is recommended that you should use this method on HTTPS or secure socket layer.
*	Username & Password Authentication - This method authenticate the REST APIs by using username and passwords in the authorization header with the form of base64 encoded.
*	Client-ID & Client-Secret Authentication - This method authenticate the REST APIs by using client credenrials in the authorization header with the form of base64 encoded. Client credenrials are provided by the plugin itself.

= API Key Authentication (Authentication with Readonly Generated Key ) =

*	If you want to protect your WP REST APIs(eg. post, pages and other REST APIs) from unauthenticated users but you don’t want to share users login credentials or client id, secret to authenticate the REST API, then you can use API Key authentication, which will generate a random authentication key for you. Using this key, you can authenticate any REST API on your site.

= JWT Authentication / JSON Web Tokens Authentication =

*	If you are looking to protect your REST APIs using the JWT token and if you do not have any third party provider/identity provider that issues the JWT token, then you should go for JWT Authentication method. In this case, our WordPress REST API Authentication itself issues the JWT token and works as an API Authenticator to protect your REST APIs. If the JWT validation is done successfully, it will allow to access the requested resource else if the JWT validation is unsuccessful then it will not allow to access the requested resource.

= OAuth 2.0 Authentication =

*	If you are looking for protecting your REST APIs using the access-token and at the same time you do not have any third party provider/identity provider, then you should go for OAuth 2.0 Authentication method. In this scenario, our WordPress REST API Authentication works as both OAuth Server and API Authenticator to protect your REST APIs.
*	Client Credentials Grant - This method uses the OAuth 2.0 protocol with Client Credentials grant to authenticate the REST APIs, the plugin will provide a time base token based on the client credenrials authentication and you can use it to register a user into WordPress by passing the token in the authorization header of user create API.
*	Password Grant - This method uses the OAuth 2.0 protocol with Password grant to authenticate the REST APIs, the plugin will provide a time base token based on the user credenrials authentication and you can use it to login into WordPress by passing the token into authorization header of every API request.

= API Authentication for Third Party OAuth 2.0 Provider( using Introspection Endpoint / User Info Endpoint ) =

*	If you are looking for protecting/restricting access to your WP REST APIs using your OAuth Provider/Identity provider, then you should go for Third Party Provider Authentication method. It would be helpful to authenticate the WordPress REST APIs with different platforms token like JWT token of Google, AWS Cognito, Auth0, miniOrange, firebase, amazon, Apple etc and access token of facebook, Okta etc.


== FEATURES ==

*  Supports Basic Authentication, API Key Authentication, OAuth 2.0 Authentication, JWT Token Authentication, Third Party OAuth 2.0 Provider Token Authentication methods
*  Global or Universal API Key & User specific API Keys for authentications
*  HMAC encryption & User specific Client credentials with the Basic authentication
*  Token endpoint to retrive user specific JWT Token
*  Signature validation using HSA & RSA Signing
*  Custom Token Expiry
*  Provides the admin privilege's client ID and client secret
*  Provides the Time base Access token or JWT token
*  WordPress Login using Access token or JWT token
*  Authenticate WordPress REST APIs with token (access token / jwt token) provided by your OAuth Provider ( Third Party Provider )
*  User's WordPress Role & Capability  based access to WordPress posts, pages etc
*  WooCommerce API Authentication
*  Custom & Third Party plugin's API Authentication. 
*  Allow or Deny public access to your APIs as per requirement
*  Postman Samples for each Authentication method to test the APIs access with the Postman application


== INTEGRATION ==

= WooCommerce API =

*	This plugin supports WordPress REST API Integration with the WooCommerce REST APIs. You can authenticate the WooCommerce store APIs with your mobile or desktop application & extend the features and functionality of the your eCommerce store.

= BuddyPress API ( BP REST API ) =

*	This plugin supports BuddyPress API integration with WordPress REST APIs. You can access BP REST API endpoints and also authenticate those from different Authentication methods liek JWT token, API Keys etc.

= Gravity Form API =

*	The plugins supports interaction with Gravity Forms from an external client applications. WP REST API also allows WordPress users to create, read, update and delete forms, entries, and results over HTTP based on their roles.

= Learndash API =

*	The plugins allows accessing LearnDash API from mobile app or any external application. It provdes you the secure access to Learndash user profiles, courses, groups & many more APIs.


== Installation ==

This section describes how to install the WordPress REST API Authentication and get it working.

= From your WordPress dashboard =

1. Visit `Plugins > Add New`
2. Search for `REST API Authentication`. Find and Install `api authentication` plugin by miniOrange
3. Activate the plugin

= From WordPress.org =

1. Download WordPress REST API Authentication.
2. Unzip and upload the `wp-rest-api-authentication` directory to your `/wp-content/plugins/` directory.
3. Activate WordPress REST API Authentication from your Plugins page.


== Privacy ==

This plugin does not store any user data. 

== Frequently Asked Questions ==

= What is the use of API Authentication =
    The REST API authentication prevents the unauthorized access to your WordPress API's. It reduces potential attack factors.
	
= How to enable API access in WooCommerce?
    You can enable API access in WooCommerce using our WP REST API Authentication plugin. Please reach out to us at info@xecurify.com.

= How does REST API Authentication plugin work? =
	You just have to select your Authentication Method.
	Based on the method you have selected you will get the authorization code/token after sending the token request.
	Access your REST API with code/token you received in previous step. 

= How to access draft posts? =
	You can access draft posts using Basic Auth, OAuth 2.0(using Username:Password) methods. Pages/posts are need to access with the status. Default status used in request is 'Publish' and any user can access Published post. 
	To access the pages/posts stored in draft, you need to append the ?status=draft to the page/post request.
	For Example:
	You need to use below URL format while sending request to access different type of posts
	1. Access draft posts only
		https://<domain>/wp-json/wp/v2/posts?status=draft
	2. Access all type of posts
		https://<domain>/wp-json/wp/v2/posts?status=any
	You just have to change the status(draft, pending, any, publish) as per your requirement. You do not have to pass status parameter to access Published posts.

= How can I authenticate the REST APIs using this plugin? =
	This plugin supports 5 methods: i) authentication through API key or token, ii) authentication through user credentials passed as a encrypted token, iii) authentication through JWT (JSON Web token), iv) authentication through OAuth 2.0 protocol and v) authentication via JWT token obtianed from the external OAuth/OpenId providers which includes Google, Facebook, Azure, AWS Cognito etc. 


= Does this plugin allows the authentication through JWT (JSON Web Tokens)? =
	Yes, this plugin support the REST API authentication throug JWT (JSON Web token). The JWT be validated evertime a API request is made, only the requested resource for the API call be allowed to access if the JWT validation is successful.


== Screenshots ==

1. List of API Authentication Methods
2. List of Protected WP REST APIs
3. Advanced Settings
4. Custom API Integration

== Changelog ==

= 1.5.2 =
* Minor fixes
* Remove extra code

= 1.5.1 =
* Minor fixes
* Security fixes

= 1.5.0 =
* Minor fixes
* Security fixes

= 1.4.2 =
* UI updates

= 1.4.1 =
* UI updates
* Minor fixes

= 1.4.0 =
* WordPress 5.6 compatibility

= 1.3.10 =
* Allow all REST APIs to authenticate
* Added postman samples
* Minor Bugfix

= 1.3.9 =
* Minor Bugfix

= 1.3.8 =
* Added compatibility for WP 5.5

= 1.3.7 =
* Bundle plan release
* Minor Bugfix

= 1.3.6 =
* Added compatibility for WP 5.4

= 1.3.5 =
* Minor Bugfix

= 1.3.4 =
* Minor Bugfix

= 1.3.2 =
* Minor Bugfix

= 1.3.1 =
* Minor Fixes

= 1.3.0 =
* Added UI Changes
* Updated plugin licensing
* Added New features
* Added compatibility for WP 5.3 & PHP7.4
* Minor UI & feature fixes

= 1.2.1 =
* Added fixes for undefined getallheaders()

= 1.2.0 =
* Added UI changes for Signing Algorithms and Role Based Access
* Added Signature Validation
* Minor fixes

= 1.1.2 =
* Added JWT Authentication
* Fixed role based access to REST APIs
* Fixed common class conflicts

= 1.1.1 =
* Fixes to Create, Posts, Update Publish Posts

= 1.1.0 =
* Updated UI and features
* Added compatibility for WordPress version 5.2.2
* Added support for accessing draft posts as per User's WordPress Role Capability
* Allowed Logged In Users to access posts through /wp-admin Dashboard

= 1.0.2 =
* Added Bug fixes  

= 1.0.0 =
* Updated UI and features
* Added compatibility for WordPress version 5.2.2

== Upgrade Notice ==

= 1.1.1 =
* Fixes to Create, Posts, Update Publish Posts

= 1.1.0 =
* Updated UI and features
* Added compatibility for WordPress version 5.2.2
* Added support for accessing draft posts as per User's WordPress Role Capability
* Allowed Logged In Users to access posts through /wp-admin Dashboard

= 1.0.2 =
* Added Bug fixes  

= 1.0.0 =
* Updated UI and features
* Added compatibility for WordPress version 5.2.2
