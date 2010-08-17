<script src="http://api.flattr.com/js/0.5.0/load.js" async></script>
<script>
	$(document).ready(function() { FlattrLoader.setup() });

</script>

<section>
	<a class="FlattrButton"
		title="{$flattrTitle}"
		rev="flattr;uid:{$flattrUID};{if $flattrTags}tags:{implode(',', $flattrTags)};{/if}category:{$flattrCategory};button:{$flattrButton};language:{$flattrLanguage};"
		href="{$flattrLink}"
		language="{$flattrLanguage}">{strip_tags($flattrDescription)}</a>
</section>
