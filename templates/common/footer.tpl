{**
 * templates/common/footer.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *}

</div><!-- pkp_structure_main -->
</div><!-- pkp_structure_body -->

<div class="pkp_structure_footer" role="contentinfo">
	<div class="pkp_brand_footer">
{*
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
</div>

</body>
</html>
