{block name='title'}{'Edit %m'|_:$menu->name|escape}{/block}

{block name='head' append}
	<link rel="stylesheet" href="{'styles/menu.admin.css'|static:menu}" />
{/block}

{block name='content'}
	<h1>{'Edit %m'|_:$menu->name|escape}</h1>
	
	{a request="admin/menu"}{'Go back'|_}{/a}
	
	<h2>{'Entries'|_}</h2>
	<ol class="adminmenu">
		{foreach $menu->entries->filter({$adminlanguage}) as $entry}
			<li>
				{$entry->title|escape}
				<div class="actions">
					{if !$entry@first}
						{button request="admin/menu/entry/move"
							    param_entry=$entry->ID
							    param_newsequence=$entry->sequence-1}
							<img src="{'images/icons/go-up.png'|static}" />
						{/button}
					{/if}
					{if !$entry@last}
						{button request="admin/menu/entry/move"
							    param_entry=$entry->ID
							    param_newsequence=$entry->sequence+1}
							<img src="{'images/icons/go-down.png'|static}" />
						{/button}
					{/if}
					
					{button request="admin/menu/entry/delete"
					        param_entry=$entry->ID
					        coorgStock="list-remove"
					        coorgConfirm="Are you sure you want to remove '%n' from the menu?"|_:$entry->title}
					{/button}
				</div>
			</li>
		{/foreach}
	</ol>
	
	<div class="leftc">
		<h2>{'Add entry'|_}</h2>
	
		{form request='admin/menu/entry/save' instance=$newEntry}
			{input for=menuID}
			{input for=language}
		
			{input for=title label="menu|Title" required}
		
			{input type=select for=entryID label="To" options=$providerActionCombos}
		
			<div id="url-data">
				{input for=data label="Data"}
			</div>
		
		
			{input type="submit" label="Save menu entry"}
		{/form}
	</div>
	<div class="rightc">
		<h2>{'Edit menu properties'|_}</h2>
		
		{form request='admin/menu/update' instance=$menu}

			{input for=name}
			{input for=description type=textarea label="Small description" required size=small}
		
			{input type="submit" label="Edit menu"}
		{/form}
	</div>
	
	<script>
		function determineDataVisibility(selector)
		{
			var value = selector.get()[0].value;
			if (!value)
			{
				value = selector.find('option').get()[0].value;
			}
			var p = selector.find('optgroup option[value="'+value+'"]');
			if (p.length)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	
		$(document).ready()
		{
			$('#url-data').toggleClass('unneeded',
			                        determineDataVisibility($('[name="entryID"]')));
			
			$('[name="entryID"]').change( function(el) {
				$('#url-data').toggleClass('unneeded', determineDataVisibility($(el.target)));
			});
		}
	</script>
{/block}
