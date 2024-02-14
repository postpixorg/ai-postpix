jQuery(document).ready(function ($) {


	window.isLoadingGifVisible = false;
	window.deferredNotification = null;

	function showLoadingGif() {
		window.isLoadingGifVisible = true;
		// Ekranda var olan tüm bildirimleri kaldır
		$('.notification').remove();
		$('#loading-gif').show();
	}

	function hideLoadingGif() {
		$('#loading-gif').fadeOut('fast', function () {
			window.isLoadingGifVisible = false;
			if (window.deferredNotification) {
				showNotification(window.deferredNotification.type, window.deferredNotification.message);
				window.deferredNotification = null; // Bekleyen bildirimi sıfırlayalım.
			}
		});
	}


	// Eden AI API Testi
	$('#test_api_button').off('click').on('click', function (e) {
		e.preventDefault();
		showLoadingGif();
		var data = {
			'action': 'aipstx_test_edenai',
			'test_api': $('#aipstx_edenai_key').val(), // 'api_key' yerine 'test_api' kullanın
			'nonce': $('#aipstx_settings_nonce').val()
		};
		sendAjaxRequest(data);
	});

	// OpenAI API Testi
	$('#aipstx_test_openai_button').off('click').on('click', function (e) {
		e.preventDefault();
		showLoadingGif();
		var data = {
			'action': 'aipstx_test_openai',
			'aipstx_test_openai': $('#aipstx_openai_key').val(), // 'api_key' yerine 'aipstx_test_openai' kullanın
			'nonce': $('#aipstx_settings_nonce').val()
		};
		sendAjaxRequest(data);
	});

	function sendAjaxRequest(data) {
		$.ajax({
			type: 'POST',
			url: aipstxAjax.ajax_url,
			data: data,
			success: function (response) {
				hideLoadingGif();
				showNotification(response.success ? 'success' : 'error', response.data);
			},
			error: function () {
				hideLoadingGif();
				showNotification('error', 'An error occurred while making the request.');
			}
		});
	}

	function showNotification(type, message) {
		// Eğer yükleme GIF'i aktifse, bildirimi beklemeye alın
		if (window.isLoadingGifVisible) {
			window.deferredNotification = { type: type, message: message };
			return;
		}
		var notificationHtml = '<div class="notification ' + type + '">' + message + '</div>';
		$('body').append(notificationHtml);
		$('.notification').fadeIn().delay(3000).fadeOut(function () {
			$(this).remove();
		});
	}



	const settingsForm = document.getElementById('postpix-settings-form');

	if (settingsForm) {
		const edenApiKeyInput = document.getElementById('aipstx_edenai_key');
		const openaiApiKeyInput = document.getElementById('aipstx_openai_key');
		const radioButtons = document.querySelectorAll('[name="aipstx_prompt_engine"]');

		function updateRadioButtons() {
			const isEdenKeyEmpty = !edenApiKeyInput.value.trim();
			const isOpenAIKeyEmpty = !openaiApiKeyInput.value.trim();
			let isAnyGPTSelected = false;

			radioButtons.forEach(radioButton => {
				if (radioButton.value.startsWith('gpt-') && radioButton.checked) {
					isAnyGPTSelected = true;
				}

				if (isEdenKeyEmpty && isOpenAIKeyEmpty) {
					radioButton.checked = false;
					radioButton.disabled = true;
				} else if (isEdenKeyEmpty && radioButton.value === 'eden_ai') {
					radioButton.disabled = true;
				} else if (isOpenAIKeyEmpty && radioButton.value.startsWith('gpt-')) {
					radioButton.disabled = true;
				} else {
					radioButton.disabled = false;
				}
			});

			// Eğer aipstx_edenai_key dolu, aipstx_openai_key boşsa ve hiçbir şey seçili değilse
			if (!isEdenKeyEmpty && isOpenAIKeyEmpty) {
				document.querySelector('[value="eden_ai"]').checked = true;
			}

			// Eğer aipstx_openai_key dolu, aipstx_edenai_key boşsa, hiçbir GPT seçili değilse ve herhangi bir radio butonu seçili değilse
			if (!isOpenAIKeyEmpty && isEdenKeyEmpty && !isAnyGPTSelected) {
				document.querySelector('[value="gpt-3.5-turbo-1106"]').checked = true;
			}

			// Eğer iki input da dolu ve hiçbir şey seçili değilse
			if (!isEdenKeyEmpty && !isOpenAIKeyEmpty && !document.querySelector('[name="aipstx_prompt_engine"]:checked')) {
				document.querySelector('[value="eden_ai"]').checked = true;
			}
		}

		edenApiKeyInput.addEventListener('input', updateRadioButtons);
		openaiApiKeyInput.addEventListener('input', updateRadioButtons);

		// Başlangıçta durumu kontrol et
		updateRadioButtons();
	}

});