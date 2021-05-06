jQuery(document).ready(function($){
	
	let lightbox = function(ops) {

		if (ops == 'close') {
			$('body').css({overflow: ''});
			return $('#lumise-lightbox').remove();
		}
		
		var tmpl = '<div id="lumise-lightbox" class="lumise-lightbox" style="display:block">\
						<div id="lumise-lightbox-body">\
							<div id="lumise-lightbox-content" class="%class%" style="min-width:%width%px">\
								%content%\
							</div>\
							%footer%\
							<a class="kalb-close" href="#close" title="Close">\
								<i class="dashicons dashicons-no-alt"></i>\
							</a>\
						</div>\
						<div class="kalb-overlay"></div>\
					</div>',
			cfg = $.extend({
				width: 1000,
				class: '',
				footer: '',
				content: '',
				onload: function(){},
				onclose: function(){}
			}, ops);

		if (cfg.footer !== '')
			cfg.footer = '<div id="lumise-lightbox-footer">'+cfg.footer+'</div>';

		tmpl = $(tmpl.replace(/\%width\%/g, cfg.width).
					replace(/\%class\%/g, cfg.class).
					replace(/\%content\%/g, cfg.content).
					replace(/\%footer\%/g, cfg.footer));

		$('.lumise-lightbox').remove();
		$('body').append(tmpl).css({overflow: 'hidden'});

		cfg.onload(tmpl);
		tmpl.find('a.kalb-close,div.kalb-overlay').on('click', function(e){
			cfg.onclose(tmpl);
			$('.lumise-lightbox').remove();
			$('body').css({overflow: ''});
			e.preventDefault();
		});

	};

	
	/*
	* Show Lumise configuration in variations
	*/
	
	$(document).on('click', (e) => {
		if (
			e.target.getAttribute('data-lumise-frame') || 
			e.target.parentNode.getAttribute('data-lumise-frame')
		) {
			
			let el = e.target.parentNode.getAttribute('data-lumise-frame') ? e.target.parentNode : e.target,
				fn = el.getAttribute('data-lumise-frame'),
				src = fn+'&nonce=LUMISE-SECURITY-BACKEND:'+lumisejs.nonce_backend,
				id = el.parentNode.getAttribute('data-id'),
				inp = window['variable-lumise-'+id],
				val = inp.value;
			
			
			if (fn == 'paste') {
				
				e.preventDefault();
				
				if (!localStorage.getItem('LUMISE-VARIATION-COPY'))
					return alert('Error, You must copy one config before pasting');
					
				$(inp).val(localStorage.getItem('LUMISE-VARIATION-COPY')).change();
				$('button#lumise-config-'+id).click().parent().attr('data-empty', 'false');
				
				return;	
				
			} else if (fn == 'clear') {
				
				e.preventDefault();
				
				if (confirm('Are you sure that you want to clear this config?')) {
					$(inp).val('').change();
					$(el).parent().attr('data-empty', 'true');
				};
				
				return;	
				
			} else if (fn == 'list') {
				
				e.preventDefault();
				
				load_product_bases(
					{
						'product_source': 'woo-variation'
					}, 
					{
						'can_create_new': false,
						'action_text': 'Select this config',
						'action_fn': (product) => {
							$(inp).val(product.lumise_data).change();
							$('button#lumise-config-'+id).click().parent().attr('data-empty', 'false');
						}
					}
				);
				
				return;	
				
			};
			
			$(el).before(
				'<iframe id="lumise-variation-'+id+'" name="lumise-variation-'+id+'" style="width: 100%;min-height:150px;border: none;" src="'+
					(val === '' ? src : '')+
				'"></iframe>'
			).closest('div.variable_lumise_data').attr('data-loading', 'Loading..').addClass('hasFrame');;
			
			if (val !== '') {
				
				let form = $('<form action="'+src+'" method="post" target="lumise-variation-'+id+'"><textarea name="data">'+val.replace(/\<textarea/g, '&lt;textarea').replace(/\<\/textarea\>/g, '&lt;/textarea&gt;')+'</textarea></form>');	
				
				$('body').append(form);
				
			    form.submit().remove();	
				
			}
			
			e.preventDefault();
		}
	});
	
});