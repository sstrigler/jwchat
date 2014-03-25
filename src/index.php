<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title><l>Welcome to JWChat</l> - the Jabber Web Chat</title>
    
    <meta name="description" content="A free, web based instant messaging client for the XMPP aka Jabber network. Through gateways it allows to connect to foreign networks like AIM, ICQ, Yahoo! and MSN. It includes support for multi user conferences (groupchats or chat rooms)">
    <meta name="keywords" content="Jabber, XMPP, web based, AJAX, instant messaging, browser, MUC, chat rooms, conferences, chat, multi user, JavaScript, HTML, client, HTTP Binding, BOSH, HTTP Polling, ejabberd, SSL, apache, free"> 
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <meta name="verify-v1" content="xlVccy/b29cMNfzNj7zRo6zOX/W/IPs2vgdv714aGTE=" />

    <script src="config.js" language="JavaScript1.2"></script>
    <script src="browsercheck.js" language="JavaScript1.2"></script>
    <script src="shared.js" language="JavaScript1.2"></script>
    <script src="switchStyle.js"></script>
    <script language="JavaScript">
<!--

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

var jid, pass, register, prio, connect_host, connect_port, connect_secure;
var jwchats = new Array();

var JABBERSERVER;
var HTTPBASE;
var BACKEND_TYPE;

/* check if user want's to register new
 * account and things */    
function loginCheck(form) { 
  if (form.jid.value == '') {
    alert(loc("You need to supply a username"));
    return false;
  }

  if (form.pass.value == '') {
    alert(loc("You need to supply a password"));
    return false;
  }

  if (document.getElementById('tr_server').style.display != 'none') {
    var val = document.getElementById('server').value;
    if (val == '') {
      alert(loc("You need to supply a jabber server"));
      return false;
    }

    JABBERSERVER = val;
  }

  jid = form.jid.value + "@" + JABBERSERVER + "/";
  if (form.res.value != '')
    jid += form.res.value;
  else
    jid += DEFAULTRESOURCE;

  if(!isValidJID(jid))
    return false;

  if (jwchats[jid] && !jwchats[jid].closed) {
    jwchats[jid].focus();
    return false;
  }

  pass = form.pass.value;
  register = form.register.checked;

  prio = form.prio[form.prio.selectedIndex].value;

  connect_port = form.connect_port.value;
  connect_host = form.connect_host.value;
  connect_secure = form.connect_secure.checked;

  jwchats[jid] = window.open('jwchat.html',makeWindowName(jid),'width=180,height=390,resizable=yes');

  return false;
}

function toggleMoreOpts(show) {
  if (show) {
    document.getElementById('showMoreOpts').style.display = 'none';
    document.getElementById('showLessOpts').style.display = '';
  } else {
    document.getElementById('showLessOpts').style.display = 'none';
    document.getElementById('showMoreOpts').style.display = '';
  }

  var rows = document.getElementById('lTable').getElementsByTagName('TBODY').item(0).getElementsByTagName('TR');

  for (var i=0; i<rows.length; i++) {
    if (rows[i].className == 'moreOpts') {
      if (show)
	rows[i].style.display = '';
      else
	rows[i].style.display = 'none';
    }
  }
  return false;
}

function serverSelected() {
  var oSel = document.getElementById('server');
  var servers_allowed = BACKENDS[bs.selectedIndex].servers_allowed;

  // TODO ...
  
  /* change format of servers_allowed to be able to associate connect 
   * host information to it 
   */
}

function backendSelected() {
  var bs = document.getElementById('backend_selector');
  var servers_allowed, default_server;
  if (bs) {
    servers_allowed = BACKENDS[bs.selectedIndex].servers_allowed;
    default_server = BACKENDS[bs.selectedIndex].default_server;
    if (BACKENDS[bs.selectedIndex].description)
      document.getElementById('backend_description').innerHTML = BACKENDS[bs.selectedIndex].description;
    HTTPBASE = BACKENDS[bs.selectedIndex].httpbase;
    BACKEND_TYPE = BACKENDS[bs.selectedIndex].type;
  }	else {
    servers_allowed = BACKENDS[0].servers_allowed;
    default_server = BACKENDS[0].default_server;
    HTTPBASE = BACKENDS[0].httpbase;
    BACKEND_TYPE = BACKENDS[0].type;
  }
  
  if (!servers_allowed
      || servers_allowed.length == 0) 
    { // allow any
      var tr_server = document.getElementById('tr_server');
      
      var input = document.createElement('input');
      input.setAttribute("type","text");
      input.setAttribute("id","server");
      input.setAttribute("name","server");
      input.setAttribute("tabindex","2");
      input.className = 'input_text';
      
      if (default_server)
	input.setAttribute("value",default_server);
      
      var td = tr_server.getElementsByTagName('td').item(0);
      for (var i=0; i<td.childNodes.length; i++)
	td.removeChild(td.childNodes.item(i));
      
      td.appendChild(input);
      
      tr_server.style.display = ''; 
      
      document.getElementById('connect_port').disabled = false;
      document.getElementById('connect_host').disabled = false;
      document.getElementById('connect_secure').disabled = false;
    }
  else if (servers_allowed.length == 1) {
    document.getElementById('tr_server').style.display = 'none';
    JABBERSERVER = servers_allowed[0];
    document.getElementById('connect_port').disabled = true;
    document.getElementById('connect_host').disabled = true;
    document.getElementById('connect_secure').disabled = true;
  } else { // create selectbox
    var tr_server = document.getElementById('tr_server');
    
    var oSelect = document.createElement('select');
    oSelect.setAttribute('id','server');
    oSelect.setAttribute('name','server');
    oSelect.setAttribute('tabindex',"2");
    oSelect.onchange = serverSelected;
    
    var td = tr_server.getElementsByTagName('td').item(0);
    for (var i=0; i<td.childNodes.length; i++)
      td.removeChild(td.childNodes.item(i));
    
    td.appendChild(oSelect);
    
  for (var i=0; i<servers_allowed.length; i++) {
    if (typeof(servers_allowed[i]) == 'undefined')
continue;
    oSelect.options.add(new Option(servers_allowed[i],servers_allowed[i]));
  }
  
  tr_server.style.display = ''; 
  document.getElementById('connect_port').disabled = true;
  document.getElementById('connect_host').disabled = true;
  document.getElementById('connect_secure').disabled = true;
}
}

function init() {
var welcome = loc("Welcome to JWChat at [_1]", SITENAME);
document.title = welcome;
document.getElementById("welcomeh1").innerHTML = welcome;

// create backend chooser - if any
if (typeof(BACKENDS) == 'undefined' || BACKENDS.length == 0) {
  // ...
} else if (BACKENDS.length == 1) {
  backendSelected();
} else {
  // create chooser
  var oSelect = document.createElement('select');
  oSelect.setAttribute('id','backend_selector');
  oSelect.setAttribute('name','backend');
  oSelect.setAttribute('tabindex',"1");
  oSelect.onchange = backendSelected;

  var tr = document.createElement('tr');
  var td = tr.appendChild(document.createElement('th'));
  var label = td.appendChild(document.createElement('label'));
  label.setAttribute('for','backend_selector');
  label.appendChild(document.createTextNode(loc("Choose Backend")));
  
  tr.appendChild(document.createElement('td')).appendChild(oSelect);
  
  var tr_server = document.getElementById('tr_server');
  tr_server.parentNode.insertBefore(tr,tr_server);
  
  tr = document.createElement('tr');
  td = tr.appendChild(document.createElement('td'));
  td = document.createElement('td');
  td.setAttribute('id','backend_description');
  td.className= 'desc';
  tr.appendChild(td);

  tr_server.parentNode.insertBefore(tr,tr_server);

  for (var i=0; i<BACKENDS.length; i++) {
    if (typeof(BACKENDS[i]) == 'undefined')
continue;
    var oOption =  new Option(BACKENDS[i].name,BACKENDS[i].httpbase);
    oOption.setAttribute('description',BACKENDS[i].description);
    oSelect.options[i] = oOption;
  }
  
  backendSelected();
}
document.forms[0].jid.focus();
document.getElementById('chars_prohibited').innerHTML = prohibited;
if (typeof(DEFAULTRESOURCE) != 'undefined' && DEFAULTRESOURCE)
  document.forms[0].res.value = DEFAULTRESOURCE;

document.getElementById('login_button').disabled = false;
}


onload = init;
//-->
  </script>

<!-- flattr button config -->
<script type="text/javascript">
/* <![CDATA[ */
  (function() {
            var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
            s.type = 'text/javascript';
            s.async = true;
            s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
            t.parentNode.insertBefore(s, t);
  })();
/* ]]> */
</script>

  <style type="text/css">
/*<![CDATA[*/
    body {
    color: #2a3847;
    background-color: white;
    }

    th {
    font-size: 0.8em;
    text-align: right;
    white-space: nowrap;
    }

    a { color: #2a3847; } 
    
    h1 { 
    font-size: 1.4em; 
    margin-top:0px; 
    margin-bottom: 0px; 
    }
    
    h2 { padding-top: 0px; margin-top: 0px; }
    
    h3 {
    border-bottom: 1px solid #2a3847;
    margin-bottom: 0px;

    font-style: normal;
    font-variant: small-caps;
    
    text-align: right;
    }
    
    input.input_text {
    border: 1px solid #2a3847;
    }
    
    input:focus, input:hover {
    background-color: #f9fae1;
    }
    
    .toggleOpts { cursor: pointer; }
    
    .desc {
    font-size: 0.65em;
    }
    
    .form_spacer {
    padding-top: 20px;
    }
    
    #td_top {
    padding-top: 20px;
    }
    #td_form {
    padding-top: 20px;
    }
    #td_bottom {
    padding: 4px;
    font-size:8pt; 
    border-top:1px solid #2a3847;
    }
    #lTable {
    padding: 8px;
    
    border: 2px solid #2a3847;
    -moz-border-radius: 8px;
    
    background-color: #81addc;
    }
#featured { padding:25px; margin:25px; text-align: left; }
#featured div { margin-left: -20px; margin-top: -20px; padding-bottom: 5px; font-size:0.8em; }
#featured ul { list-style-type: none; }
#featured ul li { display: inline; }
/*]]>*/
  </style>
</head>

<body>
  <table width="100%" height="100%">
      <tr>
        <td align=center id='td_top'>
          <table>
              <tr>
                <td>
                  <h1 id="welcomeh1"><l>Welcome to JWChat</l></h1>
                  <h2><l>A web based Jabber/XMPP client</l></h2>
                </td>
              </tr>
          </table>
        </td>
      </tr>
    <tr>
      <td height="100%" align=center valign=top id='td_form'>
  <form name="login" onSubmit="return loginCheck(this);">
        <table border=0 cellspacing=0 cellpadding=2 id="lTable" align=center width=380>
            <tr>
              <td colspan=2><h3><l>Login</l><img src="images/available.gif" width=16 height=16></h3></td>
            </tr>
            <tr id="tr_server" style="display:none;">
              <th title="<l>Select Jabber server to connect to</l>"><label for='server'><l>Server</l></label></th>
              <td></td>
            </tr>
            <tr>
              <th class='form_spacer'><label for='jid'><l>Username</l></label></th>
              <td class='form_spacer' width="100%"><input type="text" id='jid' name="jid" tabindex=3 class='input_text'></td>
            </tr>
            <tr><td>&nbsp;</td><td nowrap class="desc"><l>Username must not contain</l>: <span id='chars_prohibited'></span></td></tr>
            <tr>
              <th><label for='pass'><l>Password</l></label></th>
              <td><input type="password" id='pass' name="pass" tabindex=4 class='input_text'></td>
            </tr>
            <tr><td>&nbsp;</td><td><input type=checkbox name=register id=register> <label for="register"><l>Register New Account</l></label></td></tr>
            <tr id="showMoreOpts" class="toggleOpts">
              <td>&nbsp;</td>
              <td onClick="return toggleMoreOpts(1);"><img src="images/group_close.gif" title="<l>Show More Options</l>"> <l>Show More Options</l></td>
            </tr>
            <tr id="showLessOpts" class="toggleOpts" style="display:none;">
              <td>&nbsp;</td>
              <td onClick="return toggleMoreOpts(0);"><img src="images/group_open.gif" title="<l>Show Fewer Options</l>"> <l>Show Fewer Options</l></td>
            </tr>
            <tr class="moreOpts" style="display:none;">
              <th><label for='res'><l>Resource</l></label></th>
              <td><input type="text" id="res" name="res" class="input_text"></td>
        </tr>
            <tr class="moreOpts" style="display:none;">
              <th><label for='prio'><l>Priority</l></label></th>
              <td>
                <select type="text" id="prio"  name="prio" class="input_text" size="1">
                  <option value="0"><l>low</l></option>
                  <option value="10" selected><l>medium</l></option>
                  <option value="100"><l>high</l></option>
                </select>
              </td>
            </tr>
            <tr class="moreOpts" style="display: none;">
              <th class="form_spacer"><label for="connect_port"><l>Port</l><label></th>
              <td class="form_spacer"><input type="text" name="connect_port" id="connect_port" class="input_text" disabled></td>
            </tr>
            <tr class="moreOpts" style="display: none;">
              <th><label for="connect_host"><l>Connect Host</l></label></th>
              <td><input type="text" name="connect_host" id="connect_host" class="input_text" disabled></td>
            </tr>
            <tr class="moreOpts" style="display: none;">
              <td>&nbsp;</td>
              <td><input type="checkbox" name="connect_secure" id="connect_secure" class="input_text" disabled> <label for="connect_secure" title="<l>Advise connection manager to connect through SSL</l>" disabled><l>Allow secure connections only</l></label></td>
            </tr>
            
            <tr><td>&nbsp;</td><td><button type="submit" id='login_button' tabindex=5 disabled><l>Login</l></button></td></tr>
        </table>
  </form>
<div style="width:400px;">Lost? Click here to <a href="/register/new">register a new account</a>, <a href="/register/change_password">change your password</a> or <a href="/register/delete">delete your existing account</a>!</div>

        </td>
      </tr>
      <tr>
        <td valign="bottom" align="center">
 <div id="featured">
    <div>ads/sponsoring</div>
<?php
/* ****************************************************************
* adspace:        http://jwchat.org                              *
* creation date:  2008-02-25                                     *
* contact:        support@linklift.de                            *
* script-version: 1.5    (2008-02-20)                            *
* ****************************************************************/
// this PHP-script runs on PHP-engines >= v4.0.6
// tab-size of this document:  4 spaces



/**	Disabling all error- (, warning-, and notice-) messages...
*	You may want to un-comment the following lines when testing or debugging this plugin.
*  At the end of the plugin you may restore the original reporting-level using the following call:
*  error_reporting( $linklift_saved_reporting_level );
*  
*  Note: there is another call of error_reporting() in this plugin's method execute()!
* 
* default:			E_ERROR | E_WARNING | E_PARSE
* all messages:	E_ALL
* only errors:		E_ERROR
* no messages:		0
* 
*/
$linklift_saved_reporting_level = error_reporting();
error_reporting( E_ALL );





/**	
*	The LinkLift-website-key identifies your website on the LinkLift-marketplace.
*/
@define( "LL_WEBSITE_KEY"						, "2d5705f5fI0" );

/**	"LL_75Idf005f52.xml" is the local XML-file used to store textlink-data
*	that has been downloaded from the LinkLift-Server.
*/
@define( "LL_TEXTLINK_DATAFILE"	, "LL_75Idf005f52.xml" );


/**	The script-version-constants are used for a check
*	if a newer version of your plug-in is available on the LinkLift-server.
*  Note: the plug-in will not update itself, at this time.
*/
@define( "LL_PLUGIN_LANGUAGE"					, "php" );
@define( "LL_PLUGIN_VERSION"					, "1.5" );
@define( "LL_PLUGIN_DATE"						, "2008-02-20" );

@define( "LL_UPDATE_CHECK_TIMEFRAME"			, "-1 week" );

@define( "LL_PLUGIN_SECRET"						, "cd7diqmKb0" );


/**	In order to not block the page-load-progrss
*	a time-limit (in seconds) is set when receiving new data from the LinkLift-server.
*/
@define( "LL_DATA_TIMEOUT"						, 7 );


/**	
*	The server-host to connect to in order to download data from LinkLift-server.
*/
@define( "LL_SERVER_HOST"						, "external.linklift.net" );
@define( "LL_SERVER_URL"						, "http://" . LL_SERVER_HOST . "/" );




// if the plugin's individual "secret" is delivered the plugin's so-called SECRET-MODE is entered. Necessary for entering the plugin's DEBUG_MODE.
if (   (   (! empty($_REQUEST["ls"]))
  && (LL_PLUGIN_SECRET === $_REQUEST["ls"])
  )
|| (   (! empty($_SERVER["REQUEST_URI"]))
  && (preg_match('@ls.' . LL_PLUGIN_SECRET . '@', $_SERVER["REQUEST_URI"]))
  )
)
{
@define( "LL_SECRET_MODE"					, true );
} else {
@define( "LL_SECRET_MODE"					, false );
} //if-else


/** a possible debug-mode helping to analyse problems and functionality of the plugin
* existing LL_DEBUG_MODEs:
*   1:  Display internal testlink with maximum length and umlauts
*   2:  Display the object's / plugin's current data / state
*   3:  Display the current XML-cache
*   4:  Displays some "external values" like values of LinkLift-constants or the surrounding CMS' resolved language.
*   5:  Update the XML-cache (externally forced update)
*  10:  Displays the running script's filename
*  99:  Known debug-modes
*/
if (   (LL_SECRET_MODE)
&&
  (  (   (! empty($_REQUEST["ld"]))
    && (is_numeric($_REQUEST["ld"]))
    )
  || (   (! empty($_SERVER["REQUEST_URI"]))
    && (preg_match('@ld.(\d\d?)@', $_SERVER["REQUEST_URI"], $debug_mode_matches))
    )
  )
)
{
if (isset($debug_mode_matches[1]))
  @define( "LL_DEBUG_MODE"				, $debug_mode_matches[1] );
else
  @define( "LL_DEBUG_MODE"				, $_REQUEST["ld"] );

} else {
@define( "LL_DEBUG_MODE"					, false );
} //if-else






if (! class_exists("LinkLiftPlugin"))
{

class LinkLiftPlugin
{





// A LinkLift-website-key other than the class' constant LL_WEBSITE_KEY that should be used for updating the locally cached XML-data. 
var $linklift_website_key;
// A XML-filename other then the class' constant above. This property will have no effect if your plugin uses a local database for caching.
var $xml_filename = "";
// An array of integers representing the textlinks of the current textlink-data-array that should be generated and printed. You may want to use this property in order to distribute your textlinks on different locations of your website, to give an example. E.g: $links_to_show = array(1,3,5,7,9).
var $links_to_show;

// the current XML-cache
var $xml_cache;
// the last time the XML-cache has been updated
var $xml_cache_time;

// other variables, mainly copying linklift-constants
var $data_timeout    = LL_DATA_TIMEOUT;
var $plugin_language = LL_PLUGIN_LANGUAGE;
var $plugin_version  = LL_PLUGIN_VERSION;
var $plugin_date     = LL_PLUGIN_DATE;




/**
* Usually, instances of the LinkLift-plugin's class do not need an (individual) state.
* Thus, in general, we will need at most one instance of the plugin-class in order to generate HTML-output out of external data (XML-textlink-data).
* 
* The class-method will always return the same object (singleton) or create a new one for you if none exists.
* 
* Note: Since the method returns a reference you have to de-reference the result; e.g.:
* $new_instance =& LinkLiftPlugin::getInstance(); 
* 
* @author akniep (Andreas Rayo Kniep)
* @since 2007-10-26
* @return instance-reference on the class's singleton
*/
function &getInstance()
{
static $singleton;

if (   (! is_object($singleton))
  || (null == $singleton)
  || (empty($singleton->linklift_website_key))   )
	{
		$singleton = new LinkLiftPlugin();
	} //if
	
	return $singleton;
} //getInstance()

/**
 * Using the class' constructor you can obtain an instances of the LinkLift-plugin's having an individual state.
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-10-26, 2007-12-10
 * @param $linklift_website_key string The LinkLift-website-key that should be used for updating the locally cached XML-data. If empty the method will use the default website-key that you can either find as a constant above or as value in your local database. Usually, this parameter is unused and empty!
 * @param $xml_filename string The filename of the local XML-file that should be used for caching textlink-data iff your plugin does not use a local database for caching. If empty the method will use the default XML-filename that you can find as constant above. Usually, this parameter is unused and empty!
 * @param $links_to_show array of integers representing the textlinks of the current textlink-data-array that should be generated and printed by this instance. You may want to use this parameter in order to distribute your textlinks on different locations of your website, to give an example. E.g.: $links_to_show = array(0,2,4,6,8). Usually, this parameter is unused and empty!
 * @return new instance of the LinkLift-plugin-class LinkLiftPlugin
 */
function LinkLiftPlugin( $linklift_website_key = "", $xml_filename = "", $links_to_show = array() )
{
	if (empty($linklift_website_key))
	{
		// plugin-specific saving and loading
		
	} //if
	
	$instance_linklift_website_key	= ( (! empty($linklift_website_key)) ? ($linklift_website_key) : (LL_WEBSITE_KEY) );
	
	
	
	if (   (empty($xml_filename))
		&& (defined("LL_TEXTLINK_DATAFILE"))   )
	{
		$xml_filename		= LL_TEXTLINK_DATAFILE;
	} //if-else
	
	
	
	// saving instance-properties
	$this->setWebsiteKey(  $instance_linklift_website_key );
	$this->setXmlFilename( $xml_filename );
	$this->setLinksToShow( $links_to_show );
	
	
	
	if (file_exists( $this->getXmlFilename() ))
		$this->setXmlCacheTime( filemtime($this->getXmlFilename()) );
} //__construct()


/**
 * This method creates an instance of class LinkLiftPlugin and invokes its main-function ll_textlink_code
 *   in order to generate and output HTML-code with your textlinks (to standard-out) using the default linklift-website-key.
 * 
 * This method wraps the calls of getInstance() and ll_textlink_code() and contains no further logic.
 * 
 * class-method ("static")
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @param $return boolean, Indicating whether the generated HTML-code should be returned ($return == true), or written to standard-out (echo); default: false.
 * @return void
 */
function execute( $return = false )
{
	// not displaying error-messages while executing the LinkLift-plugin
	$linklift_saved_reporting_level = error_reporting();
	error_reporting( 0 );
	
	
	$linklift_plugin_instance	=& LinkLiftPlugin::getInstance();
	$textlink_code				=& $linklift_plugin_instance->ll_textlink_code( $return );
	
	
	// restoring original error-reporting-level
	error_reporting( $linklift_saved_reporting_level );
	
	
	return $textlink_code;
} //execute()


/**
 * Detects and returns the given string's encoding using mb_detect_encoding.
 * class-method ("static")
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-10
 * @param $string string, the encoding of which should be deteceted.
 * @return string the detected encoding or an empty string of no encoding could have been detected.
 */
function ll_mb_detect_encoding( $string ) 
{
	if (function_exists("mb_detect_encoding"))
		return mb_detect_encoding(	  $string
									, $encoding_list = array( "UTF-8", "ISO-8859-1", "ISO-8859-15", "ASCII" )
									);
	else
		return "";
} //ll_mb_detect_encoding()

/**
 * Calls a given string-function with the given 
 * Unlike strtolower, this method tries to detect the given string's encoding (calling ll_mb_detect_encoding())
 *   and convert using mb_strtolower protecting special characters (e.g. umlauts).
 * 
 * If the needed functions for detection and conversion are not available
 *   strtolower is used in order to convert the given string.
 * class-method ("static")
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-10
 * @param $string_function string, (not callback!) the string-function to be either called directly or after prepending the "mb_"-prefix
 * @param $param_arr array, of parameters being passed to the $string_function, the first parameter is assumed to be the string to apply the $string_function to
 * @return mixed the result of the called $string_function or false if a faulty array of parameters was delivered
 */
function ll_call_str_function_encoding_dependent( $string_function, $param_arr ) 
{
	if (   (empty($param_arr))
		|| (! is_array($param_arr))   )
	{
		return false;
	} //if
	
	
	if (function_exists("mb_" . $string_function))
	{
		// the first given parameter is assumed to be the string to apply the $string_function to
		$string				= $param_arr[0];
		$string_encoding	= LinkLiftPlugin::ll_mb_detect_encoding( $string );
		
		if (! empty($string_encoding))
		{
			$param_arr[] = $string_encoding;
			
			// calling the corresponding multibyte PHP-function
			return call_user_func_array("mb_" . $string_function, $param_arr);
		} //if
	} //if
	
	// calling the original PHP-function working with strings in ASCII-format
	return call_user_func_array($string_function, $param_arr);
} //ll_call_str_function_encoding_dependent()


/**
 * The method tries to connect to a given $server_host in order to open a server-socket-connection on port 80
 *   and write the given $request-string to it.
 * The given $request is expected to be a valid HTTP-request like typical GET- or POST-requests.
 * The data returned from the $server_host and received within a fixed timeframe will be returned.
 * The method is invoked by get_404_page() and ll_retrieve_xml_from_ll_server() or ll_send_ping_signal(), respectively (depending on your plugin-type).
 * class-method ("static")
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2006-09-18, 2007-03-12, 2007-12-17
 * @return $server_host string the host of the server to connect to and send the given $request to.
 * @return $request string the HTTP-request to be written to the server-socket-connection that is tried to be established with the given $server_host.
 * @return string the data received from the $server_host after writing the given $request to the opened server-socket-connection, or false if an error occurred.
 */
function get_page_content( $server_host, $request, $data_timeout = LL_DATA_TIMEOUT )
{
	if (   ($server = fsockopen($server_host, 80, $errno, $errstr, $data_timeout))
		&& (is_resource( $server ))   )
	{
		if (function_exists("stream_set_blocking"))
			stream_set_blocking($server, false);
		
		if (function_exists("stream_set_timeout"))
			stream_set_timeout( $server, $data_timeout );
		else
			socket_set_timeout( $server, $data_timeout );
		
		
		$connection_start_time = time();
		
		
		$write_result = fwrite( $server, $request );
		
		// unable to write to open socket.
		if (! $write_result)
			return false;
		
		
		
		$data = "";
		
		while (! feof($server))
		{
			$data_before = $data;
			$data       .= fread($server, 10000); 
			
			// if no data was received (yet) ...
			if ($data_before == $data)
			{
				// if no data was received and the the data-timout was reached
					// the download-process will be stopped.
				if ($data_timeout < time() - $connection_start_time)
				{
					$data = "";
					break;
				} //if
				
				// ... go to sleep for 10 ms, waiting for data.
					// usleep() works on Linux-like servers!
					// On other systems usleep may have no effect
					// PHP 5.0.0 claims that usleep works on Windows-systems, as well ...
					// ... the effect on the system's processor, however, is not known to the writer.
				if (function_exists("usleep"))
					usleep($micro_seconds = 10 * 1000);		// 10 * 1000 micro seconds  =  10 milli seconds  =  0.01 seconds
			} //if
		} //while
		
		
		fclose($server);
		
		
		return $data;
		
		
	} else {
		return false;
	} //if-else
} //get_page_content()

/**
 * Downloads the current-plugin-info for the installed plugin from the LinkLift-server.
 * The method will return an associative array of plugin_data containing (at least) the following fields:
 * - plugin-version:	the current plugin-version of the delivered plugin-language on the LinkLift-server
 * - plugin-date:		the rlease-date of the current plugin-version
 * - plugin-language:	the plugin-language of the plugin-version
 * 
 * class-method ("static")
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2008-02-16
 * @return $cms_info string additional CMS-info to be sent to the LinkLift-server; usually this contains the version of the CMS this plugin is used in; default: "".
 * @return array, associative array of plugin-data, see above for more information  <OR>  false if an error occurred.
 */
function ll_get_current_plugin_info( $cms_info = "" )
{
	$server_host = LL_SERVER_HOST;
	
	
	$linklift_website_key	= urlencode(LL_WEBSITE_KEY);
	$linklift_secret		= urlencode(LL_PLUGIN_SECRET);
	$linklift_method		= urlencode("current_plugin_version");
	$cms_info_encoded		= urlencode($cms_info);
	
	$request =   "GET /external/external_info.php5"
				. "?website_key"				. "=" . $linklift_website_key
				. "&linklift_secret"			. "=" . $linklift_secret
				. "&method"					. "=" . $linklift_method
				. "&plugin_language"			. "=" . urlencode(LL_PLUGIN_LANGUAGE)
				. "&plugin_version"			. "=" . urlencode(LL_PLUGIN_VERSION)
				. "&plugin_date"				. "=" . urlencode(LL_PLUGIN_DATE)
				. "&cms_info"					. "=" . $cms_info_encoded
				. " "
				
				. "HTTP/1.0\n"
				. "Host: " . $server_host . "\n"
				. "Connection: Close\n"
				. "\n"
				
				. "";
	
	
	$plugin_info_raw = LinkLiftPlugin::get_page_content( $server_host, $request );
	
	
	if (empty($plugin_info_raw))
		return false;
	
	
	
	
	// splits the received page in header and content
	$file_parts			= preg_split( $pattern = '@\r?\n\r?\n@', $subject = $plugin_info_raw, $limit = 2 );
	
	$body				=& $file_parts[1];
	
	// every line of the page's body is expected to contain one information
	$plugin_info_lines	= preg_split( $pattern = '@\r?\n@', $subject = $body );
	
	// every valid information is put into an associative array $plugin_info
	$plug_info 			= array();
	foreach ($plugin_info_lines as $line)
	{
		$line_parts		= explode( ":", $line, $limit = 2 );
		
		if (2 === count($line_parts))
			$plug_info[ $line_parts[0] ] = $line_parts[1];
	} //foreach($line)
	
	
	
	return $plug_info;
} //ll_get_current_plugin_info()

/**
 * Returns a URL that this plugin can be updated with.
 * 
 * class-method ("static")
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2008-02-16
 * @return string the URL to call in order to update this plugin
 */
function ll_get_update_plugin_url()
{
	$linklift_website_key	= urlencode(LL_WEBSITE_KEY);
	$linklift_secret		= urlencode(LL_PLUGIN_SECRET);
	$linklift_method		= urlencode("current_plugin_download");
	
	$request =    LL_SERVER_URL
				. "/external/external_info.php5"
				. "?website_key"				. "=" . $linklift_website_key
				. "&linklift_secret"			. "=" . $linklift_secret
				. "&method"					. "=" . $linklift_method
				. "&plugin_language"			. "=" . urlencode(LL_PLUGIN_LANGUAGE)
				. "&plugin_version"			. "=" . urlencode(LL_PLUGIN_VERSION)
				
				. "";
	
	
	return $request;
} //ll_get_update_plugin_url()

/**
 * The method returns the server's very own 404-page including the page's headers as string!
 * Therefor, a not-existing page is requested from the local server using the same accapt-language as the one delivered to this request.
 * The method is invoked by return_error().
 * class-method ("static")
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @return string the server's very own 404-page including the page's headers
 */
function get_404_page()
{
	$server_host = $_SERVER["SERVER_NAME"];
	
	
	$language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	
	// asking for the same language that was delivered to this request
	if (! empty($language))
		$acceptLanguage = "Accept-Language: " . $language . "\r\n";
	else
		$acceptLanguage = "";
	
	// using carriage returns since Windows-servers may not understand simple newlines (while Linux-servers do not seem to have problems with carriage returns)
	$request =	  "GET /this_file_does_not_exist.php "
				
				. "HTTP/1.0\r\n"
				. "Host: " . $server_host . "\r\n"
				. $acceptLanguage
				. "Connection: Close\r\n"
				. "\r\n"
				
				. "";
	
	// possible errors ignored!
	$page404 = LinkLiftPlugin::get_page_content( $server_host, $request );
	
	
	return $page404;
} //get_404_page()

/**
 * The method returns an error-page to a faulty or malicious request.
 * For $errorCode 404 the local server's very own 404-page is requested and returned.
 * The method does not return any value, but set headers and print the generated page's body to standard-out, automatically.
 * class-method ("static")
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @param $error_code int The HTTP-Code to be returned and set as page-header; default: 404.
 * @param $error_message string The main error-message to be returned as the page's body; default: "Illegal Request".
 * @param $headers_to_set array, associative array of strings containing headers to be set when exiting
 * @return void
 */
function return_error( $error_code = 404, $error_message = "Illegal Request", $headers_to_set = array() )
{
	$error_strings = array	(
							  400 => "Bad Request"
							, 401 => "Unauthorized"
							, 404 => "Not Found"
							, 405 => "Method Not Allowed"
							, 406 => "Not Acceptable"
							, 409 => "Conflict"
							, 500 => "Internal Server Error"
							);
	
	
	if (404 == $error_code)
	{
		$page404		= LinkLiftPlugin::get_404_page();
		
		// splits the received 404-page in header and content
		$file_parts		= preg_split( $pattern = '@\r?\n\r?\n@', $subject = $page404, $limit = 2 );
		
		$headers		= $file_parts[0];
		$body			= $file_parts[1];
		
		
		// setting HTTP-headers individually
		$headers_array	= explode("\n", $headers);
		foreach ($headers_array as $header)
			header( $header );
		
		
		// writing the page's body
		die( $body );
	} //if
	
	
	
	$error_string = ( (isset($error_strings[$error_code])) ? (" " . $error_strings[$error_code]) : ("") );
	
	header("HTTP/1.x " . $error_code . $error_string );
	
	
	foreach ($headers_to_set as $header => $value)
		header( $header . ": " . $value );
	
	
	
	die( $error_message );
} //return_error()

/**
 * This plugin is not supposed to be called directly, but to be used as inclusion or part of a surrounding CMS.
 * Direct requests will be responded to with an error, protecting this plugin from prying eyes.
 * The method does not return any value, but set headers and print the generated page's body to standard-out, automatically.
 * class-method ("static")
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @param $check_this_file boolean Set this flag if the check-routine should also disallow direct request to the file this script is a part of (if you have included the LinkLift-plugin in a bigger PHP-script set this parameter to false, for usage within a CSS you can set this flag to true); default: false.
 * @return void
 */
function check_request( $check_this_file = false )
{
	// direct access to the LinkLift-plugin-file is not wanted!
	if (   (   (false !== strpos( strtolower($_SERVER["SCRIPT_FILENAME"])	, strtolower("linklift_refdll.php") ))
			|| (false !== strpos( strtolower(getenv("SCRIPT_NAME"))			, strtolower("linklift_refdll.php") ))
			)
		&& (! LL_SECRET_MODE)
		)
	{
		LinkLiftPlugin::return_error( $errorCode = 404 );
	} //if
	
	if ($check_this_file)
	{
		// direct access to the LinkLift-plugin-file is not wanted!
		if (   (   (false !== strpos( strtolower($_SERVER["SCRIPT_FILENAME"])	, strtolower( basename(__FILE__) ) ))
				|| (false !== strpos( strtolower(getenv("SCRIPT_NAME"))			, strtolower( basename(__FILE__) ) ))
				)
			&& (! LL_SECRET_MODE)
			)
		{
			LinkLiftPlugin::return_error( $errorCode = 404 );
		} //if
	} //if
} //check_request()





/**
 * Setter-method for property "linklift_website_key".
 * The class'-properties are private in PHP5.
 * 
 * The method is invoked by the class' constructor.
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2008-01-02
 * @param $instance_linklift_website_key string The new linklift-website-key for this instance.
 * @return string The former value of the property "linklift_website_key".
 */
function setWebsiteKey( $instance_linklift_website_key )
{
	$current_linklift_website_key	= $this->getWebsiteKey();
	
	$this->linklift_website_key		= $instance_linklift_website_key;
	
	return $current_linklift_website_key;
} //setWebsiteKey()

/**
 * Getter-method for property "linklift_website_key".
 * The class'-properties are private in PHP5.
 * 
 * Usually, the property is set once by the constructor and remains unchanged afterwards.
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-10
 * @return string The current value of the linklift_website_key-property.
 */
function getWebsiteKey()
{
	return $this->linklift_website_key;
} //getWebsiteKey()

/**
 * Setter-method for property "xml_filename".
 * The class'-properties are private in PHP5.
 * 
 * The method checks wether a linklift-website-key is defined for this instance
 *   and extends the given filename by adding the current linklift-website-key as postfix.
 * Thus, one plugin may work with and cache textlink-data belonging to different linklift-website-keys.
 * 
 * The method is invoked by the class' constructor.
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2008-01-02
 * @param $instance_xml_filename string The new XML-filename that is used when textlink-data that has been downloaded from the LinkLift-server is cached.
 * @return string The former value of the property "xml_filename".
 */
function setXmlFilename( $instance_xml_filename = "" )
{
	$current_xml_filename			= $this->getXmlFilename();
	
	$instance_linklift_website_key	= $this->getWebsiteKey();
	
	if (! empty($instance_linklift_website_key))
	{
		$xml_filename_length	= LinkLiftPlugin::ll_call_str_function_encoding_dependent("strlen", array($instance_xml_filename));
		
		if (   ( 0 >= $xml_filename_length)
			|| ( false === ($file_format_start = LinkLiftPlugin::ll_call_str_function_encoding_dependent("strrpos", array($instance_xml_filename, ".") )) )
			)
		{
			$file_format_start	= $xml_filename_length;
		} //if
			
		$instance_xml_filename	=	  LinkLiftPlugin::ll_call_str_function_encoding_dependent("substr", array($instance_xml_filename, 0, $file_format_start))
									. ( (0 < $file_format_start) ? ("_") : ("") )
									. $instance_linklift_website_key
									. LinkLiftPlugin::ll_call_str_function_encoding_dependent("substr", array($instance_xml_filename, $file_format_start, $xml_filename_length))
									. "";
	} //if
	
	$this->xml_filename = $instance_xml_filename;
	
	return $current_xml_filename;
} //setXmlFilename()

/**
 * Getter-method for property "xml_filename".
 * The class'-properties are private in PHP5.
 * 
 * Usually, the property is set once by the constructor and remains unchanged afterwards.
 * Some CMS-plugins, though, will set the XML-filename later when having access to the CMS'-database.
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2008-01-02
 * @return string The current value of the xml_filename-property.
 */
function getXmlFilename()
{
	return $this->xml_filename;
} //getXmlFilename()

/**
 * Setter-method for property "links_to_show".
 * The class'-properties are private in PHP5.
 * 
 * The method checks wether the given value is an array. If not an empty array is used as new value.
 * 
 * The method is invoked by the class' constructor.
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2008-01-02
 * @param $instance_links_to_show array Array of integers, holding the new value of links to be shown by this instance.
 * @return array The former value of the property "links_to_show".
 */
function setLinksToShow( $instance_links_to_show = "" )
{
	$current_instance_links_to_show	= $this->getLinksToShow();
	
	$this->links_to_show			= ( (is_array($instance_links_to_show)) ? ($instance_links_to_show) : (array()) );
	
	return $current_instance_links_to_show;
} //setLinksToShow()

/**
 * Getter-method for property "links_to_show".
 * The class'-properties are private in PHP5.
 * 
 * Usually, the property is set once by the constructor and remains unchanged afterwards.
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2008-01-02
 * @return string The current value of the links_to_show-property.
 */
function getLinksToShow()
{
	return $this->links_to_show;
} //getLinksToShow()

/**
 * Setter-method for property "xml_cache_time".
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @param $xml_cache_time int The time the cached XML has been updated for the last time; default: NOW / time().
 * @return void
 */
function setXmlCacheTime( $xml_cache_time = -1 )
{
	if (0 > $xml_cache_time)
		$xml_cache_time = time();
	
	$this->xml_cache_time = $xml_cache_time;
} //setXmlCacheTime()

/**
 * Getter-method for property "xml_cache_time".
 * Note: in LL_DEBUG_MODE = 3 this method will always return 0 in order to force an update of the XML-cache.
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @return int The time the cached XML has been updated for the last time.
 */
function getXmlCacheTime()
{
	if (5 == LL_DEBUG_MODE)
		return 0;
	else
		return $this->xml_cache_time;
} //getXmlCacheTime()

/**
 * Setter-method for property "xml_cache".
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @param $xml_cache string The XML-string to be cached / that is cached.
 * @param $update_time boolean Set this flag in order to let the method update the xml_cache_time to NOW / time(); default: false.
 * @return void
 */
function setXmlCache( $xml_cache, $update_time = false )
{
	$this->xml_cache = $xml_cache;
	
	if ($update_time)
		$this->setXmlCacheTime();
} //setXmlCache()

/**
 * Getter-method for property "xml_cache".
 * Note: in LL_DEBUG_MODE = 3 this method will always return 0 in order to force an update of the XML-cache.
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @return string The currently cached XML.
 */
function getXmlCache()
{
	return $this->xml_cache;
} //getXmlCache()


/**
 * This method outputs an instance's current state by returning the values of its fields and constants.
 * Use it for debugging-information.
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @return string, the object's current state as string
 */
function __toString()
{
	$seperator		= " / ";
	
	$return_array	= array();
	
	$return_array[]	= "LinkLift Plugin";
	$return_array[]	= "Linklift-Website-Key: "	. $this->getWebsiteKey();
	$return_array[]	= "XML-Filename: "			. $this->getXmlFilename();
	$return_array[]	= "Links to show: "			. implode(",", $this->getLinksToShow());
	$return_array[]	= "XML-cache update time: "	. $this->getXmlCacheTime() . " (" . date("Y-m-d H:i:s", $this->getXmlCacheTime()). ")";
	$return_array[]	= "Data Timeout: "			. $this->data_timeout;
	$return_array[]	= "Plugin Language: "		. $this->plugin_language;
	$return_array[]	= "Plugin Version: "		. $this->plugin_version;
	$return_array[]	= "Plugin Date: "			. $this->plugin_date;
	
	$return_str		= implode($seperator, $return_array);
	
	return $return_str;
} //__toString()


/**
 * The method retrieves textlink-data to your adspace from the LinkLift-server.
 * The data is received in XML-format and contains information about all textlinks currently booked on your adspace.
 * The received XML is saved as instance-property xml_cache.
 * The method is invoked by ll_textlink_code() if the local XML-file is not existent or out-of-date.
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2006-09-18
 * @return void
 */
function ll_retrieve_xml_from_ll_server()
{
	$server_host = LL_SERVER_HOST;
	
	
	$linklift_website_key	= urlencode($this->getWebsiteKey());
	$linklift_secret		= urlencode(LL_PLUGIN_SECRET);
	
	$request =   "GET /external/textlink_data.php5"
				. "?website_key"				. "=" . $linklift_website_key
				. "&linklift_secret"			. "=" . $linklift_secret
				. "&plugin_language"			. "=" . urlencode($this->plugin_language)
				. "&plugin_version"			. "=" . urlencode($this->plugin_version)
				. "&plugin_date"				. "=" . urlencode($this->plugin_date)
				. "&http_request_uri"			. "=" . ( (isset($_SERVER["REQUEST_URI"])    ) ? (urlencode($_SERVER["REQUEST_URI"])    ) : ("") )
				. "&http_user_agent"			. "=" . ( (isset($_SERVER["HTTP_USER_AGENT"])) ? (urlencode($_SERVER["HTTP_USER_AGENT"])) : ("") )
				. "&linklift_title"			. "=" . urlencode("")
				. "&condition_no_css"			. "=" . ( (true) ? ("1") : ("0") )
				. "&condition_no_html_tags"	. "=" . ( (false) ? ("1") : ("0") )
				. " "
				
				. "HTTP/1.0\n"
				. "Host: " . $server_host . "\n"
				. "Connection: Close\n"
				. "\n"
				
				. "";
	
	
	// possible errors lead to the xmlCache not being (re-)set
	$xml = LinkLiftPlugin::get_page_content( $server_host, $request );
	
	
	
	// saving the received XML to instance-property "xml_cache"
	if (false !== strpos($xml, "<?xml"))
		$this->setXmlCache( strstr($xml, "<?xml"), $update_time = true );
} //ll_retrieve_xml_from_ll_server()

/**
 * The method parses the textlink-data to your adspace out of a String given in XML-format.
 * Usually, the delivered string contains the XML-data either just received from the LinkLift-server or read out of the local XML-file.
 * The method returns a multi-dimensional Array of textlinks containing information like link_url, link_text, link_prefix, link_postfix, and so on.
 * The method is invoked by ll_textlink_code() after calling either ll_retrieve_xml_from_ll_server() or ll_retrieve_xml_from_file_systems().
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2006-09-18, 2006-12-03, 2007-12-10
 * @param $xml string The textlink-data as String and in XML-format that either has just been received from the LinkLift-server or read out of the local XML-file, may be left empty in order to save the instance's xml_cache.
 * @return array of textlink-data. Each element is representing one actual textlink on your website in that it contains information like link_url, link_text, link_prefix, link_postfix, and so on.
 */
function ll_retrieve_textlink_data_from_xml( $xml = "" )
{
	if (empty($xml))
		$xml = $this->getXmlCache();
	
	
	// well-formedness of XML is assumed!
	$textlink_data_fields =
				array(	
						  "prefix"
						, "url"
						, "text"
						, "postfix"
						
						, "rss_prefix"
						, "rss_url"
						, "rss_text"
						, "rss_postfix"
					);
	
	$plugin_fields =
				array(	
						  "language"
						, "version"
						, "pl_date"
					);
	
	$xml_fields = array_merge($textlink_data_fields, $plugin_fields);
	
	
	
	$parsed_xml_array = array();
	foreach ($xml_fields as $field)
	{
		preg_match_all ('!<' . $field . '[^>]*>(.*?)</' . $field . '>!im', $xml, $parsed_xml_array[$field], PREG_SET_ORDER);
	} //foreach($field)
	
	
	
	// ### Extracting plugin-data #################################################
	
	$server_plugin   = array();
	$plugin_to_save  = -1;
	foreach ($plugin_fields as $field)
	{
		foreach ($parsed_xml_array[$field] as $nr => $value)
		{
			if (   ("language" == $field)
				&& ($this->plugin_language == $value[1])   )
			{
				$plugin_to_save = $nr;
			} //if;
			
			$server_plugin[$nr][$field] = $value[1];
		} //foreach($value)
	} //foreach($field)
	
	if (! empty($server_plugin[$plugin_to_save]))
		usleep(0);  // void / doing nothing. No saving of plugin-properties for this plugin-language.
	
	
	
	// ### Extracting textlink-data ###############################################
	
	$textlink_data   = array();
	foreach ($textlink_data_fields as $field)
	{
		foreach ($parsed_xml_array[$field] as $nr => $value)
		{
			$parsed_value = $value[1];
			
			// LinkLift may use CData-elements in its XML-feeds that can be recognised by XML-parsers.
			$parsed_value = str_replace(array("<![CDATA[", "]]>"), "", $parsed_value);
			
			$textlink_data[$nr][$field] = $parsed_value;
		} //foreach($value)
	} //foreach($field)
	
	
	return $textlink_data;
} //ll_retrieve_textlink_data_from_xml()

/**
 * The method retrieves textlink-data to your adspace from the local XML-file (i.e. the file-system).
 * The data is read in XML-format and contains information about all textlinks currently booked on your adspace.
 * Usually, the local XML-file gets updated after a certain period of time by calling ll_retrieve_xml_from_ll_server().
 * The read XML is saved as instance-property xml_cache.
 * The method is invoked by ll_textlink_code() if there is a local XML-file and it is not out-of-date.
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2006-09-18
 * @return void
 */
function ll_retrieve_xml_from_file_system()
{
	$xml = "";
	
	$xml_filename = $this->getXmlFilename();
	
	if (   (file_exists($xml_filename))
		&& (is_readable($xml_filename))
		&& ($xmlfile = fopen($xml_filename, "r"))   )
	{
		$xml = fread($xmlfile, filesize($xml_filename));
		fclose($xmlfile);
		
		
		$this->setXmlCache( $xml, $update_time = false );
	} //if
} //ll_retrieve_xml_from_file_system()

/**
 * The method writes textlink-data to your adspace into the local XML-file (i.e. the file-system).
 * The data is received in XML-format and contains information about all textlinks currently booked on your adspace.
 * Usually, the delivered textlink-data has just been received, i.e. downloaded, from the LinkLift-server using ll_retrieve_xml_from_ll_server().
 * The method is invoked by ll_textlink_code() after calling ll_retrieve_xml_from_ll_server() and if the received data exceeds a certain length (of bytes).
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2006-09-18
 * @param $xml string The textlink-data in XML-format that, usually, has just been received from the LinkLift-server, may be left empty in order to save the instance's xml_cache.
 * @return void
 */
function ll_write_xml_to_file_system( $xml = "" )
{
	if (empty($xml))
		$xml = $this->getXmlCache();
	
	$xml_filename = $this->getXmlFilename();
	
	if ($xmlfile = fopen($xml_filename, "w"))
	{
		fwrite($xmlfile, $xml);
		fclose($xmlfile);
	} //if
} //ll_write_xml_to_file_system()

/**
 * The method generates a textlink-array like the one created by ll_retrieve_textlink_data_from_xml() out of LinkLift's XML-feed.
 * If no debug-mode is active the generated array will be empty, otherwise, contain a textlink depending on the current debug-mode.
 * The method is invoked by ll_textlink_code().
 * 
 * @author akniep (Andreas Rayo Kniep)
 * @since 2007-12-17
 * @return array associative array containing textlinks that can be displayed within the generated HTML-code
 */
function get_debug_links()
{
	$debug_links = array();
	
	if ( LL_DEBUG_MODE )
	{
		if (function_exists( "md5" ))
			$md5_answer = md5( LL_PLUGIN_SECRET );
		else
			$md5_answer = "";
		
		// displaying an "answer" to the DEBUG_MODE-request
		if (1 != LL_DEBUG_MODE)
			$debug_links[] = array("text" => "Debug-Mode {" . LL_DEBUG_MODE . "} - (" . $md5_answer . ")", "url" => "http://www.linklift.com/", "prefix" => "[-- ", "postfix" => " --]");
		
		
		
		// see above for the purposes of the following debug-modes
		switch( LL_DEBUG_MODE )
		{
			case (1):
				$debug_links[] = array("text" => "30 Chars TextLink text - &#228;&#223;&#263;&#322;&#261;", "url" => "http://www.linklift.com/", "prefix" => "", "postfix" => "");
				break;
			
			
			case (2):
				$debug_links[] = array("text" => "<!--" . $this->__toString() . "-->", "url" => "http://www.linklift.com/somefolder/someSecondFolder/some-third-folder/someFile.php?somequery=1&someQuery=value&some-query=true&", "prefix" => "", "postfix" => "");
				break;
			
			
			case (3):
				$debug_links[] = array("text" => "<!--" . $this->getXmlCache() . "-->", "url" => "http://www.linklift.com/", "prefix" => "", "postfix" => "");
				break;
			
			
			case (4):
				$external_array = array(  "LL_WEBSITE_KEY"
										, "LL_PLUGIN_LANGUAGE"
										, "LL_PLUGIN_VERSION"
										, "LL_PLUGIN_DATE"
										, "LL_PLUGIN_SECRET"
										, "LL_DATA_TIMEOUT"
										);
				$external_array = array_map( create_function('$element', 'return $element . "=" . constant($element);'), $external_array );
				
				$external_data =  implode( " / ", $external_array );
				
				
				$debug_links[] = array("text" => "<!--" . $external_data . "-->", "url" => "http://www.linklift.com/", "prefix" => "", "postfix" => "");
				break;
			
			
			case (10):
				$debug_links[] = array("text" => "<!--" . __FILE__ . "-->", "url" => "http://www.linklift.com/", "prefix" => "", "postfix" => "");
				break;
			
			
			
			
			
			case (99):
				$debug_links[] = array("text" => "1,2,3,4,5,10,99", "url" => "http://www.linklift.com/", "prefix" => "", "postfix" => "");
				break;
			
		} //switch(LL_DEBUG_MODE)
	} //if
	
	return $debug_links;
} //get_debug_links()

/**
 * This method is the actual "main"-method of the LinkLift-plugin.
 * The method
 *  - retrieves textlink-data to your adspace from the LinkLift-server and stores it in a local XML-file (for reuse) - calling ll_retrieve_xml_from_ll_server();
 *      or retrieves the textlink-data from that local XML-file in order to minimize outbound-traffic - calling ll_retrieve_xml_from_file_systems().
 *  - parses the downloaded or read textlink-data in XML-format into an utilisable array of textlinks - calling ll_retrieve_textlink_data_from_xml().
 *  - generates and outputs plain HTML-links using some CSS-styles in order to obtain the looks you chose on the LinkLift-website;
 *      intentionally, the generated code tries to be as ordinary as possible in order to integrate best with your own HTML-code.
 * 
 * No value is returned since the generated HTML-code is directly outputted to your website (except for delivering $return = true).
 * The method is invoked either at the end of the plugin (using PHP-plugin) or at the position of your choice within your website or blog (using one of the CMS-/Blog-software-plugins).
 *
 * @author akniep (Andreas Rayo Kniep)
 * @since 2006-09-18, 2006-12-03, 2007-10-26
 * @param $return boolean Indicating whether the generated HTML-code should be returned ($return == true), or written to standard-out (echo); default: false.
 * @return the generated HTML-code containing your current textlinks, or void if ($return == false), then, HTML will be written to standard-out.
 */
function ll_textlink_code( $return = false )
{
	$linklift_website_key = $this->getWebsiteKey();
	
	
	$xml_filename = $this->xml_filename;
	
	// checking local XML file
	if (! file_exists($xml_filename))
	{
		if ($createTest = fopen($xml_filename, "a"))
		{
			fclose($createTest);
		} else {
			if (LL_DEBUG_MODE)
				$die_message = "[LinkLift] Error:  ".$xml_filename." does not exist and can not be created. Please create a writeable file called ".$xml_filename.".";
			else
				$die_message = "";
			
			if ($return)
				return $die_message;
			else
				echo $die_message;
			return;
		} //if-else
	} //if
	if (   (! is_file($xml_filename))
		|| (! is_writable($xml_filename))
		|| (! is_readable($xml_filename))   )
	{
		if (LL_DEBUG_MODE)
			$die_message = "[LinkLift] Error:  ".$xml_filename." is not a writable (and readable) file. Please create a writeable file called ".$xml_filename.".";
		else
			$die_message = "";
		
		if ($return)
			return $die_message;
		else
			echo $die_message;
		return;
	} //if
	
	
	// retrieving data from LL-server
	if (   ($this->getXmlCacheTime() < time() - 3600)
		|| (40 > filesize($xml_filename))   )
	{
		$this->ll_retrieve_xml_from_ll_server();
	} //if
	
	
	// storing/retrieving data to/from local XML-file
	if (40 < strlen( $this->getXmlCache() ))
		$this->ll_write_xml_to_file_system();
	else
		$this->ll_retrieve_xml_from_file_system();
	
	
	
	// parsing XML-data
	$textlink_data = $this->ll_retrieve_textlink_data_from_xml();
	
	if (! is_array($textlink_data))
		return "";
	
	// a possible debug-mode helping to analyse problems or functionality of the plugin
	$debug_links = $this->get_debug_links();
	$textlink_data = array_merge( $debug_links, $textlink_data );
	
	// filtering testlinks, links that should not be shown or are shown elsewhere, and special LinkLift-links
	foreach ($textlink_data as $key => $link)
	{
		// if a certain subset of textlinks has been specified by links_to_show, the current textlink has to be part of it in order to be shown
		$links_to_show = $this->getLinksToShow();
		if (   (! empty($links_to_show))
			&& (! in_array($key, $links_to_show))   )
		{
			unset($textlink_data[$key]);
		} //if
		
		// filtering testlinks and special LinkLift-links, these are links containing the plugin's secret in their URL ...
		if (   (0 < strlen(LL_PLUGIN_SECRET))
			&& (false !== strpos($link["url"], LL_PLUGIN_SECRET))
			&& (! LL_SECRET_MODE)   )
		{
			unset($textlink_data[$key]);
		} //if
	} //foreach($link)
	
	// if there are no textlinks at this time the plugin will not display anything.
	if (0 >= count($textlink_data))
		return "";
	
	
	
	
	// creating and outputting textlinks
	// generating HTML-links
	// ---------------------------------------------------------------------------v
	
	// the appearance of the generated HTML-links depends on
		// - the default values as chosen on LinkLift's own plug-in-generation-panel
		// - the parameters that have been chosen within the configuration page of your LinkLift-plugin (if possible)
	// --- CSS-parameters ----------------------------------------
	$number_of_links_per_row = 3;
	
	$styles_ul   = array();
	$styles_li   = array();
	$styles_a    = array();
	
	$styles_li[] = 'width:' . floor(100 / $number_of_links_per_row -1) . '%;';
	
	$styles_ul[] = "width:100%;";
	$styles_ul[] = "padding:0px;";
	$styles_ul[] = "background-color:;";
	$styles_ul[] = "margin-right:0px;";
	$styles_ul[] = "border:0px none #FFFFFF;";
	$styles_ul[] = "margin-top:0px;";
	$styles_ul[] = "overflow:hidden;";
	$styles_ul[] = "list-style:none;";
	$styles_ul[] = "margin-bottom:0px;";
	$styles_ul[] = "border-spacing:0px;";
	
	
	$styles_li[] = "display:inline;";
	$styles_li[] = "float:left;";
	$styles_li[] = "clear:none;";
	
	
	$styles_a[] = "color:;";
	$styles_a[] = "line-height:140%;";
	$styles_a[] = "font-size:16px;";
	
	
	
	
	
	
	// --- style-attributes --------------------------------------
	if (function_exists("array_filter"))
	{
		$styles_ul = array_filter( $styles_ul, create_function('$style', 'return (strpos($style,":;") === false);') );
		$styles_li = array_filter( $styles_li, create_function('$style', 'return (strpos($style,":;") === false);') );
		$styles_a  = array_filter( $styles_a , create_function('$style', 'return (strpos($style,":;") === false);') );
	} //if
	
	// --- style-attributes --------------------------------------
	$css_ul      = ' style="' . implode(' ', $styles_ul) . '"';
	$css_li      = ' style="' . implode(' ', $styles_li) . '"';
	$css_a       = ' style="' . implode(' ', $styles_a ) . '"';
	
	// the following condition will evaluate to true
		// if you have chosen not to use CSS-styles within the generated HTML-links
	if (true)
	{
		$css_ul = '';
		$css_a  = '';
		
		if ($number_of_links_per_row <= 1)
			$css_li = '';
	} //if-else
	
	
	
	// --- HTML-Tags ---------------------------------------------
	$tag_ul1     = '<ul' . $css_ul . '>';
	$tag_ul2     = '</ul>';
	$tag_li1     = '<li' . $css_li . '>';
	$tag_li2     = '</li>';
	
	// the following condition will evaluate to true
		// if you have chosen not to use the HTML-tags <ul> and <li> within the generated HTML-links
	if (false)
	{
		$tag_ul1 = '';
		$tag_ul2 = '';
		$tag_li1 = '';
		$tag_li2 = '<br />';
	} //if-else
	
	
	
	// --- HTML --------------------------------------------------
	$line_break		= "\r\n";
	$indentation	= "";
	
	$output 		= $line_break
					. $indentation
					
					. $tag_ul1
					. $line_break;
	
	foreach ($textlink_data as $key => $link)
	{
		$output    .= $indentation
					. ( (empty($line_break)) ? ("") : ("\t") )			// no indentation if there are no linebreaks
					
					. $tag_li1
					. 	$link["prefix"]
					. 	'<a' . $css_a . ' href="'.$link["url"].'">'		// you may add  target="_self"  in order to give the link full strength
					. 		$link["text"]
					. 	'</a>'
					. 	$link["postfix"]
					. $tag_li2
					
					. $line_break;
	} //foreach($link)
	
	$output 	   .= $indentation
					
					. $tag_ul2
					. $line_break;
	
	// ---------------------------------------------------------------------------^
	
	
	
	// usually, the generated HTML-content is written to standard-out
		// but, you may choose that the method returns the code
	if ($return)
		return $output;
	else
		echo $output;
	
} //ll_textlink_code()




/*wpee
 *
 */

} //class(LinkLiftPlugin)

} //if (! class_exists(LinkLiftPlugin))




//if (is_callable("LinkLiftPlugin", "check_request"))
	LinkLiftPlugin::check_request($check_this_file = false);

//if (is_callable(array("LinkLiftPlugin", "execute")))  
	LinkLiftPlugin::execute( $return = false );

?>

    </div>
<script type="text/javascript"><!--
google_ad_client = "pub-3213363904178661";
/* jwchat.org */
google_ad_slot = "5628366605";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
    </td>
	  </tr>
    <tr>
      <td id='td_bottom'>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
              <a href="http://blog.jwchat.org/jwchat/">Download</a> | <a href="http://blog.jwchat.org">Blog</a> | <a href="imprint.html"><l>Imprint</l></a> | <a href="about.html"><l>About</l></a>
               <br>
                &copy; 2003-2008 <a href="mailto:steve@zeank.in-berlin.de">Stefan Strigler</a>
            </td>
            <td align="right">
              <a href="http://sourceforge.net/donate/index.php?group_id=92011"><img src="http://images.sourceforge.net/images/project-support.jpg" width="88" height="32" border="0" alt="Support This Project" align="right" /></a>
              <iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fjwchat.org&amp;layout=button_count&amp;show_faces=true&amp;width=90&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:21px;" allowTransparency="true"></iframe>
              <a class="FlattrButton" style="display:none;" rev="flattr;button:compact;"  href="http://jwchat.org"></a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  </body>
</html>
