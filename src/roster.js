function RosterGroup(name) {
  this.name = htmlEnc(name);
  this.users = new Array();
  this.onlUserCount = 0;
  this.messagesPending = 0;
}

function RosterUserAdd2Group(group) {
  this.groups = this.groups.concat(group);
}

function RosterUser(jid,subscription,groups,name) {

  this.fulljid = jid;
  this.jid = cutResource(jid) || 'unknown';
  this.jid = this.jid.toLowerCase(); // jids are case insensitive

  this.subscription = subscription || 'none';
  this.groups = groups || [''];

  if (name)
    this.name = name;
  else if (this.jid == JABBERSERVER)
    this.name = loc("System");
  else if ((this.jid.indexOf('@') != -1) && 
           this.jid.substring(this.jid.indexOf('@')+1) == JABBERSERVER) // we found a local user
    this.name = this.jid.substring(0,jid.indexOf('@'));
  else
    this.name = this.jid;

  this.name = htmlEnc(this.name);

  // initialise defaults
  this.status = (this.subscription == 'from' || this.subscription == 'none') ? 'stalker' : 'unavailable';
  this.statusMsg = null;
  this.lastsrc = null;
  this.messages = new Array();
  this.chatmsgs = new Array();
  this.chatW = null; // chat window

  // methods
  this.add2Group = RosterUserAdd2Group;

}

function getElFromArrByProp(arr,prop,str) {
  for (var i=0; i<arr.length; i++) {
    if (arr[i][prop] == str)
      return arr[i];
  }
  return null;
}

function getRosterGroupByName(groupName) {
  return getElFromArrByProp(this.groups,"name",groupName);
}

function getRosterUserByJID(jid) {
  return getElFromArrByProp(this.users,"jid",jid.toLowerCase());
}

function RosterUpdateStyleIE() {
  if(!is.ie)
    return;
  this.rosterW.getElementById("roster").style.width = this.rosterW.body.clientWidth;
}

function RosterGetUserIcons(from) {
  var images = new Array();
  
  for (var i=0; i<this.groups.length; i++) {
    var img = this.rosterW.images[from+"/"+this.groups[i].name];
    if (img) {
      images = images.concat(img);
      continue; // skip this group
    }
  }
  return images;
}

function RosterToggleHide() {
  this.usersHidden = !this.usersHidden;
  this.print();
  return;
}
	
function RosterToggleGrp(name) {
  var el = this.rosterW.getElementById(name);
  if (el.className == 'hidden') {
    el.className = 'rosterGroup';
    this.hiddenGroups[name] = false;
    this.rosterW.images[name+"Img"].src = grp_open_img.src;
  } else {
    el.className = 'hidden';
    this.hiddenGroups[name] = true;
    this.rosterW.images[name+"Img"].src = grp_close_img.src;
  }
  this.updateStyleIE();
}

function RosterOpenMessage(jid) {
  var user = this.getUserByJID(jid);
  var wName = makeWindowName(user.jid); 

  if (user.messages.length > 0 && (!user.mW || user.mW.closed)) // display messages
    user.mW = open('message.html?jid='+escape(jid),
                   "mw"+wName,
                   'width=360,height=270,dependent=yes,resizable=yes');
  else if (!user.sW || user.sW.closed) // open send dialog
    user.sW = open("send.html?jid="+escape(jid),
                   "sw"+wName,
                   'width=320,height=200,dependent=yes,resizable=yes');
  return false;
}

function RosterOpenChat(jid) {

  var user = this.getUserByJID(jid);

  if (!user)
    return;

  if (user.messages.length > 0 && (!user.mW || user.mW.closed)) // display messages
    this.openMessage(jid);
		
  if (!user.chatW || user.chatW.closed)
    user.chatW = open("chat.html?jid="+escape(jid),
                      "chatW"+makeWindowName(user.jid),
                      "width=320,height=390,resizable=yes");
  else if (user.chatW.popMsgs)
    user.chatW.popMsgs();
}

function RosterCleanUp() {
  for (var i=0; i<this.users.length; i++) {
    if (this.users[i].roster)
      this.users[i].roster.cleanUp();
    if (this.users[i].sW)
      this.users[i].sW.close();
    if (this.users[i].mW)
      this.users[i].mW.close();
    if (this.users[i].chatW)
      this.users[i].chatW.close();
    if (this.users[i].histW)
      this.users[i].histW.close();
  }
}

function RosterUpdateGroupForUser(user) {
  for (var j=0; j<user.groups.length; j++) {
    if (user.groups.length > 1 && user.groups[j] == '')
      continue;
    var groupName = (user.groups[j] == '') ? loc('Unfiled') : user.groups[j];
    var group = this.getGroupByName(groupName);
    if(group == null) {
      group = new RosterGroup(groupName);
      this.groups = this.groups.concat(group);
    }
    group.users = group.users.concat(user);
  }
}
	
function RosterUpdateGroups() {
  var oldGroups = this.groups;
  this.groups = new Array();
  for (var i=0; i<this.users.length; i++)
    this.updateGroupsForUser(this.users[i]);
  // remove elements for groups that don't exist anymore
  for (var i=0; i<oldGroups.length; i++) {
    var remove = true;
    for (var j=0; j<this.groups.length; j++) {
      if (oldGroups[i].name == this.groups[j].name) {
        remove = false;
        break;
}
    }
    if (remove) {
      var rosterEl = this.rosterW.getElementById('roster');
      var groupHeaderEl = this.rosterW.getElementById(oldGroups[i].name+'Head');
      var groupEl = this.rosterW.getElementById(oldGroups[i].name);
      rosterEl.removeChild(groupHeaderEl);
      rosterEl.removeChild(groupEl);
    }
  }
}

function RosterUserAdd(user) {
  this.users = this.users.concat(user);
	
  // add to groups
  this.updateGroupsForUser(user);
  return user;
}

function RosterRemoveUser(user) {
  var uLen = this.users.length;
  for (var i=0; i<uLen; i++) {
    if (user == this.users[i]) {
      this.users = this.users.slice(0,i).concat(this.users.slice(i+1,uLen));
      // remove user element
      for (var j=0; j < user.groups.length; j++) {
        var groupName = user.groups[j].name || 'Unfiled';
        var groupEl = this.rosterW.getElementById(groupName);
        var userEl = this.rosterW.getElementById(getUserElementId(user, user.groups[j]));
        if (groupEl && userEl) { groupEl.removeChild(userEl); }
      }
      break;
    }
  }
  this.updateGroups();
}

function RosterGetGroupchats() {
  var groupchats = new Array();
  for (var i=0; i<this.users.length; i++)
    if (this.users[i].roster)
      groupchats[groupchats.length] = this.users[i].jid+'/'+this.users[i].roster.nick;
  return groupchats;
}
	
function Roster(items,targetW) {
  this.users = new Array();
  this.groups = new Array();
  this.hiddenGroups = new Array();
  this.name = 'Roster';

  this.rosterW = targetW;
  this.lastUserSelected = null; // moved into Roster from iRoster.html and groupchat_iroster.html - sam

  /* object's methods */
  this.print = printRoster;
  this.update = updateRoster;
  this.getGroupByName = getRosterGroupByName;
  this.getUserByJID = getRosterUserByJID;
  this.addUser = RosterUserAdd;
  this.removeUser = RosterRemoveUser;
  this.updateGroupsForUser = RosterUpdateGroupForUser;
  this.updateGroups = RosterUpdateGroups;
  this.toggleGrp = RosterToggleGrp;
  this.updateStyleIE = RosterUpdateStyleIE;
  this.toggleHide = RosterToggleHide;
  this.getUserIcons = RosterGetUserIcons;
  this.openMessage = RosterOpenMessage;
  this.openChat = RosterOpenChat;
  this.cleanUp = RosterCleanUp;
  this.getGroupchats = RosterGetGroupchats;
  this.selectUser = RosterSelectUser; // moved into Roster from iRoster.html and groupchat_iroster.html - sam
  this.userClicked = RosterUserClicked;

  /* setup groups */
  if (!items)
    return;
  for (var i=0;i<items.length;i++) {
    /* if (items[i].jid.indexOf("@") == -1) */ // no user - must be a transport
    if (typeof(items.item(i).getAttribute('jid')) == 'undefined')
      continue;
    var name = items.item(i).getAttribute('name') || cutResource(items.item(i).getAttribute('jid'));
    var groups = new Array('');
    for (var j=0;j<items.item(i).childNodes.length;j++)
      if (items.item(i).childNodes.item(j).nodeName == 'group')
	if (items.item(i).childNodes.item(j).firstChild) //if stanza != <group/>
          groups = groups.concat(items.item(i).childNodes.item(j).firstChild.nodeValue);
    this.addUser(new RosterUser(items.item(i).getAttribute('jid'),items.item(i).getAttribute('subscription'),groups,name));
  }
}

function rosterSort(a,b) {
//   if (typeof(a.name) != 'string' || typeof(b.name) != 'string')
//     return 0;
  return (a.name.toLowerCase()<b.name.toLowerCase())?-1:1;
}

function printRoster() {
	
  /* update user count for groups */
  for (var i=0; i<this.groups.length; i++) {
    this.groups[i].onlUserCount = 0;
    this.groups[i].messagesPending = 0;
    for (var j=0; j<this.groups[i].users.length; j++) {
      if (this.groups[i].users[j].status != 'unavailable' && this.groups[i].users[j].status != 'stalker')
        this.groups[i].onlUserCount++;
      if (this.groups[i].users[j].lastsrc)
        this.groups[i].messagesPending++;
    }
  }

  this.groups = this.groups.sort(rosterSort);

  if (this.rosterW.getElementById('roster').innerHTML.search(/\S/) > -1) {
    this.update(); // update dom, rather than redrawing roster
    return;
  }

  var A = new Array();

	/* ***
	 * loop rostergroups 
	 */
  for (var i=0; i<this.groups.length; i++) {

    var rosterGroupHeadClass = (this.usersHidden && 
				this.groups[i].onlUserCount == 0 && 
				this.groups[i].messagesPending == 0 && 
				this.groups[i].name != loc("Gateways")) 
      ? 'rosterGroupHeaderHidden':'rosterGroupHeader';
    A[A.length] = "<div id='";
		A[A.length] = this.groups[i].name;
		A[A.length] = "Head' class='";
		A[A.length] = rosterGroupHeadClass;
		A[A.length] = "' onClick='toggleGrp(\"";
		A[A.length] = this.groups[i].name;
		A[A.length] = "\");'><nobr>";
    var toggleImg = (this.hiddenGroups[this.groups[i].name])?'images/group_close.gif':'images/group_open.gif';
    A[A.length] = "<img src='";
		A[A.length] = toggleImg;
		A[A.length] ="' name='";
		A[A.length] = this.groups[i].name;
		A[A.length] = "Img'> ";
    A[A.length] = this.groups[i].name;
		A[A.length] = " (<span id='";
		A[A.length] = this.groups[i].name;
		A[A.length] = "On'>";
		A[A.length] = this.groups[i].onlUserCount;
		A[A.length] = "</span>/<span id='";// put total number in span - sam
    A[A.length] = this.groups[i].name;
    A[A.length] = "All'>";
		A[A.length] = this.groups[i].users.length;
		A[A.length] = "</span>)";
    A[A.length] = "</nobr></div>";
    var rosterGroupClass = (
			    (this.usersHidden && this.groups[i].onlUserCount == 0 && 
			     this.groups[i].messagesPending == 0 && 
			     this.groups[i].name != loc("Gateways")) 
			    || this.hiddenGroups[this.groups[i].name])
      ? 'hidden':'rosterGroup';

    A[A.length] =  "<div id='";
		A[A.length] = this.groups[i].name;
		A[A.length] = "' class='";
		A[A.length] = rosterGroupClass;
		A[A.length] = "'>";
    
    this.groups[i].users = this.groups[i].users.sort(rosterSort);

		/* ***
		 * loop users in rostergroup 
		 */
    for (var j=0; j<this.groups[i].users.length; j++) {
      var user = this.groups[i].users[j];

      var rosterUserClass = (this.usersHidden && 
			     (user.status == 'unavailable' || 
			      user.status == 'stalker') && 
			     !user.lastsrc && 
			     this.groups[i].name != loc("Gateways")) 
	? "hidden":"rosterUser";

      A[A.length] = "<div id=\"";
			A[A.length] = htmlEnc(user.jid);
			A[A.length] = "/";
			A[A.length] = this.groups[i].name;
			A[A.length] = "Entry\" class=\"";
			A[A.length] = rosterUserClass;
			A[A.length] = "\" onClick=\"return userClicked(this,'";
			A[A.length] = htmlEnc(user.jid);
			A[A.length] = "');\" title=\"";
			A[A.length] = user.name;
			if (user.realjid) {
				A[A.length] = "&#10;JID: ";
				A[A.length] = htmlEnc(user.realjid);
			} else {
				A[A.length] = "&#10;JID: ";
				A[A.length] = htmlEnc(user.jid);
			}
			A[A.length] = "&#10;";
			A[A.length] = loc("Status");
			A[A.length] = ": ";
			A[A.length] = user.status;
      if (user.statusMsg) {
        A[A.length] = "&#10;";
				A[A.length] = loc("Message");
				A[A.length] = ": ";
				A[A.length] = htmlEnc(user.statusMsg);
			}
			if ((user.messages.length + user.chatmsgs.length) > 0) {
				A[A.length] = "&#10;";
				A[A.length] = loc("[_1] message(s) pending",(user.messages.length + user.chatmsgs.length));
			}
      A[A.length] = "\">";
      var userImg = (user.lastsrc) ? messageImg : eval(user.status + "Led");
      A[A.length] = "<nobr><img src=\"";
			A[A.length] = userImg.src;
			A[A.length] = "\" name=\"";
			A[A.length] = htmlEnc(user.jid);
			A[A.length] = "/";
			A[A.length] = this.groups[i].name;
			A[A.length] = "\" width='16' height='16' border='0' align='left'>";
      A[A.length] = "<div><span class=\"nickName\">";
			A[A.length] = user.name;
			A[A.length] = "</span>";

      if (user.statusMsg) {
        A[A.length] = "<br clear=all><nobr><span class=\"statusMsg\">";
				A[A.length] = htmlEnc(user.statusMsg);
				A[A.length] = "</span></nobr>";
			}
      A[A.length] =  "</div></nobr></div>";
    } /* END inner loop */
    A[A.length] =  "</div>";
  }

  this.rosterW.getElementById("roster").innerHTML = A.join('');
  this.updateStyleIE();
}

function getUserElementId(user, group) {
  var groupName = group.name || 'Unfiled';
  return htmlEnc(user.jid)+"/"+groupName+"Entry";
}

function getRosterUserClass(usersHidden, user, group) {
  return (usersHidden && (user.status == 'unavailable' || user.status == 'stalker') && !user.lastsrc && group.name != loc("Gateways") ?
          "hidden" : "rosterUser");
}

function getRosterGroupHeaderClass(usersHidden, group) {
  return (usersHidden && group.onlUserCount == 0 && group.messagesPending == 0 && group.name != loc("Gateways") ?
         'rosterGroupHeaderHidden':'rosterGroupHeader');
}

function getUserElementTitle(user) {
  var elTitle = user.name
  if (user.realjid) {
    elTitle += "&#10;JID: ";
    elTitle += htmlEnc(user.realjid);
  } else {
    elTitle += "&#10;JID: ";
    elTitle += htmlEnc(user.jid);
  }
  elTitle += "&#10;";
  elTitle += loc("Status");
  elTitle += ": ";
  elTitle += user.status;
  if (user.statusMsg) {
    elTitle += "&#10;";
    elTitle += loc("Message");
    elTitle += ": ";
    elTitle += htmlEnc(user.statusMsg);
  }
  if ((user.messages.length + user.chatmsgs.length) > 0) {
    elTitle += "&#10;";
    elTitle += loc("[_1] message(s) pending",(user.messages.length + user.chatmsgs.length));
  }
  return elTitle;
}

function getUserInnerHTML(user, group) {
  var userImg = (user.lastsrc) ? messageImg : eval(user.status + "Led");
  var A = new Array();
  A[A.length] = "<nobr><img src=\"";
  A[A.length] = userImg.src;
  A[A.length] = "\" name=\"";
  A[A.length] = htmlEnc(user.jid);
  A[A.length] = "/";
  A[A.length] = group.name;
  A[A.length] = "\" width='16' height='16' border='0' align='left'>";
  A[A.length] = "<div><span class=\"nickName\">";
  A[A.length] = user.name;
  A[A.length] = "</span>";

  if (user.statusMsg) {
    A[A.length] = "<br clear=all><nobr><span class=\"statusMsg\">";
    A[A.length] = htmlEnc(user.statusMsg);
    A[A.length] = "</span></nobr>";
  }
  A[A.length] =  "</div></nobr>";
  return A.join('');
}

function updateRoster() {
  for (var i=0; i<this.groups.length; i++) {
    var group = this.groups[i];
    group.users = group.users.sort(rosterSort);
    var groupEl = this.rosterW.getElementById(group.name);
    if (groupEl) { // update group
      for (var j=0; j<group.users.length; j++) {
        var user = group.users[j];
        var userElId = getUserElementId(user,group);
        var userEl = this.rosterW.getElementById(userElId);
        if (!userEl) { // add user
          userEl = this.rosterW.createElement('div');
          userEl.id = userElId;
          userEl.className = getRosterUserClass(this.usersHidden, user, group);
          var onclickHandler = function(el, user, roster) {
            var e = el;
            var jid = htmlEnc(user.jid);
            var r = roster;
            var toggler = function() { r.userClicked(e, jid); };
            return toggler;
          }
          userEl.onclick = onclickHandler(userEl, user, this);//"return userClicked(this,'"+htmlEnc(user.jid)+"');";
          userEl.title = getUserElementTitle(user);
          userEl.innerHTML = getUserInnerHTML(user, group);
          var siblingEl;
          var k = j+1;
          while (!siblingEl && k<group.users.length) {
            siblingEl = this.rosterW.getElementById(getUserElementId(group.users[k],group));
            k++;
          }
          if (!siblingEl) {
            groupEl.appendChild(userEl)
          } else {
            groupEl.insertBefore(userEl,siblingEl);
          }
        } else { // update user
          userEl.className = getRosterUserClass(this.usersHidden, user, group);
          userEl.title = getUserElementTitle(user);
          var userImg = (user.lastsrc) ? messageImg : eval(user.status + "Led");
          userEl.getElementsByTagName('img')[0].src = userImg.src;
          var spanEls = userEl.getElementsByTagName('span');
          spanEls[0].innerHTML = user.name
          if (user.statusMsg) {
            if (spanEls[1]) {
              spanEls[1].innerHTML = htmlEnc(user.statusMsg);
            } else {
              try {
                var A = new Array();
                A[A.length] = "<br clear='all'><nobr><span class='statusMsg'>";
                A[A.length] = htmlEnc(user.statusMsg);
                A[A.length] = "</span></nobr>";
                var html = A.join('');
                var divEls = userEl.getElementsByTagName('div');
                if (divEls && divEls[0])
                  divEls[0].innerHTML += html;
              } catch(e) {
                // qnd: somehow IE7 doesn't like the 'nobr' here - why?
              }                
            }
          }
        }
      } // done with users
      var groupHeaderEl = this.rosterW.getElementById(group.name+'Head');
      if (groupHeaderEl) {
        groupHeaderEl.className = getRosterGroupHeaderClass(this.usersHidden, group);

        if (this.rosterW.getElementById(group.name+'On')) 
          this.rosterW.getElementById(group.name+'On').innerHTML = group.onlUserCount;
        if (this.rosterW.getElementById(group.name+'All'))
          this.rosterW.getElementById(group.name+'All').innerHTML = group.users.length;
      }
    } else { // add group
      var groupHeaderEl = this.rosterW.createElement('div');
      groupHeaderEl.id = group.name+"Head";
      groupHeaderEl.className = getRosterGroupHeaderClass(this.usersHidden, group);
      var onclickHandler = function(group) {
        var groupName = group.name;
        var toggler = function() { parent.top.roster.toggleGrp(groupName); };
        return toggler;
      }
      groupHeaderEl.onclick = onclickHandler(group);

      groupEl = this.rosterW.createElement('div');
      groupEl.id = group.name;
      var rosterGroupClass = (
            (this.usersHidden && group.onlUserCount == 0 &&
             group.messagesPending == 0 &&
             group.name != loc("Gateways"))
            || this.hiddenGroups[group.name])
        ? 'hidden':'rosterGroup';
      groupEl.className = rosterGroupClass;

      var rosterEl = this.rosterW.getElementById("roster");  

      var siblingEl;
      var j = i + 1;
      while (!siblingEl && j < this.groups.length) {
        siblingEl = this.rosterW.getElementById(this.groups[j].name+'Head');
        j++;
      }
      if (!siblingEl) {
        rosterEl.appendChild(groupHeaderEl)
        rosterEl.appendChild(groupEl);
      } else {
        rosterEl.insertBefore(groupHeaderEl, siblingEl);
        rosterEl.insertBefore(groupEl, siblingEl);
      }

      var A = new Array();
      A[A.length] = "<nobr>";
      var toggleImg = (this.hiddenGroups[group.name])?'images/group_close.gif':'images/group_open.gif';
      A[A.length] = "<img src='";
      A[A.length] = toggleImg;
      A[A.length] ="' name='";
      A[A.length] = group.name;
      A[A.length] = "Img'> ";
      A[A.length] = group.name;
      A[A.length] = " (<span id='";
      A[A.length] = group.name;
      A[A.length] = "On'>";
      A[A.length] = group.onlUserCount;
      A[A.length] = "</span>/<span id='";// put total number in span, also - sam
      A[A.length] = group.name;
      A[A.length] = "All'>";
      A[A.length] = group.users.length;
      A[A.length] = "</span>)";
      A[A.length] = "</nobr>";
      groupHeaderEl.innerHTML = A.join('');


      A = new Array();
      for (var j=0; j<group.users.length; j++) {
        var user = group.users[j];

        A[A.length] = "<div id=\"";
        A[A.length] = htmlEnc(user.jid);
        A[A.length] = "/";
        A[A.length] = group.name;
        A[A.length] = "Entry\" class=\"";
        A[A.length] = getRosterUserClass(this.usersHidden, user, group);
        A[A.length] = "\" onClick=\"return userClicked(this,'";
        A[A.length] = htmlEnc(user.jid);
        A[A.length] = "');\" title=\"";
        A[A.length] = getUserElementTitle(user);
        A[A.length] = "\">";
        A[A.length] = getUserInnerHTML(user, group);
        A[A.length] =  "</div>";
      } /* END inner loop */
      groupEl.innerHTML = A.join('');

    }
  }
}

// moved into Roster from iRoster.html and groupchat_iroster.html - sam
function RosterSelectUser(el) {
  if(this.lastUserSelected)
    this.lastUserSelected.className = "rosterUser";
  el.className = "rosterUserSelected";
  this.lastUserSelected = el;
}

function RosterUserClicked(el,jid) {
  this.selectUser(el);

  if (this.name == 'GroupchatRoster') {
    return parent.top.user.roster.openChat(jid);
  }

  var user = parent.top.roster.getUserByJID(parent.top.cutResource(jid));

	if(user && typeof(user.type) != 'undefined' && user.type == 'groupchat')
		return parent.top.openGroupchat(jid);

	if (!parent.top.isGateway(jid))
		return parent.top.roster.openChat(jid);
}

/***********************************************************************
 * GROUPCHAT ROSTER
 *+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 */
function GCRosterSort(a,b) {
  return (a.name.toLowerCase()<b.name.toLowerCase())?-1:1;
}

function GroupchatRosterPrint() {
  var A = new Array();
  
  this.groups = this.groups.sort(GCRosterSort);

  /* ***
   * loop rostergroups 
   */
  for (var i=0; i<this.groups.length; i++) {
    var rosterGroupHeadClass = (this.groups[i].users.length == 0) ? 'rosterGroupHeaderHidden':'rosterGroupHeader';

    A[A.length] = "<div id='";
		A[A.length] = this.groups[i].name;
		A[A.length] = "Head' class='";
		A[A.length] = rosterGroupHeadClass;
		A[A.length] = "'><nobr>&nbsp;";
    A[A.length] = this.groups[i].users.length;
		A[A.length] = " ";
		A[A.length] = this.groups[i].name;
    A[A.length] = "</nobr></div>";
    A[A.length] = "<div id='";
		A[A.length] = this.groups[i].name;
		A[A.length] = "' class='rosterGroup'>";
    
    this.groups[i].users = this.groups[i].users.sort(rosterSort);

		/* ***
		 * loop users in rostergroup 
		 */
    for (var j=0; j<this.groups[i].users.length; j++) {
      var user = this.groups[i].users[j];
      var rosterUserClass = (this.usersHidden && 
			     (user.status == 'unavailable' || 
			      user.status == 'stalker') && 
			     !user.lastsrc) 
	? "hidden":"rosterUser";
      
      A[A.length] = "<div id=\"";
			A[A.length] = htmlEnc(user.jid);
			A[A.length] = "/";
			A[A.length] = this.groups[i].name;
			A[A.length] = "Entry\" class=\"";
			A[A.length] = rosterUserClass;
			A[A.length] = "\" onClick=\"return userClicked(this,'";
			A[A.length] = htmlEnc(user.jid).replace(/\'/g,"\\\'")+"');\" title=\"";
			A[A.length] = user.name;
			if (user.realjid) {
				A[A.length] = "&#10;JID: ";
				A[A.length] = htmlEnc(user.realjid);
			} else {
				A[A.length] = "&#10;JID: ";
				A[A.length] = htmlEnc(user.jid);
			}
			A[A.length] = "&#10;";
			A[A.length] = loc("Status");
			A[A.length] = ": ";
			A[A.length] = user.status;
      if (user.statusMsg) {
        A[A.length] = "&#10;";
				A[A.length] = loc("Message");
				A[A.length] = ": ";
				A[A.length] = htmlEnc(user.statusMsg);
			}
			if ((user.messages.length + user.chatmsgs.length) > 0) {
				A[A.length] = "&#10;";
				A[A.length] = loc("[_1] message(s) pending",(user.messages.length + user.chatmsgs.length));
			}
      A[A.length] = "\"><nobr>";
      var userImg = (user.lastsrc) ? messageImg : eval(user.status + "Led");
      A[A.length] = "<img src=\"";
			A[A.length] = userImg.src;
			A[A.length] = "\" name=\"";
			A[A.length] = htmlEnc(user.jid);
			A[A.length] = "/";
			A[A.length] = this.groups[i].name;
			A[A.length] = "\" width=16 height=16 border=0 align=\"left\">";
      A[A.length] = "<div><span class=\"nickName\">";
			A[A.length] = user.name;
			A[A.length] = "</span>";
      if (user.statusMsg) {
        A[A.length] = "<br clear=all><nobr><span class=\"statusMsg\">";
				A[A.length] = htmlEnc(user.statusMsg);
				A[A.length] = "</span></nobr>";
			}
      A[A.length] = "</div></nobr></div>";
    } /* END inner loop */
    A[A.length] = "</div>";
  }

  this.rosterW.getElementById("roster").innerHTML = A.join('');
  this.updateStyleIE();
}

function GroupchatRosterUserAdd2Group(group) {
  this.groups = [group];
}

function GroupchatRosterUser(jid,name) {
  this.base = RosterUser;
  this.base(jid,'',[''],name);
	this.jid = this.fulljid; // always use fulljid
  this.affiliation = 'none';
  this.role = 'none';

  this.add2Group = GroupchatRosterUserAdd2Group;
}

GroupchatRosterUser.prototype = new RosterUser;

function getRosterGetRealJIDByNick(nick) {
  for (var i=0; i<this.users.length; i++)
    if (this.users[i].name == nick)
      return this.users[i].realjid;
  return null;
}

function getRosterGetFullJIDByNick(nick) {
  for (var i=0; i<this.users.length; i++)
    if (this.users[i].name == nick)
      return this.users[i].fulljid;
  return null;
}
			
function getGroupchatRosterUserByJID(jid) {
  // need to search fulljid here
  return getElFromArrByProp(this.users,"fulljid",jid);
}

function GroupchatRoster(targetW) {

  this.base = Roster;
  this.base(null);
  this.usersHidden = true;

  this.targetW = targetW.frames.groupchatRoster;

  this.rosterW = this.targetW.groupchatIRoster.document;

  this.name = 'GroupchatRoster';

  this.print = GroupchatRosterPrint;
  this.getUserByJID = getGroupchatRosterUserByJID;
  this.getRealJIDByNick = getRosterGetRealJIDByNick;
  this.getFullJIDByNick = getRosterGetFullJIDByNick;
}

GroupchatRoster.prototype = new Roster();

// some images - no idea why they are defined here

var messageImg = new Image();
messageImg.src = "images/message.gif";
var grp_open_img = new Image();
grp_open_img.src = 'images/group_open.gif';
var grp_close_img = new Image();
grp_close_img.src = 'images/group_close.gif';
var arrow_right_blinking = new Image();
arrow_right_blinking.src = 'images/arrow_right_blinking.gif';

