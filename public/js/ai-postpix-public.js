jQuery(document).ready(function ($) {

	var button = document.getElementById('aipstx_create_image');
	var container = document.getElementById('pv_images_container');

	if (button && container) {
		button.addEventListener('click', function () {
			container.style.display = 'flex';
		});
	}

	var textarea = document.getElementById('pv_prompt');
	var button = document.getElementById('aipstx_create_image');

	// Buton ve textarea varsa, fonksiyonları ve olay dinleyicilerini etkinleştir
	if (textarea && button) {
		// Butonun başlangıç durumunu ayarla
		toggleButtonState();

		// Textarea'daki değişiklikleri dinle
		textarea.addEventListener('input', function () {
			toggleButtonState();
		});
	}

	function toggleButtonState() {
		// Eğer textarea boşsa butonu devre dışı bırak, değilse etkinleştir
		if (textarea.value.trim() === '') {
			button.disabled = true;
		} else {
			button.disabled = false;
		}
	}
	

	function showAlert(message, success = true) {
		var alertBox = $('#postpix-alert');
		alertBox.removeClass('postpix-alert-success postpix-alert-error');

		if (success) {
			alertBox.addClass('postpix-alert-success').text(message);
		} else {
			alertBox.addClass('postpix-alert-error').text(message);
		}

		alertBox.fadeIn(500, function () {
			setTimeout(function () {
				alertBox.fadeOut(500);
			}, 3000); // 3 saniye sonra kaybolacak
		});
	}

	// Function to add image to post content after adding it to the media library
	var postId = jQuery("#post_ID").val();

	function addToPostContent(imageUrl, button) {
		var mediaId = null; // This will be set after the AJAX call

		// AJAX call to add image to media library
		$.ajax({
			url: aipstxAjax.ajax_url,
			type: "POST",
			data: {
				action: "aipstx_add_media_library",
				nonce: aipstxAjax.nonce,
				image_url: imageUrl,
				prompt: $("#pv_prompt").val() // Getting the prompt value from the input
			},
			success: function (response) {
				if (response.success) {
					mediaId = response.data.attachment_id; // Set the media ID from the AJAX response

					// AJAX call to add image to post content
					$.ajax({
						url: aipstxAjax.ajax_url,
						type: "POST",
						data: {
							action: "aipstx_add_post_content",
							nonce: aipstxAjax.nonce,
							post_id: postId,
							media_id: mediaId,
						},
						success: function (response) {
							if (response.success) {
								// Check if the Gutenberg editor is available
								if (wp.data && wp.data.select("core/editor")) {
									// For Gutenberg, update the post content
									var currentContent = wp.data.select("core/editor").getEditedPostContent();
									var newContent = currentContent + response.data.image_html;
									wp.data.dispatch("core/editor").editPost({ content: newContent });
								} else {
									// For the Classic Editor, update the post content
									if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
										tinyMCE.get('content').setContent(tinyMCE.get('content').getContent() + response.data.image_html);
									} else {
										// Fallback for the text area (no TinyMCE)
										var textArea = $('#content');
										textArea.val(textArea.val() + response.data.image_html);
									}
								}
								showAlert("Successfully added an image to the post content. If it does not appear, refresh the page.", true);
								// Modify only the clicked button
								button.prop('disabled', true).text('Added to post').addClass('button-disabled');
							} else {
								showAlert("Failed to add image to post content.", false);
							}
						},
						error: function () {
							showAlert("An error occurred while adding the image to the post content.", false);
						}
					});
				} else {
					showAlert("Failed to add image to media library.", false);
				}
			},
			error: function () {
				showAlert("An error occurred while adding the image to the media library.", false);
			}
		});
	}

	

	var engineSelector = $('#engine');
	var resolutionSelector = $('#resolution');
	var imageNumberDiv = $('.numberimages');
	var imageCountSelector = $('#postpix_image_count');

function updateEngineAndResolutionOptions() {
    var selectedEngine = engineSelector.val();
    var selectedResolution = resolutionSelector.val();

    // Select elementini ve içindeki option'ları disable yapma ve sınıf ekleme
    function disableSelect(selector, disable) {
        $(selector).prop('disabled', disable);
        if (disable) {
            $(selector).addClass('disabled-select');
        } else {
            $(selector).removeClass('disabled-select');
        }
    }

    // Option'ları disable yapma ve sınıf ekleme
    function disableOption(selector, disable) {
        $(selector).prop('disabled', disable);
        if (disable) {
            $(selector).addClass('disabled-option');
        } else {
            $(selector).removeClass('disabled-option');
        }
    }

    // Image Number Div kontrolü
    if (['openai', 'stabilityai', 'dall-e3', 'deepai'].includes(selectedEngine)) {
        disableSelect(imageNumberDiv.find('select'), false);
    } else {
        disableSelect(imageNumberDiv.find('select'), true);
    }

    // DALL-E 3 seçildiğinde 256x256 ve 512x512 çözünürlükleri disable yap
    if (selectedEngine === 'dall-e3') {
        disableOption(resolutionSelector.find('option[value="256x256"], option[value="512x512"]'), true);
    } else {
        disableOption(resolutionSelector.find('option'), false);
    }

    // DeepAI seçildiğinde 1024x1024 disable yap ve 512x512 hariç diğer çözünürlükleri etkinleştir
    if (selectedEngine === 'deepai') {
        disableOption(resolutionSelector.find('option[value="1024x1024"]'), true); // 1024x1024'ü disable yap
        disableOption(resolutionSelector.find('option[value="256x256"], option[value="512x512"]'), false); // 256x256 ve 512x512'yi etkinleştir
    } else {
        disableOption(resolutionSelector.find('option[value="1024x1024"]'), false); // DeepAI seçili değilse 1024x1024'ü etkinleştir
    }

    // 1024x1024 seçildiğinde DeepAI'yi disable yap
    if (selectedResolution === '1024x1024') {
        disableOption(engineSelector.find('option[value="deepai"]'), true);
    } else {
        disableOption(engineSelector.find('option[value="deepai"]'), false);
    }

    // Image Count Selector için max sınırı DALL-E 3 için 6'ya ayarlama
    if (selectedEngine === 'dall-e3') {
        imageCountSelector.find('option').each(function () {
            if (parseInt($(this).val()) > 6) {
                disableOption($(this), true);
            } else {
                disableOption($(this), false);
            }
        });
    } else {
        imageCountSelector.find('option').prop('disabled', false).removeClass('disabled-option');
    }

    // 256x256 seçildiğinde sadece DALL-E 3'ü disable yap
    if (selectedResolution === '256x256') {
        disableOption(engineSelector.find('option[value="dall-e3"]'), true);
    } else {
        disableOption(engineSelector.find('option[value="dall-e3"]'), false);
    }

    // DeepAI seçiliyken ve 1024x1024 seçili değilse, tüm motor seçeneklerini etkinleştir
    if (selectedEngine !== 'deepai' || selectedResolution !== '1024x1024') {
        disableOption(engineSelector.find('option'), false);
    }
}

	// İlk yükleme ve her değişiklikte seçenekleri güncelle
	updateEngineAndResolutionOptions();
	engineSelector.change(updateEngineAndResolutionOptions);
	resolutionSelector.change(updateEngineAndResolutionOptions);


	// Event listener for the 'Add to Library' button
	$(document).on("click", ".add-to-post-btn", function () {
		var imageUrl = $(this).data("image-url");
		var button = $(this); // Get the current button that was clicked
		addToPostContent(imageUrl, button); // Pass the button to the function
	});

	$("#aipstx_find_prompt").click(function (e) {
		e.preventDefault();
		// Yükleme göstergesini göster
		$("#pv_loading").show();

		var postContent;
		if (window.wp && wp.data && wp.data.select("core/editor")) {
			postContent = wp.data.select("core/editor").getEditedPostContent();
		} else {
			postContent = $("#content").val();
		}

		// Post içeriğini temizle
		var cleanContent = postContent
			.replace(/<!--(.|\s)*?-->/g, '') // WordPress blok yorumlarını kaldır
			.replace(/<[^>]+>/g, '') // HTML etiketlerini kaldır
			.replace(/http[s]?:\/\/[^\s]+/g, '') // Görsel URL'lerini kaldır
			.replace(/&nbsp;/g, ' '); // &nbsp; karakterlerini normal boşlukla değiştir

		var engineText = $("#engine option:selected").text();

		var postData = {
			action: "aipstx_find_prompt",
			nonce: aipstxAjax.nonce,
			postContent: cleanContent, // Temizlenmiş içeriği gönder
			engine: engineText
		};


		$.post(aipstxAjax.ajax_url, postData, function (response) {
			if (response.success) {
				$("#pv_prompt").val(response.data);
				toggleButtonState()
			} else {
				alert("Error: " + response.data);
			}
			// İşlem bittiğinde yükleme göstergesini gizle
			$("#pv_loading").hide();
		});
	});


	$("#aipstx_create_image").click(function (e) {
		e.preventDefault();
		var prompt = $("#pv_prompt").val();
		var imageCount = $("#postpix_image_count").val(); // Dropdown'dan alınan görsel sayısı
		var engine = $("#engine").val();
		var resolution = $("#resolution").val();
		var aipstx_styles = [];
		$('input[name="style[]"]:checked').each(function () {
			aipstx_styles.push($(this).val());
		});
		$("#pv_images_container").html(
			'<div class="loading-gif-container"><img src="' + aipstxParams.loadingGifUrl + '" alt="Loading..." /></div>');
		$.ajax({
			url: aipstxAjax.ajax_url,
			type: "POST",
			data: {
				action: "aipstx_create_image",
				nonce: aipstxAjax.nonce,
				prompt: prompt,
				image_count: imageCount, // Gönderilecek görsel sayısı
				resolution: resolution, // Kullanıcının seçtiği çözünürlük
				engine: engine,
				style: aipstx_styles
			},
			success: function (response) {
				$("#pv_images_container").empty();
				if (response.success) {
					response.data.forEach(function (imageUrl) {
						// Görselleri eklerken 'pv_image_preview' CSS sınıfını kullan

						var imageHtml = '<div class="pv_image_container">';
						imageHtml +=
							'<img src="' +
							imageUrl +
							'" class="pv_image_preview" alt="Generated Image">';
						imageHtml +=
							'<button type="button" class="add-to-post-btn" data-image-url="' +
							imageUrl +
							'">Add to Post</button>';
						imageHtml +=
							'<button type="button" class="set-featured-image-btn" data-image-url="' +
							imageUrl +
							'">Set as Featured Image</button>';
						imageHtml += "</div>";
						$("#pv_images_container").append(imageHtml);
					});
					// Butonun metnini değiştir

					$("#aipstx_create_image")
						.text("Create More Images");
					// Buton tıklama işleyicilerini burada ekleyebiliriz veya dışarıda ayrı bir fonksiyon olarak
					// Bu örnek kodda işleyici eklenmemiştir, bu kısım da güncellenmelidir

					jQuery(document).on("click", ".set-featured-image-btn", function () {
						var button = $(this);
						//    var postId = button.data('post-id'); // The ID of the post to set the featured image for
						var imageUrl = button.data("image-url"); // The URL of the image to be set as featured

						// First, add the image to the media library
						$.ajax({
							url: aipstxAjax.ajax_url,
							type: "POST",
							data: {
								action: "aipstx_add_media_library",
								image_url: imageUrl,
								nonce: aipstxAjax.nonce,
								prompt: $("#pv_prompt").val(), // Getting the prompt value from the input
							},
							beforeSend: function () {
								button.prop("disabled", true).text("Setting...");
							},
							success: function (response) {
								if (response.success) {
									// The image was successfully added to the media library, now set it as the featured image
									var attachmentId = response.data.attachment_id; // The attachment ID returned from the AJAX response
									setAsFeaturedImage(postId, attachmentId, button);
									showAlert("The image was successfully set as the featured image.", true);
								} else {
									button.prop("disabled", false).text("Set as Featured Image");
									showAlert("Failed to add image to media library.", false);
								}
							},
							error: function () {
								button.prop("disabled", false).text("Set as Featured Image");
								showAlert("An error occurred.", false);
							},
						});
					});



					$('.pv_image_container').each(function () {
						var imageUrl = $(this).find('img').attr('src');
						var saveButtonHtml = '<button type="button" class="save-to-library-btn" data-image-url="' + imageUrl + '"><i class="fas fa-save"></i><span class="button-text">Save to Library</span></button>';
						var saveToPcButtonHtml = '<button type="button" class="save-to-pc-btn" data-image-url="' + imageUrl + '"><i class="fas fa-download"></i><span class="button-text">Save to PC</span></button>';
						$(this).prepend(saveButtonHtml); // Butonu konteynerin başına ekler
						$(this).prepend(saveToPcButtonHtml);
					});
					$(document).on('click', '.save-to-library-btn', function () {
						var button = $(this); // Buton referansı
						var imageUrl = button.data('image-url'); // Görselin URL'sini alın

						// AJAX çağrısı yaparak görseli medya kütüphanesine ekleyin
						$.ajax({
							url: aipstxAjax.ajax_url,
							type: 'POST',
							data: {
								action: 'aipstx_add_media_library',
								image_url: imageUrl,
								nonce: aipstxAjax.nonce
							},
							beforeSend: function () {
								button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
							},
							success: function (response) {
								if (response.success) {
									// Görselin medya kütüphanesine başarıyla eklendiğini kullanıcıya bildir
									showAlert('Image saved to library!', true);
									button.prop('disabled', true).html('<i class="fas fa-check"></i>').addClass('button-disabled');
								} else {
									// Hata mesajını göster
									alert(response.data.message);
									button.prop('disabled', false).text('Save to Library');
								}
							},
							error: function () {
								showAlert('Failed to save image.', false);
								button.prop('disabled', false).text('Save to Library');
							}
						});
					});

					// 'Save to PC' butonuna tıklandığında indirme işlemi gerçekleştirilir
					$(document).on('click', '.save-to-pc-btn', function () {
						var imageUrl = $(this).data('image-url'); // Görselin URL'sini al
						var link = document.createElement('a');
						link.href = imageUrl;                      // Linkin href'ini görselin URL'si ile ayarla
						link.target = '_blank';                    // Linki yeni bir sekmede açacak şekilde ayarlayın
						link.click();                              // Linki programatik olarak tıkla
					});



					// Function to set the uploaded image as the featured image of the post
					function setAsFeaturedImage(postId, attachmentId, button) {
						$.ajax({
							url: aipstxAjax.ajax_url,
							type: "POST",
							data: {
								action: "aipstx_ensure_image_and_set_featured",
								post_id: postId,
								image_id: attachmentId,
								nonce: aipstxAjax.nonce,
							},
							success: function (response) {
								if (response.success) {
									var newFeaturedImageUrl = response.data.featured_image_url;

									// Gutenberg editörü için
									if (wp.data && wp.data.select("core/editor")) {
										// Öne çıkan görselin URL'sini güncelle

										// Gutenberg veri deposunu güncelleyin
										wp.data.dispatch('core/editor').editPost({ featured_media: attachmentId });

										// "Öne Çıkan Görsel Belirle" düğmesini gizle
										$('.components-button editor-post-featured-image__toggle').hide();

									} else {
										// Klasik editör için
										var $thumbnailImg = jQuery('#set-post-thumbnail img');
										if ($thumbnailImg.length > 0) {
											// Eğer görsel varsa, 'src' ve 'srcset' niteliklerini güncelle
											var newImageUrlWithTimestamp = newFeaturedImageUrl + '?t=' + new Date().getTime();
											$thumbnailImg.attr('src', newImageUrlWithTimestamp);
											$thumbnailImg.attr('srcset', newImageUrlWithTimestamp); // srcset'i de güncelle
										} else {
											// Eğer görsel yoksa, yeni bir <img> etiketi oluştur
											var newImageTag = '<img src="' + newFeaturedImageUrl + '" width="266" height="266" class="attachment-266x266 size-266x266" alt="" decoding="async" loading="lazy">';
											jQuery('#set-post-thumbnail').prepend(newImageTag);
										}
										// Görseli göstermek için var olan bağlantıyı göster
										jQuery('#set-post-thumbnail').show();
									}

									button.prop("disabled", true).text("Featured Image Set").addClass('button-disabled');
								} else {
									button.prop("disabled", false).text("Set as Featured Image");
									showAlert("Failed to set featured image.", false);
								}
							},
							error: function (err) {
								button.prop("disabled", false).text("Set as Featured Image");
								showAlert("An error occurred while setting the featured image.", false);
								console.log(err);
							},
						});
					}

				} else {
					$("#pv_images_container").html("Error: " + response.data);
					$("#aipstx_create_image")
						.text("Create Images");
				}
			},
			error: function () {
				$("#pv_images_container").html(
					"Error: An error occurred while creating the images."
				);
				$("#aipstx_create_image")
					.text("Create Images");
			},
		});
	});


});