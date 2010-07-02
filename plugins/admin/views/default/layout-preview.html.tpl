<section class="widget-preview">
	<header>
		<div class="section-actions">
			{if isset($widgetUp)}
				{button request="admin/layout/move"
			        param_panelID=$panelID
			        param_widgetID=$widgetID
			        param_to=$widgetUp}<img src="{'images/icons/go-up.png'|static}" />{/button}
			{/if}
			
			{if $widgetDown}
				{button request="admin/layout/move"
			        param_panelID=$panelID
			        param_widgetID=$widgetID
			        param_to=$widgetDown}<img src="{'images/icons/go-down.png'|static}" />{/button}
			{/if}
		
			{if $widgetConfigure}
				{a request="admin/layout/edit"
			        panelID=$panelID
			        widgetID=$widgetID
			        coorgStock="edit"}{/a}
			{/if}
		
			{if !$panels}
			{button request="admin/layout/delete"
			        param_panelID=$panelID
			        param_widgetID=$widgetID
			        coorgStock="list-remove"}{/button}
			{/if}
		</div>
		<h1>{block name="widget-title"}Menu{/block}</h1>
		{if $panels}
			<div class="add">
			{form request="admin/layout/save" nobreaks id=$widgetName}
				{input value=$widgetName name="widgetName"}
				
				{input type=select type=select options=$panels name="panelID" nolabel}
				
				{input type="submit" stock="list-add" nolabel}
			{/form}
			</div>
		{/if}
	</header>
	<div>
	{block name="widget-preview"}
		ME SHOULD NOT BE EMPTY
	{/block}
	</div>
</section>
