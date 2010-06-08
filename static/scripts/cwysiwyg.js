/*
 * Copyright 2010 Nathan Samson <nathansamson at gmail dot com>
 *
 * This file is part of CoOrg.
 *
 * CoOrg is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

  * CoOrg is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU Affero General Public License for more details.

  * You should have received a copy of the GNU Affero General Public License
  * along with CoOrg.  If not, see <http://www.gnu.org/licenses/>.
*/

function cWYSIWYG(textarea)
{
	function popup() {
		var node = document.createElement('div');
		node.id = "bigasspopup";
		
		var h1 = document.createElement('h1');
		h1.innerHTML = 'Replace me';
		node.appendChild(h1);
		
		this.setTitle = function(title)
		{
			h1.innerHTML = title;
		}
		
		this.show = function()
		{
			var body = document.getElementsByTagName('body')[0];
			body.appendChild(node);
			var width = $(node).width();
			var height = $(node).height();
			node.style.marginRight = -width / 2+'px';
			node.style.marginTop = -height / 2+'px';
		}
		
		this.form = function (inputs, func, popup)
		{
			var form = document.createElement('form');
			
			for (var id in inputs)
			{
				var input = inputs[id];
				if (input.type == 'text')
				{
					var label = document.createElement('label');
					label.innerHTML = input.label;
					var field = document.createElement('input');
					field.setAttribute('name', id);
					if (input.value)
					{
						field.value = input.value;
					}
					form.appendChild(label);
					form.appendChild(field);
					var br = document.createElement('br');
					form.appendChild(br);
				}
				else if (input.type == 'submit')
				{
					var button = document.createElement('input');
					button.type = 'submit';
					button.value = input.label;
					form.appendChild(button);
				}
				else if (input.type == 'var')
				{
					form[id] = input.value;
				}
			}
			form.onsubmit = function() {func(this, popup); return false;};
			node.appendChild(form);
		}
		
		this.close = function()
		{
			node.parentNode.removeChild(node);
			return false;
		}
	}

	this.onBold = function() {
		document.execCommand('bold', false, null);
		edit.focus();
	}
	
	this.onItalic = function() {
		document.execCommand('italic', false, null);
		edit.focus();
	}
	
	this.onUnderline = function() {
		document.execCommand('underline', false, null);
		edit.focus();
	}
	
	this.insertLink = function(form, popup) {
		popup.close();
		edit.focus();
		restoreSelection();
		var a = document.createElement('a');
		var url = form.url.value;
		if (url.indexOf('http://') != 0 && url.indexOf('https://') != 0)
		{
			url = 'http://' + url;
		}
		a.setAttribute('href', url);
		a.ondblclick = function(evnt) {this.editLink(evnt.target);}
		
		var curRange = window.getSelection().getRangeAt(0);
		var firstCommonParent = findFirstCommonParentNode(curRange.startContainer, curRange.endContainer);
		if (firstCommonParent == edit)
		{
			console.log('Um, fuck');
			return;
		}
		// Dummy implementation
		firstCommonParent.parentNode.replaceChild(a, firstCommonParent);
		a.appendChild(firstCommonParent);
	}
	
	this.editLink = function(form, popup) {
		popup.close();
		edit.focus();
		restoreSelection();
		form.currentLink.href = form.url.value;
	}
	
	this.popupLink = function() {
		var currentA = findFromAnchor('a');
		saveSelection();
		var p = new popup();
		if (! currentA)
		{
			p.setTitle('Insert link');
			p.form({
				'url': {'label': 'Link', 'type': 'text'},
				'title': {'label': 'Title', 'type': 'text'},
				'add': {'label': 'Add link', 'type': 'submit'}
			}, insertLink, p);
		}
		else
		{
			p.setTitle('Edit link');
			p.form({
				'url': {'label': 'Link', 'type': 'text', 'value': currentA.href},
				'title': {'label': 'Title', 'type': 'text'},
				'add': {'label': 'Save link', 'type': 'submit'},
				'currentLink': {'value': currentA, 'type': 'var'}
			}, editLink, p);
		}
		p.show();
	}
	
	this.insertOrderedList = function() {
		document.execCommand('insertOrderedList', false, null);
	}
	
	this.insertUnorderedList = function() {
		document.execCommand('insertUnorderedList', false, null);
	}
	
	this.onHeader = function(event) {
		var selection = event.data;
		if (selection.value != 'choose')
		{
			var header = document.createElement(selection.value);
			header.innerHTML = 'Ole Ola';
			alert(window.getSelection().focusNode);
			window.getSelection().focusNode.parentNode.insertBefore(header, window.getSelection().focusNode);
		}
	}
	
	this.removeMarkup = function()
	{
		var selection = window.getSelection().getRangeAt(0);
		document.execCommand('insertHTML', false, selection.toString().replace('\n', '<br />'));
	}
	
	this.saveSelection = function()
	{
		var selection = window.getSelection();
		var curRange = selection.getRangeAt(0);
		range = {'startNode': curRange.startContainer, 'startOffset': curRange.startOffset,
		         'endNode': curRange.endContainer, 'endOffset': curRange.endOffset}
	}
	
	this.restoreSelection = function()
	{
		edit.focus();
		var newRange = document.createRange();
		newRange.setStart(range.startNode, range.startOffset);
		newRange.setEnd(range.endNode, range.endOffset);
		window.getSelection().removeAllRanges();
		window.getSelection().addRange(newRange);
	}

	var range = null; /* Save current selection for insertion (of links, images, ...) */
	var container = document.createElement('div');
	container.className = "edit-container";
	var toolbar = document.createElement('div');
	toolbar.className = 'toolbar';
	var edit = document.createElement('div');
	edit.contentEditable = true;
	edit.className = 'editor';
	
	container.appendChild(toolbar);
	container.appendChild(edit);
	
	var toolbarDesc = {
		'header': {'options': {'choose': 'Choose header', 'h1': 'Header 1', 'h2': 'Header 2', 'h3': 'Header 3'},
		           'cb': this.onHeader},
		'bold': {'image': staticFile('images/icons/22/format-text-bold.png'),
		         'title': 'Bold',
		         'cb': this.onBold},
		'italic': {'image': staticFile('images/icons/22/format-text-italic.png'),
		         'title': 'Italic',
		         'cb': this.onItalic},
		'underline': {'image': staticFile('images/icons/22/format-text-underline.png'),
		         'title': 'Underline',
		         'cb': this.onUnderline},
		'link': {'image': staticFile('images/icons/22/edit-link.png'),
		         'title': 'Create link',
		         'cb': this.popupLink},
		'ol': {'image': staticFile('images/icons/22/format-text-list-ordered.png'),
		         'title': 'Ordered list',
		         'cb': this.insertOrderedList},
		'ul': {'image': staticFile('images/icons/22/format-text-list-unordered.png'),
		         'title': 'Unordered list',
		         'cb': this.insertUnorderedList},
	};
	
	var j = 0;
	for (var i in toolbarDesc)
	{
		var tool = toolbarDesc[i];
		var toolNode;
		if (tool.image) {
			toolNode = document.createElement('button');
			var toolImage = document.createElement('img');
			toolImage.src =  tool.image;
			toolImage.setAttribute('title', tool.title);
			toolNode.appendChild(toolImage);
			$(toolNode).bind('click', tool.cb);
			toolNode.type = 'button';
			toolNode.setAttribute('tabindex', 32000+j);
		}
		else if (tool.options)
		{
			toolNode = document.createElement('select');
			for (var opt in tool.options)
			{
				var optNode = document.createElement('option');
				optNode.value = opt;
				optNode.innerHTML = tool.options[opt];
				toolNode.appendChild(optNode);
			}
			$(toolNode).bind('change', toolNode, tool.cb);
			toolNode.setAttribute('tabindex', 32000+j);
		}
		else
		{
			toolNode = document.createTextNode('Unsupported tool');
		}
		toolbar.appendChild(toolNode);
		j++;
	}
	
	textarea.parentNode.insertBefore(container, textarea);
	textarea.className = 'hide';
	textarea.form.onsubmit = function() {
		textarea.innerHTML = edit.innerHTML;
	}
	edit.focus();
	document.execCommand('styleWithCSS', null, false);
	document.execCommand('insertHTML', false, textarea.firstChild.data);
	edit.blur();
	
	function findFirstCommonParentNode (ch1, ch2)
	{
		var candidate = ch1.parentNode;
		while (!(ch2.compareDocumentPosition(candidate) & Node.DOCUMENT_POSITION_CONTAINS))
		{
			candidate = candidate.parentNode;
		}
		return candidate;
	}
	
	function findFromAnchor(nodeName)
	{
		var selection = window.getSelection().getRangeAt(0);
		var base = selection.startContainer;
		while (base != edit)
		{
			if (base.nodeName.toLowerCase() == nodeName)
			{
				return base;
			}
			base = base.parentNode;
		}
		return null;
	}

}
