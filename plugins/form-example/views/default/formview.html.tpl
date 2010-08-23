{block name="title"}
	Form elements overview
{/block}

{block name="content"}
	
	<h1>Form elements overview</h1>
	
	<h2>First form</h2>
	{form id="someID" instance=$myInstance}
		{input type="text" name="name" placeholder="Your name" value="The Name" label="Name" required}
		{input type="url" name="theURL" placeholder="http://google.be/" label="Some disabled URL (with placeholder)" disabled}
		{input type="email" name="theEmail" placeholder="youremail@site.com" label="Some readonly email" readonly}
		{input type="password" value="Me Very long Password" placeholder="Password" label="Password" name="password"}
		{input for=someValue label="Errored value"}
		
		<fieldset>
			<legend>Two textarea's</legend>
			{input type="textarea" label="The text" name="theTextArea1"}
			{input type="textarea" nolabel editor=full required name="theEditor"}
		</fieldset>
		
		{input type="date" name="theDate" label="Datepicker"}
		
		<fieldset>
			<legend>Tab index test</legend>
			{input type="text" name="someText" tabindex=2 label="Second tabindex"}
			{input type="text" name="someText2" tabindex=1 label="First tabindex (required)" required}
		</fieldset>
		
		<fieldset>
			<legend>Textarea size</legend>
			{input type="textarea" label="Small" name="small" size=small}
			{input type="textarea" label="Big" name="big" size=big}
			{input type="textarea" label="Wide" name="wide" size=wide}
			{input type="textarea" label="Small but wide" name="small-wide" size="small-wide"}
		</fieldset>
		
		{input type="text" name="wide" label="Wide" required size=wide}
		{input type="text" name="full-wide" label="Even wider" required size="full-wide"}
		
		{input type=submit label="Disabled input" disabled name="disabled"}
		{input type=submit label="Working input"}
		{input type=submit nolabel stock="list-add"}
		{input type=submit nolabel stock="list-remove" disabled}
		
		<fieldset>
			<legend>Radio's and checboxes etc...</legend>
			{input type=radio label="Gender" value="M" options=$genders name=gender}
			{input type=checkbox label="Intrests" value=$myIntrests options=$allIntrests name=intrests}
			
			{input type=select label="Country" value="be" options=$countries name=country}
			{input type=select label="Intrests in another way" value=$myIntrests options=$allIntrests name=myIntrests2 multiple}
		</fieldset>
		
		<fieldset>
			<legend>Disabled Radio's and checboxes etc...</legend>
			{input type=radio label="Gender" value="M" options=$genders name=gender2 disabled}
			{input type=checkbox label="Intrests" value=$myIntrests options=$allIntrests name=intrests2 disabled}
			
			{input type=select label="Country" value="be" options=$countries name=country2 disabled}
			{input type=select label="Intrests in another way" value=$myIntrests options=$allIntrests name=myIntrests3 multiple disabled}
		</fieldset>
	{/form}
	
{/block}
