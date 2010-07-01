<?php

function domainExists($domain)
{
	if ($domain == 'com' || $domain == 'gsdsd.b' || $domain == '.' ||
	    $domain == 'f.sc.ot.t.f.i.tzg.era.l.d.')
	{
		return false;
	}
	return true;
}

?>
