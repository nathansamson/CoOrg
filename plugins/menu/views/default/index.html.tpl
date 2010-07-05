{block name='title'}{'Menu'|_}{/block}

{block name='admin-content'}
{stylesheet file={'styles/menu.admin.css'|static:'menu'}}
<h1>{'Menu'|_}</h1>

<ul class="menulist">
{foreach $menus as $menu}
	<li>
		{a request="admin/menu/edit" name=$menu->name}{$menu->name|escape}{/a}
		<span class="description">{$menu->description|format:none}</span>
		<div class="actions">
			{a request="admin/menu/edit" name=$menu->name coorgStock=edit}{/a}
			{button request="admin/menu/delete"
			        param_name=$menu->name
			        coorgStock="delete"
			        coorgConfirm="Are you sure you want to remove '%m'?"|_:$menu->name}{/button}
		</div>
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
