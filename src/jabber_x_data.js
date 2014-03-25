function genJabberXDataTable(x) {
	var html = '<input type=hidden name="jwchat_form_type" value="jabber:x:data">';

	if (x.getElementsByTagName('title').item(0))
		html += "<h1>"+x.getElementsByTagName('title').item(0).firstChild.nodeValue.replace(/\n/g,"<br>")+"</h1>";
	if (x.getElementsByTagName('instructions').item(0))
		html += x.getElementsByTagName('instructions').item(0).firstChild.nodeValue.replace(/\n/g,"<br>");

	if (!x.getElementsByTagName('field').length)
		return html;

	html += '<table width="100%">';
	for (var i=0; i<x.getElementsByTagName('field').length; i++) {
		var aField = x.getElementsByTagName('field').item(i);
 		html += "<tr>";
		switch (aField.getAttribute('type')) {
		case 'hidden':
			if (aField.firstChild && aField.firstChild.firstChild)
				html += "<td colspan=2><input type=hidden name='"+aField.getAttribute('var')+"' value='"+aField.firstChild.firstChild.nodeValue+"'></td>";
			break;
		case 'fixed':
			html += "<td colspan=2><b>"+aField.firstChild.firstChild.nodeValue+"</b></td>";
			break;
		case 'jid-single':
		case 'text-single':
			html += "<th>" + aField.getAttribute('label') + "</th>";
			html += "<td>";
			html += "<input type=text size='24' name='" + aField.getAttribute('var') + "'";
			if (aField.firstChild && aField.firstChild.firstChild)
				html += " value='" + aField.firstChild.firstChild.nodeValue + "'";
			html += ">";
			html += "</td>";
			break;
		case 'text-private':
			html += "<th>" + aField.getAttribute('label') + "</th>";
			html += "<td>";
			html += "<input type=password size='24' name='" + aField.getAttribute('var') + "'";
			if (aField.firstChild && aField.firstChild.firstChild)
				html += " value='" + aField.firstChild.firstChild.nodeValue + "'";
			html += ">";
			html += "</td>";
			break;
		case 'jid-multi':
		case 'text-multi':
			html += "<th valign=top>" + aField.getAttribute('label') + "</th>";
			html += "<td>";
			html += "<textarea cols=24 rows=4 name='" + aField.getAttribute('var') + "'>";
			if (aField.firstChild && aField.firstChild.firstChild)
				html += aField.firstChild.firstChild.nodeValue;
			html += "</textarea>";
			html += "</td>";
			break;
		case 'list-single':
			html += "<th>" + aField.getAttribute('label') + "</th>";
			html += "<td>";
			html += "<select name='" + aField.getAttribute('var') + "'>";
			if (aField.childNodes.length) {
				var val;
				for (var j=0; j<aField.childNodes.length; j++) 
					if (aField.childNodes.item(j).nodeName == 'value') 
						val = aField.childNodes.item(j).firstChild.nodeValue;
				for (var j=0; j<aField.childNodes.length; j++) {
					if (aField.childNodes.item(j).nodeName == 'option') {
						html += "<option value='" + aField.childNodes.item(j).firstChild.firstChild.nodeValue + "'";
 						if (val && val == aField.childNodes.item(j).firstChild.firstChild.nodeValue)
 							html += " selected";
						html += ">"+aField.childNodes.item(j).getAttribute('label')+"</option>";
					}
				}
			}
			html += "</select>";
			html += "</td>";
			break;
		case 'list-multi':
			html += "<th>" + aField.getAttribute('label') + "</th>";
			html += "<td>";
			html += "<select name='" + aField.getAttribute('var') + "' "
				+ "multiple='true'>";
			if (aField.childNodes.length) {
				for (var j=0; j<aField.childNodes.length; j++) {
					if (aField.childNodes.item(j).nodeName == 'option') {
						html += "<option value='" + aField.childNodes.item(j).firstChild.firstChild.nodeValue + "'";
// 						if (x.o[i].value == x.o[i].o[j].value)
// 							html += " selected";
						html += ">"+aField.childNodes.item(j).getAttribute('label')+"</option>";
					}
				}
			}
			html += "</select>";
			html += "</td>";
			break;
		case 'boolean':
			html += "<th>" + aField.getAttribute('label') + "</th>";
			html += "<td>";
			html += "<input type=checkbox name='" +aField.getAttribute('var') + "'";
			if (aField.firstChild && aField.firstChild.firstChild && aField.firstChild.firstChild.nodeValue == '1')
				html += " checked";
			html += ">";
			html += "</td>";
			break;
		default:
			srcW.Debug.log("unknown type: " + aField.getAttribute('type'),1);
			break;
		}
 		html += "</tr>";
	}
	html += "</table>";

	return html;
}

function genJabberXDataReply(form) {
	var xml = "<x xmlns='jabber:x:data' type='submit'>";

	var els = form.elements;
	for (var i=0; i<els.length; i++) {
		if (els[i].name == '' || els[i].value == '' || els[i].name == 'jwchat_form_type')
			continue;
		xml += "<field var='" +els[i].name + "'><value>";
		if (els[i].type == 'checkbox')
			xml += (els[i].checked) ? '1':'0';
		else
			xml += els[i].value;
		xml += "</value></field>";
	}

	xml += "</x>";

	return xml;
}
