<HTML>
<HEAD>
<TITLE>{$title|escape}</TITLE>
<link type="text/css" rel="stylesheet"
	href="/css/ui-lightness/jquery-ui-1.8.17.custom.css">

{foreach from=$csses item=css }

<link type="text/css" rel="stylesheet"
	href="{$css}">
{/foreach}




<script type="text/javascript" src="/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.8.17.custom.min.js"></script>

{foreach from=$jses item=js }

<script type="text/javascript" src="{$js}"></script>

{/foreach}

</HEAD>
<BODY bgcolor="#ffffff">

{$content}

</BODY>
</HTML>
