function cWYSIWYG(textarea)
{
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
	
	this.insertLink = function() {
	}
	
	this.insertOrderedList = function() {
		document.execCommand('insertOrderedList', false, null);
	}
	
	this.insertUnorderedList = function() {
		document.execCommand('insertUnorderedList', false, null);
	}
	
	this.removeMarkup = function()
	{
		var selection = window.getSelection().getRangeAt(0);
		document.execCommand('insertHTML', false, selection.toString().replace('\n', '<br />'));
	}

	var container = document.createElement('div');
	var toolbar = document.createElement('div');
	toolbar.className = 'toolbar';
	var edit = document.createElement('div');
	edit.contentEditable = true;
	
	container.appendChild(toolbar);
	container.appendChild(edit);
	
	var buttons = {
		'bold': this.onBold,
		'italic': this.onItalic,
		'underline': this.onUnderline,
		'link': this.insertLink,
		'ol': this.insertOrderedList,
		'ul': this.insertUnorderedList,
	};
	
	for (var i in buttons)
	{
		var button = document.createElement('button');
		button.innerHTML = i;
		$(button).bind('click', buttons[i]);
		button.type = 'button';
		toolbar.appendChild(button);
	}
	
	textarea.parentNode.insertBefore(container, textarea);
	textarea.style.display = 'none';
	textarea.form.onsubmit = function() {
		textarea.innerHTML = edit.innerHTML;
	}
	edit.focus();
	document.execCommand('styleWithCSS', null, false);
	document.execCommand('insertHTML', false, textarea.firstChild.data);
	edit.blur();
}
