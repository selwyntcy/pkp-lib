{**
 * templates/frontend/components/footer.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Common site frontend footer.
 *
 * @uses $isFullWidth bool Should this page be displayed without sidebars? This
 *       represents a page-level override, and doesn't indicate whether or not
 *       sidebars have been configured for thesite.
 *}

	</div><!-- pkp_structure_main -->

	{* Sidebars *}
	{if empty($isFullWidth)}
		{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
		{if $leftSidebarCode}
			<div class="pkp_structure_sidebar left" role="complementary" aria-label="{translate|escape key="common.navigation.sidebar"}">
				{$leftSidebarCode}
			</div><!-- pkp_sidebar.left -->
		{/if}
	{/if}
</div><!-- pkp_structure_content -->

{* 20160125: hack: disable bottom site logo *}
{*<div class="pkp_structure_footer_wrapper role="contentinfo">*}
<div class="pkp_structure_footer_wrapper" role="contentinfo" style="display: none;">

	<div class="pkp_structure_footer">

		<div class="pkp_site_name_wrapper">
			{* Logo or site title. *}
			<div class="pkp_site_name">
				{if $currentJournal && $multipleContexts}
					{url|assign:"homeUrl" journal="index" router=$smarty.const.ROUTE_PAGE}
				{else}
					{url|assign:"homeUrl" page="index" router=$smarty.const.ROUTE_PAGE}
				{/if}
				{if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
					<a href="{$homeUrl}" class="is_img" rel="home">
						<img src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width|escape}" height="{$displayPageHeaderLogo.height|escape}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} />
					</a>
				{elseif $displayPageHeaderTitle && !$displayPageHeaderLogo && is_string($displayPageHeaderTitle)}
					<a href="{$homeUrl}" class="is_text" rel="home">{$displayPageHeaderTitle}</a>
				{elseif $displayPageHeaderTitle && !$displayPageHeaderLogo && is_array($displayPageHeaderTitle)}
					<a href="{$homeUrl}" class="is_img">
						<img src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" alt="{$displayPageHeaderTitle.altText|escape}" width="{$displayPageHeaderTitle.width|escape}" height="{$displayPageHeaderTitle.height|escape}" />
					</a>
				{else}
					<a href="{$homeUrl}" class="is_img" rel="home">
						{* hack: show Quest logo in site footer *}
						{*<img src="{$baseUrl}/templates/images/structure/logo.png" alt="{$applicationName|escape}" title="{$applicationName|escape}" width="180" height="90" />*}
						<img src="{$baseUrl}/public/journals/1/pageHeaderLogoImage_en_US.png" alt="{$applicationName|escape}" title="{$applicationName|escape}" width="180" height="90" />
					</a>
				{/if}
			</div>
		</div>

		{* include a section if there are footer link categories defined *}
		{if $footerCategories|@count > 0}
			<div class="categories categories_{$footerCategories|@count}">
				{foreach from=$footerCategories item=category name=loop}
					{assign var=links value=$category->getLinks()}
					<div class="category category_{$loop.index}">
						<h4>{$category->getLocalizedTitle()|strip_unsafe_html}</h4>
						<ul>
							{foreach from=$links item=link}
								<li><a href="{$link->getLocalizedUrl()}">{$link->getLocalizedTitle()|strip_unsafe_html}</a></li>
							{/foreach}
						</ul>
					</div>
				{/foreach}
			</div><!-- pkp_structure_footer categories -->

			{if $pageFooter}
				<div class="page_footer">
					{$pageFooter}
				</div>
			{/if}<!-- pkp_structure_footer page_footer -->
		{/if}
	</div><!-- pkp_structure_footer -->

</div><!-- pkp_structure_footer_wrapper -->

<div class="pkp_brand_footer" role="complementary" aria-label="{translate|escape key="about.aboutThisPublishingSystem"}">
{* hack: begins
	<a href="{url page="about" op="aboutThisPublishingSystem"}">
		<img alt="{translate key=$packageKey}" src="{$baseUrl}/{$brandImage}">
	</a>
	<a href="{$pkpLink}">
		<img alt="{translate key="common.publicKnowledgeProject"}" src="{$baseUrl}/lib/pkp/templates/images/pkp_brand.png">
	</a>
*}
{* hack: before they figure out how to handle a custom footer (https://github.com/pkp/pkp-lib/issues/782), we hardcode the footer *}
{* also hacked the corresponding less style sheet lib/pkp/styles/structures/foot.less and plubins/themes/default/styles/footer.less *}
	<a href="http://www.cuhk.edu.hk/">
		<img alt="The Chinese University of Hong Kong" src="{$baseUrl}/templates/images/cuhk_emblem.png">
	</a>
	<a href="http://www.theology.cuhk.edu.hk/">
		<img alt="Divinity School of Chung Chi College" src="{$baseUrl}/lib/pkp/templates/images/dsccc_emblem.png">
	</a>
</div>

</div><!-- pkp_structure_page -->

{call_hook name="Templates::Common::Footer::PageFooter"}
</body>
</html>
