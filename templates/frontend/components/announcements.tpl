{**
 * templates/frontend/components/announcements.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a list of announcements
 *
 * @uses $announcements array List of announcements
 *}

<ul class="cmp_announcements">
	{foreach from=$announcements item=announcement}
		<li>
			{include file="frontend/objects/announcement_summary.tpl"}
		</li>
	{/foreach}
</ul>
