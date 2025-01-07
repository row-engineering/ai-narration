document.addEventListener('DOMContentLoaded', function () {
	if ( !document.body.classList.contains('ai-narrations_page_ai-narration-narrations') ) {
		return;
	}

	const fetchConfig = {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded'
		},
		credentials: 'same-origin'
	};

	function serializeData(obj) {
		const formData = new URLSearchParams();
		Object.keys(obj).forEach(key => {
			if (Array.isArray(obj[key])) {
				obj[key].forEach(value => {
					formData.append(`${key}[]`, value);
				});
			} else {
				formData.append(key, obj[key]);
			}
		});
		return formData.toString();
	}

	async function generateNarration(postIDs) {
		postIDs.forEach(postID => updateBtnText(postID, 'Generating...'))

		try {
			const response = await fetch(ajaxurl, {
				...fetchConfig,
				body: serializeData({
					action: 'generate_narration',
					post_ids: postIDs,
					nonce: narrationAdmin.nonce
				})
			});
			const data = await response.json()
			if (data.success) {
				const reply = data.data
				if (reply) {
					if (reply.status !== 200) {
						updateBtnText(reply.post_id, 'Generation failed')
						alert(`Couldn't generate narration for post ${reply.post_id}. ${reply.message}`)
					} else {
						updateGenerationStatus(reply.post_id)
					}
				}
			} else {
				if (data.data) {
					updateBtnText(data.data.post_id, 'Generation failed')
					alert(`Error generating narration. ${data.data.message}`);
				}
			}
		} catch (error) {
			updateBtnText(error.post_id, 'Generation failed')
			console.error('Error generating narration:', error);
			alert('Error generating narration: ' + error.message);
		}
	}

	async function deleteNarration(postIDs) {
		try {
			const response = await fetch(ajaxurl, {
				...fetchConfig,
				body: serializeData({
					action: 'delete_narration',
					post_ids: postIDs,
					nonce: narrationAdmin.nonce
				})
			});
			const data = await response.json();
			if (data.success) {
				location.reload();
			} else {
				throw new Error(data.data || 'Unknown error');
			}
		} catch (error) {
			console.error('Error deleting narration:', error);
			alert('Error deleting narration: ' + error.message);
		}
	}

	function updateGenerationStatus(postID) {
		const btn = document.querySelector(`.generate-narration[data-post-id="${postID}"]`)

		const maxTime = 10 * 60 * 1000 // 10 min
		let attempts = 0

		const checkProgress = setInterval(() => {
			attempts++
			if (attempts * 3000 > maxTime) {
				updateBtnText(postID, 'Generation timed out')
				clearInterval(checkProgress)
			}

			const postRow = btn.closest('tr')
			const postLink = postRow.querySelector('td.column-title a')
			const postURL = postLink.href
			const postPath = postURL.replace(location.origin, '').replace(/^\/|\/$/g, '')

			fetch(`/wp-content/narrations/${postPath}/index.json`)
			.then(response => {
				if (response.ok) {
					return response.json()
				}
				return false
			})
			.then(data => {
				const totalTracks = data.audio.total
				const tracksGenerated = data.audio.tracks.length

				if (tracksGenerated < totalTracks) {
					updateBtnText(postID, `Generating ${tracksGenerated}/${totalTracks}...`)
				} else {
					updateBtnText(postID, 'Generated!')
					clearInterval(checkProgress)
				}
			})
			.catch(error => {
				console.error('Error checking audio generation progress', error)
				if (attempts > 10) {
					updateBtnText(postID, 'Generation error')
					clearInterval(checkProgress)
				}
			}
		)
		}, 3000)
	}

	function updateBtnText(postID, text) {
		const btn = document.querySelector(`.generate-narration[data-post-id="${postID}"]`)
		if (btn) {
			btn.innerHTML = text
		}
	}

	document.querySelectorAll('.generate-narration').forEach(button => {
		button.addEventListener('click', function () {
			generateNarration([this.dataset.postId]);
		});
	});

	document.querySelectorAll('.delete-narration').forEach(button => {
		button.addEventListener('click', function () {
			deleteNarration([this.dataset.postId]);
		});
	});

	document.getElementById('bulk-generate').addEventListener('click', function () {
		const selectedPosts = Array.from(document.querySelectorAll('input[name="post[]"]:checked'))
			.map(checkbox => checkbox.value);

		if (selectedPosts.length > 0) {
			generateNarration(selectedPosts);
		}
	});

	document.getElementById('bulk-delete').addEventListener('click', function () {
		const selectedPosts = Array.from(document.querySelectorAll('input[name="post[]"]:checked'))
			.map(checkbox => checkbox.value);

		if (selectedPosts.length > 0) {
			deleteNarration(selectedPosts);
		}
	});
});