{block name='title'}Menu{/block}

{block name='content'}
<h1>Menu</h1>

<ul>
{foreach $menus as $menu}
	<li>
		<a href="{url controller="admin" scontroller="menu" action="edit" name=$menu->name}">
			{$menu->name} {$menu->description}</li>
		</a>
{/foreach}
</ul>

<h2>Nieuw menu maken</h2>
{form request='admin/menu/save' instance=$newMenu}
	{input for=name label="Name" required}
	{input for=description type=textarea label="Small description" required size=small}
	
	{input type="submit" label="Create menu"}
	{/form}
{/block}
