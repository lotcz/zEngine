/**
 * This file will search page for all embedded Youtube videos and fit them to correct page width
 */
window.addEventListener(
	'load',
	() => {
		const iframes = document.querySelectorAll('iframe');
		iframes.forEach(
			(iframe) => {
				const src = iframe.getAttribute('src');
				if (src === null || !src.startsWith('https://www.youtube.com/embed')) return;
				const oldParent = iframe.parentElement;
				const newParent = document.createElement('div');
				newParent.style.position = 'relative';
				newParent.style.width = '100%';
				newParent.style.paddingBottom = '56.25%';
				newParent.style.height = '0';
				newParent.style.overflow = 'hidden';

				if (oldParent.tagName.toLowerCase() === 'a') {
					oldParent.replaceWith(newParent);
				} else {
					iframe.replaceWith(newParent);
				}

				iframe.style.position = 'absolute';
				iframe.style.top = '0';
				iframe.style.left = '0';
				iframe.style.width = '100%';
				iframe.style.height = '100%';
				iframe.style.border = '0';
				iframe.setAttribute('width', '100%');
				iframe.setAttribute('height', '100%');
				newParent.appendChild(iframe);
			}
		);
	}
);
