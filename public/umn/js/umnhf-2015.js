(function() {
	function onReady(fn) {
	  	if(document.readyState == 'complete' || document.readyState == 'loaded') {
	    	fn();
	  	} else {
	    	document.addEventListener('DOMContentLoaded', fn);
	  	}
	};

	function slideUp(el, ms) {
		var endHeight = el.offsetHeight;
		var style = getComputedStyle(el);
	  	var mTop = parseInt(style.marginTop);
	  	var mBottom = parseInt(style.marginBottom);
	  	var pTop = parseInt(style.paddingTop);
	  	var pBottom = parseInt(style.paddingBottom);
	  	var cHeight = endHeight - mTop - mBottom - pTop - pBottom;
	  	el.style.overflowY = 'hidden';
	  	el.style.display = 'block';
	  	endHeight += mTop + mBottom;
	  	var height = endHeight;
	  	var t = 16;
	  	tween(endHeight * t / ms, false);
	  	function handleTween(style, n, amount) {
	  		var c = parseInt(getComputedStyle(el)[style]);
	  		if(c - n < 0) {
	  			el.style[style] = '0px';
	  			height -= n;
	  			var r = n - c;
	  			tween(amount, r);
	  		} else {
	  			el.style[style] = (c - n) + 'px';
	  			height -= n;
	  			setTimeout(function() { tween(amount, false) }, t);
	  		}
	  	};
	  	function tween(amount, remainder) {
	  		var n = (remainder === false ? amount : remainder);
	  		if(mBottom != 0 && pBottom + cHeight + pTop + mTop < height && height <= mBottom + pBottom + cHeight + pTop + mTop) {
	  			handleTween('marginBottom', n, amount);
	  		} else if(pBottom != 0 && cHeight + pTop + mTop < height && height <= pBottom + cHeight + pTop + mTop) {
	  			handleTween('paddingBottom', n, amount);
	  		} else if(cHeight != 0 && pTop + mTop < height && height <= cHeight + pTop + mTop) {
	  			handleTween('height', n, amount);
	  		} else if(pTop != 0 && mTop < height && height <= pTop + mTop) {
	  			handleTween('paddingTop', n, amount);
	  		} else if(mTop != 0 && 0 < height && height <= mTop) {
	  			handleTween('marginTop', n, amount);
	  		} else if(height <= 0) {
	  			el.style.overflowY = '';
	  			el.style.marginTop = '';
	  			el.style.marginBottom = '';
	  			el.style.paddingTop = '';
	  			el.style.paddingBottom = '';
	  			el.style.height = '';
	  			el.style.display = '';
	  		}
	  	};
	};

	function slideDown(el, ms) {
		var height = 0;
		el.style.display = 'block';
		var endHeight = el.offsetHeight;
		var style = getComputedStyle(el);
	  	var mTop = parseInt(style.marginTop);
	  	var mBottom = parseInt(style.marginBottom);
	  	var pTop = parseInt(style.paddingTop);
	  	var pBottom = parseInt(style.paddingBottom);
	  	var cHeight = endHeight - mTop - mBottom - pTop - pBottom;
	  	endHeight += mTop + mBottom;
	  	el.style.overflowY = 'hidden';
	  	el.style.marginTop = '0px';
	  	el.style.marginBottom = '0px';
	  	el.style.paddingTop = '0px';
	  	el.style.paddingBottom = '0px';
	  	el.style.height = '0px';
	  	var t = 16;
	  	tween(endHeight * t / ms, false);
	  	function handleTween(style, n, v, amount) {
	  		var c = parseInt(getComputedStyle(el)[style]);
	  		if(c + n > v) {
	  			el.style[style] = v + 'px';
	  			height += n;
	  			var r = c + n - v;
	  			tween(amount, r);
	  		} else {
	  			el.style[style] = (c + n) + 'px';
	  			height += n;
	  			setTimeout(function() { tween(amount, false) }, t);
	  		}
	  	};
	  	function tween(amount, remainder) {
				var n = (remainder === false ? amount : remainder);
	  		if(mTop != 0 && 0 <= height && height < mTop) {
	  			handleTween('marginTop', n, mTop, amount);
	  		} else if(pTop != 0 && mTop <= height && height < pTop + mTop) {
	  			handleTween('paddingTop', n, pTop, amount);
	  		} else if(cHeight != 0 && pTop + mTop <= height && height < pTop + mTop + cHeight) {
	  			handleTween('height', n, cHeight, amount);
	  		} else if(pBottom != 0 && pTop + mTop + cHeight <= height && height < pTop + mTop + cHeight + pBottom) {
	  			handleTween('paddingBottom', n, pBottom, amount);
	  		} else if(mBottom != 0 && pTop + mTop + cHeight + pBottom <= height && height < pTop + mTop + cHeight + pBottom + mBottom) {
	  			handleTween('marginBottom', n, mBottom, amount);
	  		} else if(pTop + mTop + cHeight + pBottom + mBottom <= height) {
			  	el.style.overflowY = '';
			  	el.style.marginTop = '';
			  	el.style.marginBottom = '';
			  	el.style.paddingTop = '';
			  	el.style.paddingBottom = '';
			  	el.style.height = '';
	  		}
	  	};
	};

	function slideToggle(el, ms) {
		if(getComputedStyle(el)['display'] == 'none') {
			slideDown(el, ms);
		} else {
			slideUp(el, ms);
		}
	};

	function toggleClass(el, class1, class2) {
		if(el.classList) {
			if(el.classList.contains(class1)) {
				el.classList.remove(class1);
				el.classList.add(class2);
			} else {
				el.classList.remove(class2);
				el.classList.add(class1);
			}
		} else {
			if(new RegExp('(^| )' + class1 + '( |$)', 'gi').test(el.className)) {
				el.className = el.className.replace(new RegExp('(^|\\b)' + class1 + '(\\b|$)', 'gi'), ' ');
				el.className += ' ' + class2;
			} else {
				el.className = el.className.replace(new RegExp('(^|\\b)' + class2 + '(\\b|$)', 'gi'), ' ');
				el.className += ' ' + class1;
			}
		}
	};

	function headerClick() {
		var time = 300;
		menu = document.getElementById('umnhf-alt-n');
		slideToggle(menu, time);
	}

	function footerClick() {
		var time = 300;

		if(getComputedStyle(this)['cursor'] == 'pointer') {
			var links = this.nextElementSibling;
			toggleClass(this, 'umnhf-f-title-opened', 'umnhf-f-title-closed');
			slideToggle(links, time);
			var opened = document.querySelectorAll('.umnhf-f-cl > .umnhf-f-title.umnhf-f-title-opened');
			if(opened.length > 1) {
				for(var i = 0; i < opened.length; i++) {
					if(opened[i] !== this) {
						var other = opened[i];
						var otherLinks = other.nextElementSibling;
						toggleClass(other, 'umnhf-f-title-opened', 'umnhf-f-title-closed');
						slideUp(otherLinks, time);
					}
				}
			}
		}

	};

	onReady(function() {
		var UMN_UTIL = {},
			mobileSearch = false;
		UMN_UTIL.hasClass = function (ele,cls) {
			return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
		};
		UMN_UTIL.addClass = function (ele,cls) {
			if (!UMN_UTIL.hasClass(ele,cls)) ele.className += " "+cls;
		};
		UMN_UTIL.removeClass = function (ele,cls) {
			if (UMN_UTIL.hasClass(ele,cls)) {
				var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
				ele.className=ele.className.replace(reg,' ');
			}
		};
		var umnhf = document.getElementById('umnhf-h'),
			searchForm = document.getElementById('umnhf-h-search'),
			searchButton = document.getElementById('umnhf-m-search');
		if (umnhf && searchButton) {
			//searchButton.setAttribute("href", "#");
			searchButton.onclick = function (event) {
				if (!mobileSearch) {
					UMN_UTIL.addClass(searchForm, 'mobile');
					UMN_UTIL.addClass(searchButton, 'mobile');
					searchButton.innerHTML = '<span class="umnhf-m-cancel">&#x2715;</span> Hide';
					mobileSearch = true;
				} else {
					UMN_UTIL.removeClass(searchForm, 'mobile');
					UMN_UTIL.removeClass(searchButton, 'mobile');
					searchButton.innerHTML = 'Search';
					mobileSearch = false;
				}
				event.preventDefault();
				event.returnValue = false;
				return false;
			};
		}

		// Slide open/close footer links
		var titles = document.querySelectorAll('#umnhf-f .umnhf-f-cl > .umnhf-f-title');
		for(var i = 0; i < titles.length; i++) {
			titles[i].addEventListener('click', footerClick);
		}

		// Add event listener to alternate header.
		var mlink = document.getElementById('umnhf-alt-um');
		if (typeof mlink !== "undefined" && mlink !== null && mlink.value !== '') {
			mlink.addEventListener('click', headerClick);
		}

		// Set the current year as copyright date.
		document.getElementById('cdate').innerHTML = new Date().getFullYear();
	});
})();