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

	async function generateNarration(postIds) {
		try {
			const response = await fetch(ajaxurl, {
				...fetchConfig,
				body: serializeData({
					action: 'generate_narration',
					post_ids: postIds,
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
			console.error('Error generating narration:', error);
			alert('Error generating narration: ' + error.message);
		}
	}

	async function deleteNarration(postIds) {
		try {
			const response = await fetch(ajaxurl, {
				...fetchConfig,
				body: serializeData({
					action: 'delete_narration',
					post_ids: postIds,
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