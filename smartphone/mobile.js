var iOS = (navigator.userAgent.match(/(iPad|iPhone|iPod)/i) ? true : false);

window.addEventListener('DOMContentLoaded', function () {

	if (window.pageYOffset < 1) {
		window.scrollTo(0, 1);
	}
	

	if (supports_html5_storage() && localStorage.getItem('last_page_url') && localStorage.getItem('last_page_date')) {

		var date_parsed = Date.parse(localStorage.getItem('last_page_date'));

		if (((new Date()) - date_parsed) < 1000 * 60 * 60 * 5 && document.getElementById('boardIndexNavigator')) {

			var el = document.createElement('a');
			el.href = localStorage.getItem('last_page_url');
			el.innerHTML = "Terug naar laatst bekeken topic";
			el.className = "sticky";

			document.getElementById('boardIndexNavigator').appendChild(el);

		}

	}
	
	if (supports_html5_storage() && localStorage.getItem("fontSize")){
	
		var a = localStorage.getItem("fontSize");
		
		document.body.style.fontSize = a + "px";
		
		console.log(document.body.style.fontSize);
	
	}

	if (supports_html5_storage() && document.getElementById('settings')) {

		var settingsEl = document.getElementById('settings');
		settingsEl.style.display = 'block';

		/* instantImages */
			var a = localStorage.getItem("instantImages");
					
			var el = document.createElement('a');
			el.href = "javascript:void(-1);"
			el.innerHTML = "Afbeeldingen automatisch laden";
			el.className = ((a !== "true") ? "disabled" : "");
	
			el.onclick = function () {
	
				var a = localStorage.getItem("instantImages");
				localStorage.setItem("instantImages", ((a === "true") ? "false" : "true"));
	
				this.className = ((a === "true") ? "disabled" : "");
	
			};

			document.getElementById('settingsContainer').appendChild(el);
			
		/* askInsideURL */
		
			var b = localStorage.getItem("askInsideURL");
		
			var el = document.createElement('a');
			el.href = "javascript:void(-1);"
			el.innerHTML = "Alle links automatisch omzetten";
			el.className = ((b !== "true") ? "disabled" : "");
	
			el.onclick = function () {
	
				var b = localStorage.getItem("askInsideURL");
				localStorage.setItem("askInsideURL", ((b === "true") ? "false" : "true"));
	
				this.className = ((b === "true") ? "disabled" : "");
	
			};

			document.getElementById('settingsContainer').appendChild(el);
			
		/* askInsideURL */
			
			var c = localStorage.getItem("fontSize");
			
			if(!c){
			
				localStorage.setItem("fontSize", 14);
			
			}
			
			
			// first
			var el_1 = document.createElement('a');
			el_1.href = "javascript:void(-1);"
			el_1.innerHTML = "Grotere letters";
	
			el_1.onclick = function () {
	
				var c = parseInt(localStorage.getItem("fontSize"));
				
				if(c + 1 <= 20){
				
					localStorage.setItem("fontSize", c + 1);

					document.body.style.fontSize = (c + 1) + "px";
					
				}
	
			};

			document.getElementById('settingsContainer').appendChild(el_1);
			
			// first
			var el_2 = document.createElement('a');
			el_2.href = "javascript:void(-1);"
			el_2.innerHTML = "Kleinere letters";
	
			el_2.onclick = function () {
	
				var c = parseInt(localStorage.getItem("fontSize"));
				
				if(c - 1 >= 6){
				
					localStorage.setItem("fontSize", c - 1);
	
					document.body.style.fontSize = (c - 1) + "px";
					
				}
	
			};

			document.getElementById('settingsContainer').appendChild(el_2);

	}

	if (supports_html5_storage()) {

		var a = document.createElement('a');
		a.href = (window.location + "");


		if (a.search.match(/\?topic=[0-9]+\.(.+);smartphone/)) {

			localStorage.setItem("last_page_date", new Date());
			localStorage.setItem("last_page_url", (window.location + ""));

		} else {

			localStorage.setItem("last_page_date", false);
			localStorage.setItem("last_page_url", false);

		}


		// instantImages
		var b = localStorage.getItem("instantImages"),
			c = document.getElementsByTagName("a");

		if (b === "true") {

			for (i in c) {
			
				if (typeof c[i] != "undefined" && typeof c[i].className != "undefined") {

					if (c[i].className.match(/imageLoader/g)) {

						loadImage(c[i]);

					}

				}

			}

		}

	}

	if (iOS) {

		var a = document.getElementsByTagName("a");
		for (var i = 0; i < a.length; i++) {

			if (a[i].host == "www.gmot.nl") {

				a[i].onclick = function () {
				
					if(!this.search.match(/smartphone/)){
					
						if(localStorage.getItem("askInsideURL") == "true" || confirm('Deze link gaat wel naar GMOT.nl maar niet naar de smartphone versie.\n\nWil je deze link in de smartphone versie openen?')){
						
							this.href = "http://www.gmot.nl" + this.pathname + this.search + ((this.search) ? ';' : '?') + 'smartphone' + this.hash;
						
						}else{
						
							return true;
						
						}
					
					}

					var evalCode = this.getAttribute("data-onclick");

					if (evalCode) {

						var result = eval(evalCode);

						if (!result) {

							return false;

						}

					}

					window.location = this.getAttribute("href");
					return false
				}

			}

		}

	}

}, false);

function loadImage(img) {

	var image = document.createElement('img');
	image.setAttribute('src', img.getAttribute('data-src'));
	image.setAttribute('alt', img.getAttribute('data-alt'));
	image.setAttribute('border', img.getAttribute('data-border'));

	if (img.getAttribute('data-width'))
		image.setAttribute('width', img.getAttribute('data-width'));

	img.parentNode.replaceChild(image, img);

}

function supports_html5_storage() {

	try {
		return 'localStorage' in window && window['localStorage'] !== null;

	} catch (e) {

		return false;

	}

}

var reloader = function () {

	document.getElementById('reloader').innerHTML = 'Herladen...';

	return true;

}