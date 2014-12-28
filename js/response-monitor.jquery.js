/**
 * response-monitor.js
 * https://github.com/witstep/response-monitor.js
 * Copyright (c) Jorge Paulo
 * Licensed under the MIT license
 */
//why note use this in the main source file?
if (window.jQuery) {
	(function($){
		$.fn.ResponseMonitor = function(options){
			return this.each(function() {
				new ResponseMonitor(this,options);
			});
		};
	})(jQuery);
}

