{**
 * templates/controllers/informationCenter/notesList.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display submission file note list in information center.
 *}

<div id="{$notesListId}">
	{iterate from=notes item=note}
		{assign var=noteId value=$note->getId()}
		{if $noteFilesDownloadLink && isset($noteFilesDownloadLink[$noteId])}
			{assign var=downloadLink value=$noteFilesDownloadLink[$noteId]}
		{else}
			{assign var=downloadLink value=0}
		{/if}
		{assign var=noteViewStatus value=$note->markViewed($currentUserId)}
		{include file="controllers/informationCenter/note.tpl" noteFileDownloadLink=$downloadLink noteViewStatus=$noteViewStatus}
	{/iterate}
	{if $notes->wasEmpty()}
		<p>{translate key="informationCenter.noNotes"}</p>
	{/if}
</div>
