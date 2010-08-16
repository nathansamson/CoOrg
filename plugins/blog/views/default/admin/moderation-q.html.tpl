{block name="title"}{'Moderation queue'|_}{/block}

{block name="admin-content"}
	<table>
		<tr>	
			<th>{'Title'|_}</th>
			<th>{'Message'|_}</th>
			<th>&nbsp;</th>
		</tr>
	{foreach $queue as $c}
		<tr>
			<td>{$c->title}</td>
			<td>{$c->comment|format:'none'|truncate:100}</td>
			<td>
				{form request="admin/blog/comment/spam" nobreaks}
					{input name=commentID value=$c->ID}
					{input name=from value=$coorgRequest}
				
					{input name=feedback type="select" options=$spamOptions nolabel}
					{input type="submit" stock="spam" nolabel}
				{/form}
				{button request="admin/blog/comment/notspam"
					param_commentID=$c->ID
					param_from=$coorgRequest
					coorgStock="notspam"}{/button} 
			</td>
		</tr>
	{/foreach}
	</table>
	
	<p class="notice">
		{'A feed is available for the all unmoderated comments, so you can check them in your favourite feed reader'|_|linkyfy:b:'blog.atom/comment/unmoderated'}
	</p>
	
	{pager pager=$qPager request="admin/blog/comment/index" page='.*.' coorgWidth=11}
{/block}
