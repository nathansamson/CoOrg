{block name="title"}{'Mollom config'|_}{/block}

{block name="admin-content"}
	<h1>{'Mollom config'|_}</h1>
	
	{form request="admin/mollom/save" instance=$mollomConfig}
		{input for=publicKey label="Mollom public key" size=wide required}
		{input for=privateKey label="Mollom private key" size=wide required}
	
		{input type="submit" label="Save configuration"}
	{/form}
{/block}
