$(function() {
	"use strict";
	new PerfectScrollbar(".app-container"),
	new PerfectScrollbar(".header-message-list"),
	new PerfectScrollbar(".header-notifications-list"),


	    $(".mobile-search-icon").on("click", function() {
			$(".search-bar").addClass("full-search-bar")
		}),

		$(".search-close").on("click", function() {
			$(".search-bar").removeClass("full-search-bar")
		}),

		$(".mobile-toggle-menu").on("click", function() {
			$(".wrapper").addClass("toggled")
		}),
		



		$(".dark-mode").on("click", function() {

			if($(".dark-mode-icon i").attr("class") == 'bx bx-sun') {
				//localStorage.setItem('cor', 'sun');
				$(".dark-mode-icon i").attr("class", "bx bx-moon");
				$("html").attr("class", "light-theme")
			} else {
				//localStorage.setItem('cor', 'moon');
				$(".dark-mode-icon i").attr("class", "bx bx-sun");
				
				$("html").attr("class", "dark-theme")
			}

		}), 

		
		$(".toggle-icon").click(function() {
			$(".wrapper").hasClass("toggled") ? ($(".wrapper").removeClass("toggled"), $(".sidebar-wrapper").unbind("hover")) : ($(".wrapper").addClass("toggled"), $(".sidebar-wrapper").hover(function() {
				$(".wrapper").addClass("sidebar-hovered")
			}, function() {
				$(".wrapper").removeClass("sidebar-hovered")
			}))
		}),
		$(document).ready(function() {
			$(window).on("scroll", function() {
				$(this).scrollTop() > 300 ? $(".back-to-top").fadeIn() : $(".back-to-top").fadeOut()
			}), $(".back-to-top").on("click", function() {
				return $("html, body").animate({
					scrollTop: 0
				}, 600), !1
			});

			// let cor = localStorage.getItem('cor');
			// if (cor === "moon"){
			// 	$("html").attr("class", "dark-theme");
			// } else {
			// 	("html").attr("class", "light-theme");
			// }
		}),
		
		$(function() {
			for (var e = window.location, o = $(".metismenu li a").filter(function() {
					return this.href == e
				}).addClass("").parent().addClass("mm-active"); o.is("li");) o = o.parent("").addClass("mm-show").parent("").addClass("mm-active")
		}),
		
		
		$(function() {
			$("#menu").metisMenu()
		}), 
		
		$(".chat-toggle-btn").on("click", function() {
			$(".chat-wrapper").toggleClass("chat-toggled")
		}), $(".chat-toggle-btn-mobile").on("click", function() {
			$(".chat-wrapper").removeClass("chat-toggled")
		}),


		$(".email-toggle-btn").on("click", function() {
			$(".email-wrapper").toggleClass("email-toggled")
		}), $(".email-toggle-btn-mobile").on("click", function() {
			$(".email-wrapper").removeClass("email-toggled")
		}), $(".compose-mail-btn").on("click", function() {
			$(".compose-mail-popup").show()
		}), $(".compose-mail-close").on("click", function() {
			$(".compose-mail-popup").hide()
		}), 
		
		
		$(".switcher-btn").on("click", function() {
			$(".switcher-wrapper").toggleClass("switcher-toggled")
		}), $(".close-switcher").on("click", function() {
			$(".switcher-wrapper").removeClass("switcher-toggled")
		}), $("#lightmode").on("click", function() {
			$("html").attr("class", "light-theme")
		}), $("#darkmode").on("click", function() {
			$("html").attr("class", "dark-theme")
		}), $("#semidark").on("click", function() {
			$("html").attr("class", "semi-dark")
		}), $("#minimaltheme").on("click", function() {
			$("html").attr("class", "minimal-theme")
		}), $("#headercolor1").on("click", function() {
			$("html").addClass("color-header headercolor1"), $("html").removeClass("headercolor2 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor2").on("click", function() {
			$("html").addClass("color-header headercolor2"), $("html").removeClass("headercolor1 headercolor3 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor3").on("click", function() {
			$("html").addClass("color-header headercolor3"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor4").on("click", function() {
			$("html").addClass("color-header headercolor4"), $("html").removeClass("headercolor1 headercolor2 headercolor3 headercolor5 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor5").on("click", function() {
			$("html").addClass("color-header headercolor5"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor3 headercolor6 headercolor7 headercolor8")
		}), $("#headercolor6").on("click", function() {
			$("html").addClass("color-header headercolor6"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor3 headercolor7 headercolor8")
		}), $("#headercolor7").on("click", function() {
			$("html").addClass("color-header headercolor7"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor3 headercolor8")
		}), $("#headercolor8").on("click", function() {
			$("html").addClass("color-header headercolor8"), $("html").removeClass("headercolor1 headercolor2 headercolor4 headercolor5 headercolor6 headercolor7 headercolor3")
		})
		
	// sidebar colors 
	$('#sidebarcolor1').click(theme1);
	$('#sidebarcolor2').click(theme2);
	$('#sidebarcolor3').click(theme3);
	$('#sidebarcolor4').click(theme4);
	$('#sidebarcolor5').click(theme5);
	$('#sidebarcolor6').click(theme6);
	$('#sidebarcolor7').click(theme7);
	$('#sidebarcolor8').click(theme8);

	function theme1() {
		$('html').attr('class', 'color-sidebar sidebarcolor1');
	}

	function theme2() {
		$('html').attr('class', 'color-sidebar sidebarcolor2');
	}

	function theme3() {
		$('html').attr('class', 'color-sidebar sidebarcolor3');
	}

	function theme4() {
		$('html').attr('class', 'color-sidebar sidebarcolor4');
	}

	function theme5() {
		$('html').attr('class', 'color-sidebar sidebarcolor5');
	}

	function theme6() {
		$('html').attr('class', 'color-sidebar sidebarcolor6');
	}

	function theme7() {
		$('html').attr('class', 'color-sidebar sidebarcolor7');
	}

	function theme8() {
		$('html').attr('class', 'color-sidebar sidebarcolor8');
	}
	
	function formatarTelefone(valor) {
		valor = valor.replace(/\D/g, ''); // Remove tudo que não é dígito

		// DDD
		let ddd = valor.substring(0,2);

		// Número principal
		let numeroPrincipal = valor.substring(2);

		// Se não tem DDD, retorna só o que tem
		if (!ddd) return valor;

		// Decide formato: fixo (8 dígitos) ou celular (9 dígitos)
		let telefoneFormatado = '';
		if (numeroPrincipal.length > 0) {
			telefoneFormatado = '(' + ddd + ') ';
			if (numeroPrincipal.length > 4) {
				// Se celular
				if (numeroPrincipal.length >= 9) {
					telefoneFormatado += numeroPrincipal.substring(0,5) + '-' + numeroPrincipal.substring(5,9);
				} else {
					telefoneFormatado += numeroPrincipal.substring(0,4) + '-' + numeroPrincipal.substring(4);
				}
			} else {
				telefoneFormatado += numeroPrincipal;
			}
		} else {
			// Só DDD
			telefoneFormatado = '(' + ddd;
		}

		// Limita sempre a 15 caracteres
		return telefoneFormatado.substring(0,15);
	}

	document.querySelectorAll('input[data-type="telefone"]').forEach(function(input) {
		input.addEventListener('input', function(e) {
			let valorDigitado = e.target.value;
			let novoValor = formatarTelefone(valorDigitado);
			e.target.value = novoValor;
		});
	});	

	function formatarCPF(valor) {
	// Mantém só dígitos e limita a 11
	valor = valor.replace(/\D/g, '').substring(0, 11);

	const p1 = valor.substring(0, 3);
	const p2 = valor.substring(3, 6);
	const p3 = valor.substring(6, 9);
	const p4 = valor.substring(9, 11);

	let cpf = p1;
	if (p2) cpf += '.' + p2;
	if (p3) cpf += '.' + p3;
	if (p4) cpf += '-' + p4;

	return cpf;
	}

	document.querySelectorAll('input[data-type="cpf"]').forEach(function(input) {
	input.addEventListener('input', function(e) {
		e.target.value = formatarCPF(e.target.value);
	});
	});	

	function formatCpfCnpjFromDigits(digs) {
		if (digs.length <= 11) {
			// CPF: 000.000.000-00
			return digs
			.replace(/^(\d{3})(\d)/, '$1.$2')
			.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
			.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
		} else {
			// CNPJ: 00.000.000/0000-00
			return digs
			.replace(/^(\d{2})(\d)/, '$1.$2')
			.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
			.replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4')
			.replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5');
		}
		}

		function aplicarMascaraCpfCnpj(input) {
		const raw = input.value;
		const cursor = input.selectionStart || 0;

		// Quantos dígitos havia antes do cursor
		let digitsBeforeCursor = raw.slice(0, cursor).replace(/\D/g, '').length;

		// Mantém só dígitos e limita a 14 (CNPJ)
		let digits = raw.replace(/\D/g, '').slice(0, 14);

		// Garante coerência
		if (digitsBeforeCursor > digits.length) digitsBeforeCursor = digits.length;

		const masked = formatCpfCnpjFromDigits(digits);
		input.value = masked;

		// Sempre permitir até o tamanho do CNPJ com máscara
		input.maxLength = 18;

		// Reposiciona o cursor após o mesmo N° de dígitos
		let count = 0, newPos = 0;
		if (digitsBeforeCursor === 0) {
			newPos = 0;
		} else {
			for (; newPos < masked.length; newPos++) {
			if (/\d/.test(masked[newPos])) count++;
			if (count >= digitsBeforeCursor) { newPos++; break; }
			}
			if (newPos > masked.length) newPos = masked.length;
		}
		input.setSelectionRange(newPos, newPos);
	}

	document.querySelectorAll('input[data-type="cpfcnpj"]').forEach((input) => {
		input.maxLength = 18; // nunca trave em 14
		input.addEventListener('input', () => aplicarMascaraCpfCnpj(input));
		input.addEventListener('paste', () => {
			// formata logo após colar
			setTimeout(() => aplicarMascaraCpfCnpj(input), 0);
		});
	});
});