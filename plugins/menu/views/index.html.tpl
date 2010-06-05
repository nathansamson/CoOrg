{block name='title'}{'Menu'|_}{/block}

{block name='head' append}
	<link rel="stylesheet" href="{'styles/menu.admin.css'|static:menu}" />
{/block}

{block name='content'}
<h1>{'Menu'|_}</h1>

<ul class="menulist">
{foreach $menus as $menu}
	<li>
		<a href="{url controller="admin/menu/edit" name=$menu->name}">{$menu->name|escape}</a>
		<span class="description">{$menu->description|format:none}</span>
		<span class="actions">
			<a href="{url request="admin/menu/edit" name=$menu->name}">
				<img src="{'images/icons/edit.png'|static}" />
			</a>
			{button request="admin/menu/delete"
			        param_name=$menu->name}
				<img src="{'images/icons/edit-delete.png'|static}" />
			{/button}
		</span>
	</li>
{/foreach}
</ul>

<h2>{'Create new menu'|_}</h2>
{form request='admin/menu/save' instance=$newMenu}
	{input for=name label="Name" required}
	{input for=description type=textarea label="Small description" required size=small}
	
	{input type="submit" label="Create menu"}
	{/form}
{/block}
