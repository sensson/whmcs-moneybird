<span class="header">
	<img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16">
	{$language.modulename}
</span>

<ul class="menu">
	<li><a href="{$modulelink}">{$language.home}</a></li>
{foreach from=$pages key=name item=page}
	{if $page.type eq 'page'}
	<li><a href="{$modulelink}&amp;page={$name}">{$language.$name}</a></li>
	{/if}
{/foreach}
</ul>
