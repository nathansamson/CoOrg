{block name=title}{'Layout'|_}{/block}

{block name=head append}
	<link rel="stylesheet" href="{'styles/layout.css'|static:admin}" />
{/block}

{block name=content}
	<div class="page-actions">
		{a request="#widget-list" coorgStock="list-add"}{'Add a widget'}{/a}
	</div>
	<h1>{'Layout'|_}</h1>
	<div id="layout-preview" class="preview">
		<div class="header block">
			<h1>{'Site header'|_}</h1>
		</div>
		<div class="navigation">
			<div class="navigation-left block">
				{aside name="navigation-left" preview edit=($editPanelID=="navigation-left") editWidgetID=$editWidgetID}
			</div><div class="navigation-right block">
				{aside name="navigation-right" preview edit=($editPanelID=="navigation-right") editWidgetID=$editWidgetID}
			</div>
			<br />
		</div>
		<div class="content">
			<div class="main-left block">
				{aside name="main" preview edit=($editPanelID=="main") editWidgetID=$editWidgetID}
			</div><div class="main block">
				<h1>{'Site Page'|_}</h1>
				<p>
				Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam malesuada luctus odio, eu semper enim interdum et. Aenean consequat, dui ut fermentum fermentum, ligula velit mollis lorem, et dignissim mi lacus mattis lectus. Aenean scelerisque pharetra quam, id laoreet ligula porta a. Curabitur vitae massa vitae felis sollicitudin mollis sit amet non orci. Aliquam id quam ante, vel viverra neque. Phasellus nec augue sit amet tortor pharetra dictum. Aliquam erat volutpat. Curabitur mi ante, gravida nec auctor vitae, bibendum pretium erat. Suspendisse vel quam eu mauris tempus fermentum quis in velit. Proin malesuada pharetra tortor, et auctor tellus porta a.</p>
			</div>
			<br />
		</div>
		<br />
	</div>
	
	<h2>{'Available widgets'}</h2>
	<div class="preview" id="widget-list">
		{aside preview}
	</div>
{/block}
