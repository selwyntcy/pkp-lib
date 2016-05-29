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

{* 20160211: hack: brand it as CUHK and DSCCC *}
<div class="pkp_structure_footer_wrapper" role="contentinfo">

	<div class="pkp_structure_footer">

		{if $pageFooter}
			<div class="pkp_footer_content">
				{$pageFooter}
			</div>
		{/if}

		<div class="pkp_brand_footer" role="complementary" aria-label="Links to CUHK and DSCCC">
{* hack 20160530: hardcoding issn in the footer for show *}
<p style="color: #666666;"><em>QUEST: Studies on Religion & Culture in Asia</em><br />ISSN: 2415-5993</p>
			<a href="http://www.cuhk.edu.hk/">
				<img alt="The Chinese University of Hong Kong" src="{$baseUrl}/templates/images/cuhk_emblem.png">
			</a>
			<a href="http://www.theology.cuhk.edu.hk/">
				<img alt="Divinity School of Chung Chi College" src="{$baseUrl}/lib/pkp/templates/images/dsccc_emblem.png">
			</a>
		</div>
	</div>

</div><!-- pkp_structure_footer_wrapper -->

</div><!-- pkp_structure_page -->

{call_hook name="Templates::Common::Footer::PageFooter"}
{* 20160128: hack: using cdn version of slick.js as upstream has removed it *}
{* todo: should move to theme *}
<script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.js"></script>
</body>
</html>
