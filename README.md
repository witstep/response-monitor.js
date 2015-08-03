# response-monitor.js

A simple client-side JavaScript library for use in the implementation of web pages that request file downloads and are susceptible to noticeable delays. Such delayed response is, typically, caused by server-side activity, required to produce the file contents, but it can also be intentionally imposed as a means of restricting traffic and processing load.

[Spin.js](http://fgnass.github.io/spin.js) is used as the default waiting indicator.

## TOC
* [Features](#features)
* [Example Usage](#example-usage)
* [Browser Compatibility](#browser-compatibility)
* [Getting the Library](#getting-the-library)
* [API Reference](#api-reference)
* [Limitations](#limitations)
* [License](#license)
 
## Features
- Simple integration
- No dependencies
- [JQuery](http://jquery.com) plug-in (optional)
- [Spin.js](http://fgnass.github.io/spin.js) Integration (optional)
- Configurable callbacks for monitoring events
- Handles multiple simultaneous requests
- Server-side error detection
- Timeout detection
- Cross browser
- AMD and CommonJS support
- Lightweight
- MIT license

## Example Usage ##

* [Server-side](#server-side)
    - [Cookies](#cookies)
* [Client-side](#client-side)
    * [Download Request Triggers](#download-request-triggers)
        * [Forms](#forms)
        * [Anchors](#anchors)
        * [URLs](#urls)
    * [JQuery](#jquery)
    * [Callbacks](#callbacks)
	* [RequireJS](#requirejs)

### Server-side ###

#### Cookies ####

The monitor relies on a Cookie set on the server's response. If it is not set, the client will always finish monitoring with a timeout status.
The response monitor implements all client-side Cookie handling, no extra programming is required, but on the server-side a Cookie must be set as described bellow.

The Cookie name is not constant so that simultaneous requests from the same web-browser don't collide. It is composed of a fixed prefix and a time-stamp suffix.

* The client passes the token as a GET argument, like this: `response-monitor=1419642741528`.
* The server must then respond with a cookie named: `response-monitor_1419642741528`.
* The value of the Cookie must be set and it can then optionally be interpreted as a simple status indicator, or carry a more complex structure, like a JSON string.
* The server should use a short expiration period of a few minutes, otherwise the differently named cookies can accumulate because they are not overwritten during subsequent requests.

This is an example of setting the Cookie using PHP:

```PHP
<?php
$cookiePrefix = 'response-monitor'; //must match the one set on the client options
$tokenValue = $_GET[$cookiePrefix];
$cookieName = $cookiePrefix.'_'.$tokenValue; //ex: response-monitor_1419642741528

//this value is passed to the client through the ResponseMonitor.onResponse callback
$cookieValue = 1; //for ex, "1" can interpret as success and "0" as failure

setcookie(
	$cookieName,
	$cookieValue,
	time()+300,            // expire in 5 minutes
	"/",
	$_SERVER["HTTP_HOST"],
	true,
	false
);

header('Content-Type: text/plain');
header("Content-Disposition: attachment; filename=\"$cookieName.txt\"");
print_r($_REQUEST); //output $_GET and $_POST on the response file
?>
```

### Client-side

The class must be made available to the JavaScript engine in the usual ways.

(Don't forget the [server-side](#server-side) requirements!)

```html
<script src="response-monitor.min.js"></script>
```

Alternatively it can be loaded using [RequireJS](#requirejs)

#### Download Request Triggers

The supported download triggers are HTML Anchors, Forms, and URLs as strings followed by a call to the `execute()` method.

##### Forms #####

Any HTML form can be used without specific changes. Both GET and POST methods are supported.

For example

```html
<form id="my_form" method="POST">
	<input type="text" name="criteria1">
	<input type="text" name="criteria2">
	<input type="submit" value="Download Report">
</form>
```

After the form is registered, the submit event is intercepted and monitored.

```html
<script>
	var my_form = document.getElementById('my_form');
	//the submit event of the form will be monitored
	ResponseMonitor.register(my_form);
</script>
```

##### Anchors #####

HTML Anchors to be monitored are defined as any other kind of hyper-link.

Optionally, the hash fragment can be used to set the timeout period.


```html
<a class="my_anchors" href="/report?criteria1=a&criteria2=b#30">Report 1</a>
<a class="my_anchors" href="/report?criteria3=a&criteria4=b#60">Report 2</a>
<a class="my_anchors" href="/report?criteria5=a&criteria6=b#90">Report 3</a>
```

In this example, a collection of Anchors is registered with a single call:

```html
<script>
	var my_anchors = document.getElementsByClassName('my_anchors');
	ResponseMonitor.register(my_anchors); //the clicks on the links will be monitored
</script>
```

##### URLs #####

A string defining an URL can be used as a request trigger.

```html
<script>
	var myMonitor = new ResponseMonitor('service.php?dividend=20&divisor=2');
</script>
```

The request can then be initiated with the ` execute() ` method.

```html
<input type="button" value="Click Me!" onclick="myMonitor.execute();">
```

##### JQuery #####

To use the JQuery plug-in, both the main source file and the JQuery plug-in must be available to the JavaScript engine.

Script tags in a web page would look like this:

```html
<script src="response-monitor.js"></script>
<script src="response-monitor.jquery.js"></script>
```

The following example registers all forms and anchors, setting a specific timeout of 20 seconds for forms:

```html
<script type="text/javascript">
	$('a').ResponseMonitor();
	$('form').ResponseMonitor({timeout: 20});
</script>
```

##### Callbacks #####

The callback functions are set on the `options` object and then passed on the `Constructor` or `register()` method.

```html
<script type="text/javascript">
	var options = {
		onRequest: function(token){
			$('#cookie').html(token);
			$('#outcome').html('');
			$('#duration').html(''); 
		},
		onMonitor: function(countdown){
			$('#duration').html(countdown); 
		},
		onResponse: function(status){
			$('#outcome').html(status==1?'success':'failure');
		},
		onTimeout: function(){
			$('#outcome').html('timeout');
		}
	};

	$('a').ResponseMonitor(options);
</script>
```

##### RequireJS #####

The dependency on spin.js is optional and the related references can be ommitted, if implementing a custom waiting indicator.

```html

<script src="//cdnjs.cloudflare.com/ajax/libs/require.js/2.1.1/require.min.js"></script>

<script>

	requirejs.config({
		paths: { 
			'spin': ['//cdnjs.cloudflare.com/ajax/libs/spin.js/2.0.1/spin.min'],
			'response-monitor': ['../js/response-monitor.min']
		}
	});

	require(['response-monitor','spin'], function(ResponseMonitor){
		ResponseMonitor.register(document.anchors);
	});

</script>
```

## Browser Compatibility

The following browsers have been tested:

- Chrome
- Firefox 3+
- Safari 4+
- Internet Explorer 8+

## Getting the Library

#### Direct downloads

Use this option if you want to host the JS file(s) yourself.

- Minified ([response-monitor.min.js](https://raw.github.com/witstep/response-monitor.js/v0.1.3/js/response-monitor.min.js))
- Original ([response-monitor.js](https://raw.github.com/witstep/response-monitor.js/v0.1.3/js/response-monitor.js))
- JQuery plug-in ([response-monitor.jquery.js] (https://raw.github.com/witstep/response-monitor.js/v0.1.3/js/response-monitor.jquery.js))

#### Content Delivery Network

- [rawgit.com](https://rawgit.com/)

Rawgit is a free service that caches files from Github repositories, in the high-performance [MaxCDN] (http://www.maxcdn.com) content delivery system

You shouldn't use this option, without first reading and understanding the rawgit.com [FAQ](https://rawgit.com/faq).

The `script` tag would look like this:

```html
<script src="//cdn.rawgit.com/witstep/response-monitor.js/v0.1.3/js/response-monitor.min.js">
</script>
```
## API Reference ##

### Token and Cookies ###

The monitor depends on the server setting a cookie on the response to the download request.
The name of the `Cookie` is produced by concatenating a token passed by the client with a configurable prefix. The value of the `Cookie` can optionally be used to pass a status message to the client.


`Set-Cookie: <prefix>_<token>`

For example in raw HTTP it would look like this

Client Request

```
GET /service?arg=abcd&response-monitor=419642741528
Host: www.example.org
```

Server Response

```
HTTP/1.0 200 OK
Content-type: text/plain
Content-Disposition: attachment; filename="Hello.txt"
Set-Cookie: response-monitor_419642741528=1; Expires=Sun, 28 Dec 2014 10:18:14 GMT


Hello Response Monitor!
```

### Triggers ###

#### HTML Anchors ####

The monitor overrides the `onclick` event of the anchor, appends the token as a GET argument, and interprets the `#` fragment as the timeout period in seconds.

#### HTML Forms ####

The monitor overrides the `onsubmit` event of the form, appends the token as a GET argument, and interprets the `#` fragment as the timeout period in seconds.
Both GET and POST methods are supported

#### URL Strings #####

The monitor can be instantiated using a string representation of an URL. In this case, the only way to start the download request is by explicitly calling the `execute()` method.


### Options ###

If no options are set, there will be an attempt to use [spin.js](http://fgnass.github.io/spin.js) as the default waiting indicator. If it is not available, the instantiation of the response monitor will fail.

To use the response monitor without spin.js, the programmer must define her own function callbacks.

| Option         | Description                                                                   | Default           |
| -------------- | ----------------------------------------------------------------------------- | ----------------- |
| *timeout*      | How many seconds to wait for a response before stopping monitoring            | `120`             |
| *cookiePrefix* | The prefix use for the Cookie. The client and server must use the same value  | `response-monitor`|
| *onRequest*    | Function called when the user clicks on an anchor or submits a form           | `function(){}`    |
| *onMonitor*    | Function called once every second while waiting for a response                | `function(){}`    |
| *onResponse*   | Function called when a server response is detected                            | `function(){}`    |
| *onTimeout*    | Function called when the waiting period terminates                            | `function(){}`    |


### Methods ###

#### ResponseMonitor(trigger [,options]) ####
This is the class `constructor`. Can be use to create an instance and register a single element.

#### ResponseMonitor.register(triggers [,options]) ####

Registers multiple triggers in a single call.

#### ResponseMonitor.execute() ####

Make the download request. It can be used as an extra way to trigger requests, other than `a.onclick` and `form.onsubmit`.
It is the only way to make the request for monitor object instantiated with URL strings.

#### ResponseMonitor.setTimeout(timeout) ####

Provides a way to change the timeout value after instantiation.

#### ResponseMonitor.setCookiePrefix(timeout) ####

Provides a way to change the `Cookie` prefix after instantiation.

### Callbacks ###

#### onRequest(cookieName) ####

It is called when the user initiates a request by clicking on an anchor or submitting a registered form.
The cookieName provides an unique identifier for the request, that can be useful for the application being implemented.

#### onMonitor(countdown) ####

This callback is called once every second, until there's a response from the server or the timeout period elapses.
The `countdown` variable starts with the same values as the configured `timeout`, and counts down to zero.

#### onResponse(status) ####

Called when the monitor detects that the Cookie is avaiable. The `status` variable contains the value passed in the cookie.

#### onTimeout()  ####

Called once when the timeout period elapses without a response from the server.

## Limitations ##

* Relies on the server Cookie to detect error status, no attempt is being made in detecting server errors like "content-disposition" different from "attachment", HTTP 500, 404, etc. If the Cookie is not set, these errors would eventually result in timeout

* There is no explicit way to unregister elements from the response monitor. The closest thing is changing the onclick and onsubmit properties. After that, the elements won't be monitored and the dropping of the monitor object reference from the handler makes it available for garbage collection.

## License ##

[MIT License](http://www.opensource.org/licenses/mit-license)

Copyright ©2014 Jorge Paulo
