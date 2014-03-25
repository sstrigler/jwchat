function correctPNG() // correctly handle PNG transparency in Win IE 5.5 or higher.
   {
   for(var i=0; i<document.images.length; i++)
      {
        if (navigator.userAgent.indexOf('MSIE') == -1)
          return;

        if (navigator.userAgent.indexOf('MSIE 5')>0 && navigator.userAgent.indexOf('MSIE 5.5')==-1) 
          return; // don't break IE 5.0

	  var img = document.images[i]
	  var imgName = img.src.toUpperCase()
	  if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
	     {
		 var imgID = (img.id) ? "id='" + img.id + "' " : ""
		 var imgClass = (img.className) ? "class='" + img.className + "' " : ""
		 var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
		 var imgStyle = "display:inline-block;" + img.style.cssText 
		 var imgAttribs = img.attributes;
     var imgEvents = '';
		 for (var j=0; j<imgAttribs.length; j++)
			{
        var imgAttrib = imgAttribs[j];
			if (imgAttrib.nodeName == "align")
			   {		  
			   if (imgAttrib.nodeValue == "left") imgStyle = "float:left;" + imgStyle
			   if (imgAttrib.nodeValue == "right") imgStyle = "float:right;" + imgStyle
			   }
      else if (imgAttrib.nodeName == "onclick")
        imgEvents += " onClick='" + imgAttrib.nodeValue + "'";
      else if (imgAttrib.nodeName == "onmouseover")
        imgEvents += " onMouseOver='" + imgAttrib.nodeValue+ "'";
      else if (imgAttrib.nodeName == "onmouseout")
        imgEvents += " onMouseOut='" + imgAttrib.nodeValue+ "'";
      }
		 var strNewHTML = "<span " + imgID + imgClass + imgTitle + imgEvents
		 strNewHTML += " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
	     strNewHTML += "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
		 strNewHTML += "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>" 
		 img.outerHTML = strNewHTML
		 i = i-1
	     }
      }
   }

onload = correctPNG;
