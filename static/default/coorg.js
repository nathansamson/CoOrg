function PlaceholderFake(input)
{
	var usePlaceholder = false;

	function onFocus() {
		if (usePlaceholder)
		{
			removePlaceholder();
		}
	}
	
	function onLooseFocus() {
		if (input.val().trim() == '')
		{
			setPlaceholder();
		}
	}
	
	function removePlaceholder() {
		usePlaceholder = false;
		input.val('');
		input.toggleClass('placeholder', false);
	}

	function setPlaceholder() {
		usePlaceholder = true;
		input.val(input.attr('placeholder'));
		input.toggleClass('placeholder', true);
	}

	input.focus(onFocus);
	input.blur(onLooseFocus);
	
	if (input.val() == '')
	{
		setPlaceholder();
	}
}

function CoOrgList(baseID, theName, options, respondOnEnter, listClass)
{
	function add(superContainer, theName, value)
	{
		var listItem = document.createElement('li');
		var label = document.createElement('label');
		label.innerHTML = value;
	
		removeImg = document.createElement('a');
		removeImg.innerHTML = '<sup>X</sup>';
		removeImg.onclick = function (event) {
			superContainer.removeChild(event.target.parentNode.parentNode.parentNode);
		};
		label.appendChild(removeImg);
	
		var input = document.createElement('input');
		input.value = value;
		input.setAttribute('type', 'hidden');
		input.setAttribute('name', theName);

		listItem.appendChild(label);
		listItem.appendChild(input);
		superContainer.insertBefore(listItem, superContainer.firstChild);
	}

	function keyPress(event)
	{
		if (event.keyCode == 13)
		{
			if (this.value != '')
			{
				event.preventDefault();
				event.stopPropagation();
				$(this).trigger('list-add');
			}
		}
	}

	function onAdd()
	{
		var superContainer = document.getElementById(this.id+'__container');
		add(superContainer, this.name, this.value);
		this.value = '';
	}
	
	var newInputElement = $('#'+baseID).get(0);
	if (respondOnEnter)
	{
		$(newInputElement).keypress(keyPress);
	}
	$(newInputElement).bind('list-add', onAdd);
	var superContainer = document.createElement('ul');
	superContainer.className = listClass;
	superContainer.setAttribute('id', baseID+'__container');

	for(var i = options.length - 1; i >= 0; i--)
	{
		if (options[i] != '')
		{
			add(superContainer, theName+"[]", options[i]);
		}
	}
	newInputElement.parentNode.insertBefore(superContainer, newInputElement.nextSibling);
}

function CoOrgAutoSuggest(inputNode, request, isList)
{
	const KEY_UP = 38;
	const KEY_DOWN = 40;
	const KEY_ENTER = 13;
	const KEY_ESCAPE = 27;
	const KEY_TAB = 9;

	inputNode.attr('autocomplete', 'off');
	var ajax = null;
	var currentTimeout = null;
	var resultScreen = document.createElement('div');
	var ol = $('<ol class="autocomplete" />');
	var currentSelected = null;
	var currentMouseSelected = null;
	if (isList == null) isList = false;
	var removeOlTimeout = null;
	
	function beginRequest(a)
	{
		if (!inputNode.val())
		{
			return;
		}
		ajax = $.ajax({
			url: request,
			data: {search: inputNode.val()},
			success: endRequest,
			error: failureRequest
		});
	}
	
	function failureRequest(request, status, error)
	{
		$(ol).detach();
	}
	
	function endRequest(data, textStatus, request)
	{
		currentSelected = null;
		$(ol).empty();
		$(data).find('suggestions suggestion').each(function(index, element) {
			$('<li>'+$(element).text()+'</li>').
			       mouseenter(onMouseEnterLi).
			       click(onMouseClickLi).
			       appendTo(ol);
		});
		

		if ($(ol).children().length)
			$(ol).insertAfter(inputNode);
		else
			$(ol).detach();
	}
	
	function onMouseEnterLi(event)
	{
		if (currentSelected) currentSelected.toggleClass('selected', false);
		if (currentMouseSelected) currentMouseSelected.toggleClass('selected', false);
		currentSelected = null; // Current selection happens with mouse
		currentMouseSelected = $(this);
		currentMouseSelected.toggleClass('selected', true);
	}
	
	function onMouseClickLi(event)
	{
		if (removeOlTimeout)
		{
			clearTimeout(removeOlTimeout);
			removeOlTimeout = null;
		}
		choose($(this));
	}
	
	function findPrev()
	{
		if (currentSelected && currentSelected.prev().length)
		{
			return currentSelected.prev();
		}
		else if (currentMouseSelected && currentMouseSelected.prev().length)
		{
			return currentMouseSelected.prev();
		}
		else
		{
			return $(ol.get(0).lastChild);
		}
	}
	
	function findNext()
	{
		if (currentSelected && currentSelected.next().length)
		{
			return currentSelected.next();
		}
		else if (currentMouseSelected && currentMouseSelected.next().length)
		{
			return currentMouseSelected.next();
		}
		else
		{
			return $(ol.get(0).firstChild);
		}
	}
	
	function keypress (event)
	{
		switch (event.keyCode)
		{
		case KEY_UP:
			if (currentSelected) currentSelected.toggleClass('selected', false);
			currentSelected = findPrev();
			if (currentMouseSelected) currentMouseSelected.toggleClass('selected', false);
			currentMouseSelected = null;
			currentSelected.toggleClass('selected', true);
			break;
		case KEY_DOWN:
			if (currentSelected) currentSelected.toggleClass('selected', false);
			currentSelected = findNext();
			if (currentMouseSelected) currentMouseSelected.toggleClass('selected', false);
			currentMouseSelected = null;
			currentSelected.toggleClass('selected', true);
			break;
		case KEY_ENTER:
			if (currentSelected)
			{
				choose(currentSelected);
			}
			else if (isList)
			{
				$(inputNode).trigger('list-add');
			}
			else
			{
				break;
			}
			event.preventDefault();
			break;
		case KEY_ESCAPE:
			$(ol).detach();
			break;
		case KEY_TAB:
			break;
		default:
			if (ajax)
			{
				ajax.abort();
				ajax = null;
			}
			if (currentTimeout)
			{
				clearTimeout(currentTimeout);
				currentTimeout = null;
			}
			currentTimeout = setTimeout(beginRequest, 300);
		}
	}
	
	function keyup(event)
	{	
		if (inputNode.val() == '')
		{
			$(ol).detach();
		}
	}
	
	function choose(what)
	{
		inputNode.val(what.text());
		if (!isList)
		{
			inputNode.get(0).form.submit();
		}
		else
		{
			$(inputNode).trigger('list-add');
		}
		$(ol).detach();
	}
	
	function onFocus()
	{
		if ($(ol).children().length)
			$(ol).insertAfter(inputNode);
	}
	
	function onBlur(event)
	{
		// Urgh. I don't like this hack.
		// If we hide the ol immediately, the mouse click does not propagate.
		// We delay detachment of ol, to give the mouseclick event a chance.
		// When the mouseclick happens, it clears this timeout.
		// Did I say already, that I don't like this approach?
		removeOlTimeout = setTimeout(detachOl, 300);
	}
	
	function detachOl()
	{
		removeOlTimeout = null;
		$(ol).detach();
	}
	
	inputNode.keypress(keypress);
	inputNode.keyup(keyup);
	inputNode.focus(onFocus);
	inputNode.blur(onBlur);
}
