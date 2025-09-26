document.addEventListener('DOMContentLoaded', function () {

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
		if (!window.sup) {
			window.sup = {}
		}
		postIDs.forEach(postID => getStatusUpdates(postID))

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

	function getStatusUpdates(postID) {
		const btn = document.querySelector(`.generate-narration[data-post-id="${postID}"]`)
		const postRow = btn.closest('tr')
		const postLink = postRow.querySelector('td.column-title a')
		const postURL = postLink.href
		const postPath = postURL.replace(location.origin, '').replace(/^\/|\/$/g, '')

		const maxTime = 10 * 60 * 1000 // 10 min
		let attempts = 0

		btn.innerHTML = 'Generating...'

		sup[postID] = setInterval(() => {
			attempts++
			if (attempts * 3000 > maxTime) {
				stopChecking(postID, 'Generation timed out')
			}

			fetch(`/wp-content/narrations/${postPath}/index.json`)
			.then(response => {
				if (response.ok) {
					return response.json()
				}
				return false
			})
			.then(data => {
				if (data.audio) {
					const totalTracks = data.audio.total
					const tracksGenerated = data.audio.tracks.length
	
					if (tracksGenerated < totalTracks) {
						updateBtnText(postID, `Generating ${tracksGenerated}/${totalTracks}...`)
					} else {
						stopChecking(postID, 'Generated!')
					}
				}
			})
			.catch(error => {
				console.error('Error checking audio generation progress', error)
				if (attempts > 10) {
					stopChecking(postID, 'Generation error')
				}
			}
		)
		}, 3000)
	}

	function stopChecking(postID, text) {
		updateBtnText(postID, text)
		clearInterval(sup[postID])
		sup[postID] = false
	}

	function updateBtnText(postID, text) {
		const btn = document.querySelector(`.generate-narration[data-post-id="${postID}"]`)
		if (btn) {
			btn.innerHTML = text
		}
	}

	function getQueryParam(name){
		if (typeof URLSearchParams !== 'undefined') {
			const params = new URLSearchParams(window.location.search)
			return params.get(name)
		} else {
			const query = window.location.search.substring(1);
			const pairs = query.split('&')
			for (let i = 0; i < pairs.length; i++) {
				const [key, value] = pairs[i].split('=')
				if (key === name) {
					return value ? decodeURIComponent(value.replace(/\+/g, ' ')) : ''
				}
			}
			return null
		}
	}

	function manageServiceVendor () {
		const select = document.getElementById('ai_narration_service_vendor') || false
		if (!select) {
			return
		}
		function checkIfNone() {
			const el_api_key = document.getElementById('ai_narration_service_api_key').closest('tr')
			const el_api_voice = document.getElementById('ai_narration_voice').closest('tr')
			if (select.value === 'none') {
				console.log('Vendor is None');
				el_api_key.style.display = el_api_voice.style.display = 'none'
			} else {
				el_api_key.style.display = el_api_voice.style.display = 'table-row'
			}
		}
		checkIfNone()
		select.addEventListener('change', checkIfNone)
	}

	/* Add Events per Page */

	function addEventsPageSettings() {
		manageServiceVendor()
	}

	function addEventsPageNarrations() {
		document.querySelectorAll('.generate-narration').forEach(button => {
			button.addEventListener('click', function () {
				generateNarration([this.dataset.postId])
			});
		});

		document.querySelectorAll('.delete-narration').forEach(button => {
			button.addEventListener('click', function () {
				deleteNarration([this.dataset.postId])
			});
		});

		document.getElementById('bulk-generate').addEventListener('click', function () {
			const selectedPosts = Array.from(document.querySelectorAll('input[name="post[]"]:checked'))
				.map(checkbox => checkbox.value)

			if (selectedPosts.length > 0) {
				generateNarration(selectedPosts)
			}
		})

		document.getElementById('bulk-delete').addEventListener('click', function () {
			const selectedPosts = Array.from(document.querySelectorAll('input[name="post[]"]:checked'))
				.map(checkbox => checkbox.value)

			if (selectedPosts.length > 0) {
				deleteNarration(selectedPosts)
			}
		});
	}

	function init() {
		const page_id = getQueryParam('page')
		switch (page_id) {
			case 'ai-narration-settings':
				addEventsPageSettings()
				break
			case 'ai-narration-narrations':
				addEventsPageNarrations()
				break
			default:
				console.log("no match")
		}
	}

	init();
});
