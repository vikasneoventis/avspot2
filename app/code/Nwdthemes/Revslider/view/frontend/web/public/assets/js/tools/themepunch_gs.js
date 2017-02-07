
// THEMEPUNCH INTERNAL HANDLINGS
if(typeof(console) === 'undefined') {
    var console = {};
    console.log = console.error = console.info = console.debug = console.warn = console.trace = console.dir = console.dirxml = console.group = console.groupEnd = console.time = console.timeEnd = console.assert = console.profile = console.groupCollapsed = function() {};
}

// THEMEPUNCH LOGS
if (window.tplogs==true)
	try {
		console.groupCollapsed("ThemePunch GreenSocks Logs");
	} catch(e) { }

// SANDBOX GREENSOCK

var oldgs = window.GreenSockGlobals;
	oldgs_queue = window._gsQueue;

var punchgs = window.GreenSockGlobals = {};

var punchgsSandbox = {
    oldgs:          oldgs,
    oldgs_queue:    oldgs_queue
}

if (window.tplogs==true)
	try {
		console.info("Build GreenSock SandBox for ThemePunch Plugins");
		console.info("GreenSock TweenLite Engine Initalised by ThemePunch Plugin");
	} catch(e) {}