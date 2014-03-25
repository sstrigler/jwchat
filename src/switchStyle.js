style = "jwchat.css"; // fallback

// look for stylesheet
if (parent.top.stylesheet)
  style = parent.top.stylesheet;
else if (top.opener.top.stylesheet)
  style = top.opener.top.stylesheet;
else if (top.opener.opener.top.stylesheet)
	style = top.opener.opener.top.stylesheet;

document.write('<link rel="styleSheet" type="text/css" href="'+style+'">');
