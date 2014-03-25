/*
 * JWChat, a web based jabber client
 * Copyright (C) 2003-2004 Stefan Strigler <steve@zeank.in-berlin.de>
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

var SITENAME = "localhost";

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
			name:"Native Binding",
			description:"Ejabberd's native HTTP Binding backend",
			httpbase:"/http-bind/",
			type:"binding",
			servers_allowed:[SITENAME]
		},
		{
			name:"Native Polling",
			description:"Ejabberd's native HTTP Polling backend",
			httpbase:"/http-poll/",
			type:"polling",
			servers_allowed:[SITENAME]
		},
		{
			name:"Open Relay",
			description:"HTTP Binding backend that allows connecting to any jabber server",
			httpbase:"/JHB/",
			type:"binding",
			default_server: SITENAME
		},
		{
			name:"Restricted Relay",
			description:"This one let's you choose from a limited list of allowed servers",
			httpbase:"/JHB/",
			type:"binding",
			servers_allowed:[SITENAME,'jabber.org','jwchat.org']
		}
];

var DEFAULTRESOURCE = "jwchat";
var DEFAULTPRIORITY = "10";

/* DEFAULTCONFERENCEGROUP + DEFAULTCONFERENCESERVER
 * default values for joingroupchat form
 */
var DEFAULTCONFERENCEROOM = "talks";
var DEFAULTCONFERENCESERVER = "conference."+SITENAME;

/* debugging options */
var DEBUG = false; // turn debugging on
var DEBUG_LVL = 2; // debug-level 0..4 (4 = very noisy)

var USE_DEBUGJID = false; // if true only DEBUGJID gets the debugger
var DEBUGJID = "admin@"+SITENAME; // which user get's debug messages


// most probably you don't want to change anything below

var timerval = 2000; // poll frequency in msec

var stylesheet = "jwchat.css";
var THEMESDIR = "themes";

/* Auto-login options. Define parameters for guest user account and
 * when JWChat page is called with 'jid' parameter which is equal
 * to GUEST_ACC value then configuration below is used for autologin.
 * Sample configuration:
 * var GUEST_ACC = "guest_2";
 * var GUEST_PWD = "guestpwd";
 * Sample call:
 * http://jwchat.localhost/index.html?jid=guest_2
 * will triger auto-login for the 'guest_2' account.
 *
 * As it is possible that many people try to login at the same time
 * the auto-login function will generate random resource based on
 * algorithm: DEFAULTRESOURCE + Math.round(Math.random()*1000)
 */
var GUEST_ACC = "";
var GUEST_PWD = "";
