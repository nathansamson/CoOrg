
<section>
{form request="search" method="GET"}
	{input type="search" placeholder={"Search"|_} name="s" nolabel}
	
	{foreach $includeSearch as $include}	
		{input name="i[]" value=$include}
	{/foreach}
{/form}
</section>
