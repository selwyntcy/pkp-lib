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

		{if $pageFooter}
			<div class="pkp_footer_content">
				{$pageFooter}
			</div>
		{/if}

		<div class="pkp_brand_footer" role="complementary" aria-label="{translate|escape key="about.aboutThisPublishingSystem"}">
			<a href="{url page="about" op="aboutThisPublishingSystem"}">
				<img alt="{translate key=$packageKey}" src="{$baseUrl}/{$brandImage}">
			</a>
			<a href="{$pkpLink}">
				<img alt="{translate key="common.publicKnowledgeProject"}" src="{$baseUrl}/lib/pkp/templates/images/pkp_brand.png">
			</a>
		</div>
	</div>

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
{* 20160128: hack: using cdn version of slick.js as upstream has removed it *}
{* todo: should move to theme *}
<script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick.min.js"></script>
</body>
</html>
