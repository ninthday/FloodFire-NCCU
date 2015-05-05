(function() {

	/* Import modules for parsing url. */
	var parseUrl = require('url').parse,
	    http = require('http'),
	    https = require('https');

	function unshorten(url, callback) {
		/* Parse url. */
		var urlParts = parseUrl(url),
		    protocol = urlParts.protocol,
		    host = urlParts.host,
		    path = urlParts.pathname;
		
		if (protocol && host && path) {
			/* Send request to server to get result of original url. */
			('https:' == protocol ? https : http).request(
				{	// set up header info
					'method': 'HEAD',
					'host': host,
					'path': path
				},
				function(response) {
					// callback function for parsing response info
					(callback || console.log)(response.headers.location || url);
				}
			).end();
		} else {
			console.error('Not a valid URL: ' + url);
			(callback || console.log)(url);
		}
	}

	// export this module
	module.exports = unshorten;

}());

