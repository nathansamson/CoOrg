{block name='title'}{'Edit %m'|_:$menu->name}{/block}

{block name='head' append}
	<link rel="stylesheet" href="{'styles/menu.admin.css'|static:menu}" />
{/block}

{block name='content'}
	<h1>{'Edit %m'|_:$menu->name}</h1>
	
	<a href="{url request="admin/menu"}">{'Go back'|_}</a>
	
	<h2>{'Entries'|_}</h2>
	<ol class="adminmenu">
		{foreach $menu->entries({$adminlanguage}) as $entry}
			<li>
				{$entry->title}
				<span class="actions">
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
					        param_entry=$entry->ID}
						<img src="{'images/icons/list-remove.png'|static}" />
					{/button}
					
				</span>
			</li>
		{/foreach}
	</ol>
	
	<div class="leftc">
		<h2>{'Add entry'|_}</h2>
	
		{form request='admin/menu/entry/save' instance=$newEntry}
			<input type="hidden" name="menu" value="{$menu->name}" />
			<input type="hidden" name="language" value="{$adminlanguage}" />
		
			{input for=title label="Title" required}
		
			{input type=select for=entryID label="To" options=$providerActionCombos}
		
			<span id="url-data">
				{input for=data label="Data"}
			</span>
		
		
			{input type="submit" label="Save menu"}
		{/form}
	</div>
	<div class="rightc">
		<h2>{'Edit menu properties'|_}</h2>
		
		{form request='admin/menu/update' instance=$menu}

			<input type="hidden" name="name" value="{$menu->name}" />
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
