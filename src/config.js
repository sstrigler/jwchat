/*
 * JWChat, a web based jabber client

 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * Please visit http://jwchat.sourceforge.net for more information!
 */

/*
 * This is the main configuration file for the chat client itself.
 * You have to edit this before you can use jwchat on your website!
 *
 * Have a look at the README for hints and troubleshooting!
 */

var SITENAME = "jwchat.org";

/* BACKENDS
 * Array of objects each describing a backend.
 *
 * Required object fields:
 * name      - human readable short identifier for this backend
 * httpbase  - base address of http service [see README for details]
 * type      - type of backend, must be 'polling' or 'binding'
 *
 * Optional object fields:
 * description     - a human readable description for this backend
 * servers_allowed - array of jabber server addresses users can connect to 
 *                   using this backend
 *
 * If BACKENDS contains more than one entry users may choose from a
 * select box which one to use when logging in.
 *
 * If 'servers_allowed' is empty or omitted user is presented an input
 * field to enter the jabber server to connect to by hand.
 * If 'servers_allowed' contains more than one element user is
 * presented a select box to choose a jabber server to connect to.
 * If 'servers_allowed' contains one single element no option is
 * presented to user.
 */
var BACKENDS = 
[
		{
			name:"node-xmpp-bosh",
			description:"An XMPP BOSH & WebSocket server (connection manager) written using node.js in Javascript",
			httpbase:"/http-bind/",
			type:"binding",
			default_server: "jwchat.org"
    }/*,
		{
			name:"JabberHTTPBind",
			description:"A Java&trade; servlet implementing JEP-0124 (<a href='http://zeank.in-berlin.de/jhb/' target='_new'>http://zeank.in-berlin.de/jhb/</a>)",
			httpbase:"/JHB/",
			type:"binding",
			default_server: "jwchat.org"
    }/*,
    {
      name:"Ejabberd's HTTP Polling",
      description:"HTTP Polling backend of local ejabberd service",
      httpbase:"/http-poll/",
      type:"polling",
			servers_allowed: ['jwchat.org', 'x-berg.de', 'muckl.org'],
      default_server:"jwchat.org"
    }*/
];

var DEFAULTRESOURCE = "jwchat";
var DEFAULTPRIORITY = "10";

/* DEFAULTCONFERENCEGROUP + DEFAULTCONFERENCESERVER
 * default values for joingroupchat form
 */
var DEFAULTCONFERENCEROOM = "jwchat";
var DEFAULTCONFERENCESERVER = "conference.jwchat.org";

/* debugging options */
var DEBUG = true; // turn debugging on
var DEBUG_LVL = 4; // debug-level 0..4 (4 = very noisy)

var USE_DEBUGJID = true; // if true only DEBUGJID gets the debugger
var DEBUGJID = "zeank2@jwchat.org"; // which user get's debug messages

// most probably you don't want to change anything below

var timerval = 2000; // poll frequency in msec

var stylesheet = "jwchat.css";
var THEMESDIR = "themes";
