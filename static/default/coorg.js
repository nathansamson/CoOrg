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
