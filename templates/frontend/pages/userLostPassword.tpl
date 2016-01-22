{**
 * templates/frontend/pages/userLostPassword.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Password reset form.
 *
 *}
{include file="frontend/components/header.tpl" pageTitle="user.login.resetPassword"}

<div class="page page_lost_password">
	<h1 class="page_title">
		{translate key="user.login.resetPassword"}
	</h1>

	<p>{translate key="user.login.resetPasswordInstructions"}</p>

	<script>
		$(function() {ldelim}
			// Attach the form handler.
			$('#lostPasswordForm').pkpHandler('$.pkp.controllers.form.FormHandler');
		{rdelim});
	</script>

	<form class="pkp_form lost_password" id="lostPasswordForm" action="{url page="login" op="requestResetPassword"}" method="post">
		{if $error}
			<div class="pkp_form_error">
				{translate key=$error}
			</div>
		{/if}

		<fieldset class="email">
			<ul class="fields">
				<li class="email">
					<label>
						<span class="label">
							{translate key="user.login.registeredEmail"}
						</span>
						<input type="text" name="email" id="email" value="{$email|escape}" maxlength="32" required>
					</label>
				</li>
			</ul>
		</fieldset>

		<fieldset class="buttons">
			<button class="submit" type="submit">
				{translate key="user.login.resetPassword"}
			</button>

			{if !$hideRegisterLink}
				{url|assign:registerUrl page="user" op="register" source=$source}
				<a href="{$registerUrl}" class="register">
					{translate key="user.login.registerNewAccount"}
				</a>
			{/if}
		</fieldset>
	</form>

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
