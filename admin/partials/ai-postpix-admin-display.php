<?php if (!defined('ABSPATH'))
	exit; // Exit if accessed directly ?>
<div id="postpixadmin">
	<div class="wrap">
		<div class="logo"></div>
		<p>Welcome to the AI Postpix plugin! First of all, you must enter your required API keys in the relevant fields.
		</p>
		<?php
		settings_errors('aipstx_settings');
		?>
		<div id="loading-gif" class="loading-gif" style="display: none;">
			<img src="<?php echo esc_url(plugins_url('img/settings-load.gif', dirname(dirname(__FILE__)))); ?>"
				alt="Loading..." />
		</div>
		<div class="postpix-settings-form">
			<div class="admin-left">

				<form method="post" action="" id="postpix-settings-form">
					<?php
					wp_nonce_field('aipstx_settings_action', 'aipstx_settings_nonce');
					?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">Theme Style:</th>
							<td>
								<input type="radio" name="aipstx_theme_style" value="light" <?php checked(get_option('aipstx_theme_style', 'light'), 'light', true); ?> /> Light
								Mode<br>
								<input type="radio" name="aipstx_theme_style" value="dark" <?php checked(get_option('aipstx_theme_style', 'light'), 'dark'); ?> /> Dark Mode
							</td>
						</tr>
					</table>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">Eden AI API Key:</th>
							<td><input type="text" id="aipstx_edenai_key" name="aipstx_edenai_key"
									value="<?php echo esc_attr(get_option('aipstx_edenai_key')); ?>"
									placeholder="Enter Your EdenAI API Key Here" /></td><button type="button"
								id="test_api_button" class="button button-secondary">Test My EdenAI API</button>
						</tr>
						<tr valign="top">
							<th scope="row">OpenAI API Key:</th>
							<td><input type="text" id="aipstx_openai_key" name="aipstx_openai_key"
									value="<?php echo esc_attr(get_option('aipstx_openai_key')); ?>"
									placeholder="Enter Your OpenAI API Key Here" /></td> <button type="button"
								id="aipstx_test_openai_button" class="button button-secondary">Test My OpenAI
								API</button>
						</tr>
					</table>

					<table class="form-table">
						<th scope="row">Engine for Find Prompt:</th>
						<td>
							<input type="radio" name="aipstx_prompt_engine" value="eden_ai" <?php checked('eden_ai', get_option('aipstx_prompt_engine', 'eden_ai')); ?> /> EdenAI <a
								href="https://docs.edenai.co/reference/start-your-ai-journey-with-edenai"
								target="_blank">How can you access the EdenAI API key for free? (with $5 Free API
								credit)</a><br>
							<input type="radio" name="aipstx_prompt_engine" value="gpt-4" <?php checked('gpt-4', get_option('aipstx_prompt_engine', 'gpt-4')); ?> /> GPT-4 <a
								href="https://help.openai.com/en/articles/7102672-how-can-i-access-gpt-4"
								target="_blank">How can you access the GPT-4 API? (paid and conditional)</a><br>
							<input type="radio" name="aipstx_prompt_engine" value="gpt-4-0613" <?php checked('gpt-4-0613', get_option('aipstx_prompt_engine', 'gpt-4-0613')); ?> />
							gpt-4-0613 (paid and conditional)<br>
							<input type="radio" name="aipstx_prompt_engine" value="gpt-3.5-turbo-1106" <?php checked('gpt-3.5-turbo-1106', get_option('aipstx_prompt_engine', 'gpt-3.5-turbo-1106')); ?> /> gpt-3.5-turbo-1106 <a
								href="https://help.openai.com/en/articles/4936850-where-do-i-find-my-api-key"
								target="_blank">How can you access the OpenAI API key?</a><br>
							<input type="radio" name="aipstx_prompt_engine" value="gpt-3.5-turbo" <?php checked('gpt-3.5-turbo', get_option('aipstx_prompt_engine', 'gpt-3.5-turbo')); ?> />
							gpt-3.5-turbo<br>
							<input type="radio" name="aipstx_prompt_engine" value="gpt-3.5-turbo-16k" <?php checked('gpt-3.5-turbo-16k', get_option('aipstx_prompt_engine', 'gpt-3.5-turbo-16k')); ?> /> gpt-3.5-turbo-16k<br>
							<input type="radio" name="aipstx_prompt_engine" value="gpt-3.5-turbo-instruct" <?php checked('gpt-3.5-turbo-instruct', get_option('aipstx_prompt_engine', 'gpt-3.5-turbo-instruct')); ?> /> gpt-3.5-turbo-instruct<br>
							</tr>
					</table>

					<?php submit_button('Save Changes', 'primary', 'postpix-settings-submit'); ?>
					<p style="font-size: 12px;">*You can find how to obtain API keys for EdenAI, OpenAI and GPT4 in the
						respective links.</p>
				</form>

			</div>
			<div class="admin-right">
				<h3>How to use Ai Postpix step by step?</h3>
				<ul>
					<li>After entering your API keys in the relevant fields you can see the Ai Postpix area at the
						bottom of WordPress blog posts. </li>
					<li>When you have completed your blog post, select any engine of your choice in the <span>"Generate
							Image with:"</span> field on the right side of the Ai Postpix area. <div class="spacer">
							<span>(This choice affects both the prompt you will find and the model you will use to
								generate the image.)</span></div>
					</li>
					<li>To find the most suitable prompt for your blog post, click on the <span>"Find Prompt"</span>
						button and wait for the AI to analyze your post. After that you can edit the generated prompt
						according to your wishes. </li>
					<li>With the <span>"Improve My Prompt"</span> button, one of the features of the Pro version, 5
						improved versions of your existing prompt are created and you can choose the one you want and
						use it. You can also use this feature unlimitedly for each prompt <div class="spacer">
							<span>(Prompts become more detailed and longer as they are improved).</span></div>
					</li>
					<li>Select how many images you want to create in the "Number of Images" section and in which
						resolutions you want to create images in the resolution section.<div class="spacer"><span>(Some
								models may not have some resolutions and number of images selection.)</span></div>
					</li>
					<li>With <span>"Image Styles"</span>, another feature of the Pro version, you can create images in
						any custom style you wish.</li>
					<li>Then click on <span>"Create Images"</span> and wait until the images are created. After the
						images are created, you can add the created images to your posts with the <span>"Add to
							Post"</span> button and make them the featured images of your posts with the <span>"Set as
							Featured Image"</span> button.</li>
					<li>You can save the image to your library with the <span>"Save to Library"</span> button on the top
						right of the created images, and you can download it directly to your computer with the
						<span>"Save to PC"</span> button.</li>
				</ul>
				<script>
					document.addEventListener('DOMContentLoaded', function () {
						var scrollButton = document.getElementById('scrollButton');
						var adminRight = document.querySelector('.admin-right');

						function checkScroll() {
							if (adminRight.scrollTop < (adminRight.scrollHeight - adminRight.offsetHeight)) {
								scrollButton.style.opacity = 1;
							} else {
								scrollButton.style.opacity = 0;
							}
						}

						adminRight.addEventListener('scroll', checkScroll);

						scrollButton.addEventListener('click', function () {
							adminRight.scrollBy({
								top: adminRight.offsetHeight,
								behavior: 'smooth'
							});
						});

						checkScroll();
					});
				</script> <button id="scrollButton"><i class="fas fa-arrow-down"></i></button>
			</div>
		</div>
	</div>

</div>