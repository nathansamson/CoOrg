{'Hi,

A new comment was posted for the blog at %URL'|_:$messageURL}

{if $totalModerationQueue > 1}
{'Notice that their are  in total %X messages waiting to be moderated.'|_:$totalModerationQueue}
{'You can find them at %link'|_:$moderationURL}
{/if}

{'The message was posted on %date and the title was "%title".'|_:($date|date_format:'Y-m-d H:i:s'):$title}

{'The text of the message is:
%body'|_:($body|escape)}



{'I hope it was not really spam, but a meningful and intresting message!'|_}
