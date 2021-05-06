(function($) {
	
	window.URL = window.URL || window.webkitURL;
	
	window.lumise_create_thumbn = function(ops) {
				
		var img = new Image();
    		img.onload = function(){
	    		
	    		let cv = window.creatThumbnCanvas ? 
	    				 window.creatThumbnCanvas : 
	    				 window.creatThumbnCanvas = document.createElement('canvas'),
					ctx = cv.getContext('2d'),
					w = (ops.width ? ops.width : (ops.height*(this.naturalWidth/this.naturalHeight))),
					h = (ops.height ? ops.height : (ops.width*(this.naturalHeight/this.naturalWidth))),
					type = this.src.indexOf('image/jpeg') > -1 ? 'jpeg' : 'png';
					
	    		_w = this.naturalHeight*(w/this.naturalWidth) >= h ? 
	    				w : this.naturalWidth*(h/this.naturalHeight);
	    		_h = (w == _w) ? this.naturalHeight*(w/this.naturalWidth) : h;
	    		
	    		
	    		cv.width = this.width;
	    		cv.height = this.height;
	    		
	    		if (type == 'jpeg') {
	    			ctx.fillStyle = ops.background? ops.background : '#eee';
					ctx.fillRect(0, 0, cv.width, cv.height);
				};
				
	    		ctx.drawImage(this, 0, 0);
	    		
	    		HERMITE.resample_single(cv, _w, _h, true);
	    		
	    		ops.callback(cv.toDataURL('image/'+type, 100), this);
	    	
	    		delete ctx;
	    		delete cv;
	    		delete img;
	    		
    		}
    		
    	img.src = ops.source;
			
	};
	
	window.lumise_validate_file = function(file) {
		
		if (['image/png', 'image/jpeg', 'image/svg+xml'].indexOf(file.type) === -1)
			return false;
		if (file.size > 25485760)
			return false;
		
		return true;
		
	}
	
	window.build_lumi = function(thumbn, img) {
		
		var w = 200,
			h = 200*(img.naturalHeight/img.naturalWidth),
			time = new Date().getTime(),
			data = {"stages":{"lumise":{"data":{"objects":[null,null,{"type":"image","originX":"center","originY":"center","left":w/2,"top":h/2,"width":w,"height":h,"fill":"rgb(0,0,0)","stroke":"","strokeWidth":0,"strokeLineCap":"butt","strokeLineJoin":"miter","strokeMiterLimit":10,"scaleX":1,"scaleY":1,"angle":0,"flipX":false,"flipY":false,"opacity":1,"visible":true,"backgroundColor":"","fillRule":"nonzero","globalCompositeOperation":"source-over","skewX":0,"skewY":0,"crossOrigin":"","alignX":"none","alignY":"none","meetOrSlice":"meet","src":img.src,"evented":true,"selectable":true,"filters":[],"resizeFilters":[]}],"background":"#ebeced","devicePixelRatio":2,"product_color":"#00ff7f","limit_zone":{"width":207.69375,"height":332.31,"top":0,"left":0},"edit_zone":{"height":h,"width":w,"left":0,"top":0,"radius":"0"},"product_width":500,"product_height":500,"screenshot":thumbn},"screenshot":thumbn,"edit_zone":{"height":h,"width":w,"left":0,"top":0,"radius":"0"},"updated":time,"padding":[0,0]}},"updated":time}
		
		return 'data:application/octet-stream;base64,'+btoa(encodeURIComponent(JSON.stringify(data)));
		
	};
	
	window.triggerObjects = {};
	
	window.trigger = function(obj, id) {
			
			var func;
			for( var ev in obj.events ){
	
				if( typeof obj.events[ev] == 'function' )
					func = obj.events[ev];
				else if( typeof obj[obj.events[ev]] == 'function' )
					func = obj[obj.events[ev]];
				else continue;
				
				ev = ev.split(',');
				
				ev.map(function(evs){
					
					evs = evs.split(':');
	
					if(evs[1] === undefined)evs[1] = 'click';
					
					if (id !== undefined && id === true) {
						if (evs[0] === '')
							obj.el.off(evs[1]).on(evs[1], obj, func);
						else obj.el.find(evs[0]).off(evs[1]).on(evs[1], obj, func);
					} else {
						if (evs[0] === '')
							obj.el.on( evs[1], obj, func );
						else obj.el.find( evs[0] ).on( evs[1], obj, func );
					}
				});
	
			};
			
			if (typeof obj.init == 'function')
				obj.init({data: obj});
			
			if (id !== undefined)
				triggerObjects[id] = obj;
				
		};
	
	var popup_actions = (s) => {
		if (window.frameElement) {
			if (s == 'open') {
				window.frameElement.setAttribute(
					'data-current-style', 
					encodeURIComponent(window.frameElement.getAttribute('style'))
				);
				window.frameElement.setAttribute(
					'data-full', 'true'
				);
				// $(window.frameElement).css({
				// 	position: 'fixed',
				// 	top: '0px',
				// 	left: '0px',
				// 	width: '100vw',
				// 	height: '100vh',
				// 	zIndex: '1000000',
				// });
			} else {
				window.frameElement.setAttribute(
					'style', 
					decodeURIComponent(window.frameElement.getAttribute('data-current-style'))
				);
				window.frameElement.removeAttribute('data-full');
			}
		};
	};
	
	var lightbox = function(ops) {

			if (ops == 'close') {
				popup_actions('close');
				return $('#lumise-lightbox').remove();
			};
			var getWidthParent = $('div#lumise-product-page').width();
			if(getWidthParent >= 1000){
				getWidthParent = 1000;
			}
			// var tmpl = '<div id="lumise-lightbox" class="lumise-lightbox">\
			// 				<div id="lumise-lightbox-body">\
			// 					<div id="lumise-lightbox-content" style="min-width:%width%px">\
			// 						%content%\
			// 					</div>\
			// 					%footer%\
			// 					<a class="kalb-close" href="#close" title="Close">\
			// 						<svg enable-background="new 0 0 32 32" height="32px" id="close" version="1.1" viewBox="0 0 32 32" width="32px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M17.459,16.014l8.239-8.194c0.395-0.391,0.395-1.024,0-1.414c-0.394-0.391-1.034-0.391-1.428,0  l-8.232,8.187L7.73,6.284c-0.394-0.395-1.034-0.395-1.428,0c-0.394,0.396-0.394,1.037,0,1.432l8.302,8.303l-8.332,8.286  c-0.394,0.391-0.394,1.024,0,1.414c0.394,0.391,1.034,0.391,1.428,0l8.325-8.279l8.275,8.276c0.394,0.395,1.034,0.395,1.428,0  c0.394-0.396,0.394-1.037,0-1.432L17.459,16.014z" fill="#121313" id="Close"></path><g></g><g></g><g></g><g></g><g></g><g></g></svg>\
			// 					</a>\
			// 				</div>\
			// 				<div class="kalb-overlay"></div>\
			// 			</div>',
			// 	cfg = $.extend({
			// 		width: 1000,
			// 		footer: '',
			// 		content: '',
			// 		onload: function(){},
			// 		onclose: function(){}
			// 	}, ops);

			var tmpl = '<div id="lumise-lightbox" class="lumise-lightbox">\
							<div id="lumise-lightbox-body">\
								<div id="lumise-lightbox-content" style="min-width:'+getWidthParent+'px">\
									%content%\
								</div>\
								%footer%\
								<a class="kalb-close" href="#close" title="Close">\
									<svg enable-background="new 0 0 32 32" height="32px" id="close" version="1.1" viewBox="0 0 32 32" width="32px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M17.459,16.014l8.239-8.194c0.395-0.391,0.395-1.024,0-1.414c-0.394-0.391-1.034-0.391-1.428,0  l-8.232,8.187L7.73,6.284c-0.394-0.395-1.034-0.395-1.428,0c-0.394,0.396-0.394,1.037,0,1.432l8.302,8.303l-8.332,8.286  c-0.394,0.391-0.394,1.024,0,1.414c0.394,0.391,1.034,0.391,1.428,0l8.325-8.279l8.275,8.276c0.394,0.395,1.034,0.395,1.428,0  c0.394-0.396,0.394-1.037,0-1.432L17.459,16.014z" fill="#121313" id="Close"></path><g></g><g></g><g></g><g></g><g></g><g></g></svg>\
								</a>\
							</div>\
							<div class="kalb-overlay"></div>\
						</div>',
				cfg = $.extend({
					width: 1000,
					footer: '',
					content: '',
					onload: function(){},
					onclose: function(){}
				}, ops);

			if (cfg.footer !== '')
				cfg.footer = '<div id="lumise-lightbox-footer">'+cfg.footer+'</div>';

			tmpl = $(tmpl.replace(/\%width\%/g, cfg.width).
						replace(/\%content\%/g, cfg.content).
						replace(/\%footer\%/g, cfg.footer));

			$('.lumise-lightbox').remove();
			$('body').append(tmpl);

			cfg.onload(tmpl);
			tmpl.find('a.kalb-close,div.kalb-overlay').on('click', function(e){
				cfg.onclose(tmpl);
				popup_actions('close');
				$('.lumise-lightbox').remove();
			});
			
			popup_actions('open');

		},
		process_submit_upload = function() {
			
			var formData = new FormData(), 
				no_data = true;
			
			$('input.lumise-upload-helper-inp').each(function() {
				
				var el = $(this),
					val = el.val();
					
				if (val === '' || el.attr('data-file') == 'design')	
					return;
					
				if (el.attr('data-file') == 'font') {
					if (val.indexOf('data:application/') === -1)
						return;
				} else if (val.indexOf('data:image/') === -1)
					return;
					
				no_data = false;
				
				if (el.attr('data-processing') == 'true')
					return;
					
				el.attr({'data-processing': 'true'});
				
				$('div.lumise_form_submit a.lumise_cancel').remove();
				
				$('div.lumise_form_submit input[type="submit"]').hide().after('<button disabled="true" style="padding: 0 14px;margin-left:180px" class="lumise_cancel" id="lumise-form-submitting"><i class="fa fa-spin fa-spinner"></i> Uploading..</button>');
				
				formData.append(this.getAttribute('name'), new Blob([btoa(encodeURIComponent(val))]));
				
						 
			});
			
			if (no_data === false) {
				
				formData.append('action', 'upload_fields');
				formData.append('nonce', 'LUMISE_ADMIN:'+LumiseDesign.nonce); 
			
				$.ajax({
				    data	:	 formData,
				    type	:	 "POST",
				    url		:	 LumiseDesign.ajax,
				    contentType: false,
				    processData: false,
				    xhr: function() {
					    var xhr = new window.XMLHttpRequest();
					    xhr.upload.addEventListener("progress", function(evt){
					      if (evt.lengthComputable) {
					        var percentComplete = evt.loaded / evt.total,
					        	txt = '<i class="fa fa-spin fa-spinner"></i>  '+parseInt(percentComplete*100)+'% upload complete';
					        if (percentComplete === 1)
					        	txt = '<i class="fa fa-spin fa-refresh"></i> Submitting..';
					       	$('#lumise-form-submitting').html(txt);
					      }
					    }, false);
					    return xhr;
					},
				    success: function (res, status) {
					    
					    res = JSON.parse(res);
					    
					    if (res.error) {
						    alert(res.error);
						    return;
					    };
					    
					    files = JSON.parse(decodeURIComponent(res.success));
					    
					    Object.keys(files).map(function(n) {
						    $('input.lumise-upload-helper-inp[name="'+n+'"]').val(files[n]);
					    });
					    
					    $('input.lumise-upload-helper-inp').eq(0).closest('form').submit();
					    
					}
				});
				
			}
			
			return no_data;
			
		};
	
	window.enjson = function(str) {
		return btoa(encodeURIComponent(JSON.stringify(str)));
	};
	
	window.dejson = function(str) {
		return JSON.parse(decodeURIComponent(atob(str)));
	};
	
	window.esc_html = function(str) {
		return str.replace(/\"/g, '&quot;')
				  .replace(/\'/g, '&#39;')
				  .replace(/\>/g, '&gt;')
				  .replace(/\</g, '&lt;');
	};
	
	window.lumise = {

		i : function(s){
			return LumiseDesign.js_lang[s.toString()];
		},
		
		filters : {},
		
		actions : {},
		
		add_filter : function(name, callback) {
			if (this.filters[name] === undefined)
				this.filters[name] = [];
			if (typeof callback == 'function')
				this.filters[name].push(callback);
		},

		apply_filter : function(name, obj, p) {

			if (this.filters[name] !== undefined) {
				this.filters[name].map(function(filter){
					if (typeof filter == 'function')
						obj = filter(obj, p);
				});
			}

			return obj;

		},
		
		add_action : function(name, callback) {
			
			this.actions.add(name, callback);
			
		},

		do_action : function(name, obj) {

			return this.actions.do(name, obj);
		},
				
		product: {		
			
			popup: $('#lumise-popup'),
			
			init: function(cfg) {
				
				if (cfg.bases !== undefined)
					lumise.product.bases = cfg.bases;
				
				trigger({
					
					el: $('#lumise-product-page'),
					
					events: {
						'.lumise-popup-content .close-pop': 'close_popup',
						'#lumise-product-form:submit': 'before_submit',
						'#lumise-popup': 'popup_click',
						'#lumise-stages-wrp ul.lumise_tab_nav li[data-add="tab"]': 'new_stage',
						'#lumise-stages-upload-helper:change': 'upload_image_helper',
						'#lumise-stages-wrp .lumise_tab_nav_wrap': 'is_stage_accord',
						'#lumise-stages-wrp div.lumise_tab_nav_wrap>i[data-move]': 'stage_accord',
						'div.fill-base-color input:change': 'fill_variation'
					},
					
					fill_variation : function(e) {
						
						let wrp = $(this).closest('div.lumise-stages-wrp');
						
						wrp.find('div.fill-base-color input').val(this.value);
						
						wrp.find('div.lumise-stage-body .lumise-stage-design-view').css({
							background: this.value
						}).click();
						
						wrp.find('div.lumise-stage-editzone').css({
							'border-color': lumise.invert_color(this.value)
						});
					},
					
					change_color: function(e) {
						
						if (this.tagName === 'INPUT') {
							lumise.invert_color(this.value);
							return $('div.lumise-stage-body .lumise-stage-design-view').css({background: this.value});
						}
						
						var color = e.target.getAttribute('data-color');
						
						if (color) {
							lumise.invert_color(e.target.getAttribute('data-color'));
							$('div.lumise-stage-body .lumise-stage-design-view').css({background: e.target.getAttribute('data-color')});
						}
		
					},
					
					invert: function(color) {
						$('.lumise-stage-editzone').css({'border-color': lumise.invert_color(color), 'color': lumise.invert_color(color)});
					},
					
					close_popup: function(e) {
						
						$('#lumise-popup').hide();
						popup_actions('close');
						e.preventDefault();
						
					},
					
					before_submit: function(e) {
						
						/*
						*	Printing
						*/	
						$('.lumise_field_printing').each(function(){
							var vals = {};
							$(this).find('.lumise_checkbox').each(function(){
								if ($(this).find('input.action_check').prop('checked')) {
									
									var v = $(this).find('input.action_check').val().toString(),
										r = $(this).find('.lumise_radios input[type="radio"]:checked').val();
									
									if (this.getAttribute('data-type') == 'size') {
										if (r !== null && r !== undefined) {
											vals['_'+v] = r;
										}
									} else vals['_'+v] = '';
								}
							});
							
							$(this).find('input.field-value').val(encodeURIComponent(JSON.stringify(vals)));
							
						});
				
						/*
						*	Stages
						*/
						
						var stages = lumise.product.get_stages($('#lumise-stages-wrp')),
							variations = lumise.product.get_variations();
						
						$('textarea#lumise-field-stages-inp').val(enjson(stages));
						$('textarea#lumise-field-variations-inp').val(enjson(variations));
						var attrs = lumise.product.get_attributes();
						Object.keys(attrs).map(function(index) {
							if(['select', 'product_color', 'color', 'checkbox', 'radio'].indexOf(attrs[index].type) < 0)
							{
								return true;
							}
							attrs[index].values = typeof attrs[index].values === "string" ?  JSON.parse(attrs[index].values): attrs[index].values;
						})
						$('textarea#lumise-field-attributes-inp').val(enjson(attrs))
						
						return true; /*lumise.product.upload_images_submit(stages, variations);*/
						
					},
					
					popup_click: function(e) {
						
						var act = e.target.getAttribute('data-act'),
							etarget = e.target;
						
						if (e.target.id == 'lumise-popup')
							act = 'close';
						
						if (!act)
							return;
							
						switch (act) {
							case 'close': 
								$(this).hide();
								popup_actions('close');
								return e.preventDefault();	 
							break;
							case 'base': 
								
								var url = e.target.getAttribute('data-src'),
									source = e.target.getAttribute('data-source');
									
								lumise.product.set_image(url, source);
								
							break;
							case 'upload': 
								
								lumise.product.upload({
									max_width: 5000,//1350,
									max_height: 5000,//815,
									/*min_size: {
										value: 100024, //100k
										err_msg: cfg.sm+"\n\n"+cfg.ru
									},*/
									max_size: {
										value: 20485760, //20MB
										err_msg: cfg.lg+"\n\n"+cfg.ru
									},
									type: {
										value: ["image/png", "image/jpeg", "image/svg+xml"],
										err_msg: cfg.tp+"\n\n"+cfg.ru
									},
									callback: function(url) {
										
										var formData = new FormData(),
											tx = $('#lumise-base-upload-progress div[data-view="uploading"]>span'),
											name = new Date().getTime().toString(36);
								
										$('#lumise-base-upload-progress div[data-view]').addClass('hidden');
										$('#lumise-base-upload-progress,#lumise-base-upload-progress div[data-view="uploading"]').removeClass('hidden');
																		
										formData.append(name, new Blob([url]));
										
										formData.append('action', 'upload_product_images'); 
										formData.append('nonce', 'LUMISE_ADMIN:'+LumiseDesign.nonce);
										formData.append('vendor', window.parent && window.parent.lumisejs && window.parent.lumisejs.is_admin === false ? 'true' : 'false');
										
										tx.html('Starting upload..');
										
										$.ajax({
										    data	:	 formData,
										    type	:	 "POST",
										    url		:	 LumiseDesign.ajax,
										    contentType: false,
										    processData: false,
										    xhr		:	 function() {
											    var xhr = new window.XMLHttpRequest();
											    xhr.upload.addEventListener("progress", function(evt){
												    
												    if (evt.lengthComputable) {
												        var percentComplete = evt.loaded / evt.total;
												        if (percentComplete < 1)
												        	tx.html(parseInt(percentComplete*100)+'% complete');
												        else tx.html('Upload completed, processing..');
												    }
												    
											    }, false);
											    return xhr;
											},
										    success: function (res, status) {
											    
												$('#lumise-base-upload-progress div[data-view]').addClass('hidden');
											    
											    if (res.indexOf('Error') === 0) {
												    $('#lumise-base-upload-progress div[data-view="fail"]').removeClass('hidden');
												    alert(res);
												    return;
											    };
											    
											    res = JSON.parse(res);
											    
											    $('#lumise-base-upload-progress div[data-view="success"]')
											    	.removeClass('hidden')
											    	.find('img')
											    	.attr({src: lumise_upload_url+res[name], 'data-src': res[name]});
											    
											    $('#lumise-uploaded-bases').prepend('<li><img data-act="base" data-src="'+res[name]+'" data-source="uploads" src="'+lumise_upload_url+res[name]+'"><i class="fa fa-trash" title="Delete" data-act="delete" data-file="'+name+'.jpg"></i><span data-file="'+name+'.jpg" data-act="edit-name" data-name="'+name+'" title="Click to edit image name">'+name+' <i data-file="'+name+'.jpg" data-act="edit-name" data-name="'+name+'" class="fa fa-pencil"></i></span></li>');
											    
										    },
										    error: function() {
											    $('#lumise-base-upload-progress div[data-view]').addClass('hidden');
											    $('#lumise-base-upload-progress div[data-view="fail"]').removeClass('hidden');
											    alert('Error: could not upload images this time, please try again later');
										    }
										});
										
										//lumise.product.set_image(url, 'uploads', etarget);
									}
								});
								
								e.preventDefault();
								
							break;
							
							case 'use' : 
							
								var url = $('#lumise-base-upload-progress div[data-view="success"] img').attr('data-src'),
									source = 'uploads';
									
								lumise.product.set_image(url, source);
								
							break;
							
							case 'dismiss' : 
								$('#lumise-base-upload-progress').addClass('hidden');
							break;
							
							case 'samples' : 
								$('#lumise-uploaded-bases,button[data-act="samples"],button[data-act="upload"]').addClass('hidden');
								$('#lumise-sample-bases,button[data-act="uploaded"]').removeClass('hidden');
								e.preventDefault();
							break;
							
							case 'uploaded' : 
								$('#lumise-uploaded-bases,button[data-act="samples"],button[data-act="upload"]').removeClass('hidden');
								$('#lumise-sample-bases,button[data-act="uploaded"]').addClass('hidden');
								e.preventDefault();
							break;
							
							case 'edit-name': 
								
								var name = prompt('Enter new image name', etarget.getAttribute('data-name')),
									file = etarget.getAttribute('data-file');
								
								if (name === null || name === '')
									return;
								
								$.ajax({
									url: LumiseDesign.ajax,
									method: 'POST',
									data: {
										nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
										ajax: 'backend',
										action: 'edit_name_product_image',
										name: name,
										file: file,
										vendor: window.parent && window.parent.lumisejs && window.parent.lumisejs.is_admin === false ? 'true' : 'false'
									},
									statusCode: {
										403: function(){
											alert(LumiseDesign.js_lang.error_403);
										}
									},
									success: function(res) {
										if (etarget.tagName == 'I') {
											var i = $(etarget).clone();
												pr = $(etarget).parent();
										} else {
											var pr = $(etarget),
												i = pr.find('i').clone();
										}
										pr.html(name);
										pr.append(i);
									}
								});
								
							break;
							
							case 'delete': 
							
								if (confirm(lumise.i(170))) {
									
									var file = e.target.getAttribute('data-file'),
										li = $(e.target).closest('li');
									
									li.attr({'data-act': 'load-more', 'data-loading': 'true'})
										.html('<i class="fa fa-spin fa-spinner fa-2x"></i><br>'+lumise.i('wait'));
									
									$.ajax({
										url: LumiseDesign.ajax,
										method: 'POST',
										data: {
											nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
											ajax: 'backend',
											action: 'delete_base_image',
											file: file.split('/').pop(),
											vendor: window.parent && window.parent.lumisejs && window.parent.lumisejs.is_admin === false ? 'true' : 'false'
										},
										statusCode: {
											403: function(){
												alert(LumiseDesign.js_lang.error_403);
											}
										},
										success: function(res) {
											
											if (res != '1')
												alert(res);
											
											li.remove();
											
										}
									});
									
								}
								
							break;
							
							case 'load-more':
								
								if (e.target.getAttribute('data-loading') == 'true')
									return;
								
								e.target.innerHTML = '<i class="fa fa-spin fa-spinner fa-2x"></i><br>'+lumise.i('wait');
								e.target.setAttribute('data-loading', 'true');
								
								$.ajax({
									url: LumiseDesign.ajax,
									method: 'POST',
									data: {
										nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
										ajax: 'backend',
										action: 'load_more_bases',
										limit: 18,
										start: e.target.getAttribute('data-start'),
										vendor: window.parent && window.parent.lumisejs && window.parent.lumisejs.is_admin === false ? 'true' : 'false'
									},
									statusCode: {
										403: function(){
											alert(LumiseDesign.js_lang.error_403);
										}
									},
									success: function(res) {
										
										res = JSON.parse(res);
										
										var ul = $(e.target).parent();
										$(e.target).remove();
										if (res.items.length > 0) {
											res.items.map(function(re, i) {
												
												re = encodeURI(re);
												
												var name = re.split('.'),
													type = name.pop();
												
												name = name.join('.').split('/').pop();
												
												if (res.names !== undefined && res.names[i] !== '')
													name = res.names[i];
												
												ul.append('<li><img data-act="base" data-src="products/'+re+'" data-source="uploads" src="'+lumise_upload_url+'products/'+re+'" /><i class="fa fa-trash" title="'+lumise.i('delete')+'" data-act="delete" data-file="'+re+'"></i><span data-file="'+re+'" data-act="edit-name" data-name="'+encodeURIComponent(name)+'" title="'+lumise.i(171)+'">'+name+' <i data-file="'+re+'" data-act="edit-name" data-name="'+encodeURIComponent(name)+'" class="fa fa-pencil"></i></span></li>');
											});
											
											if (res.items.length+res.start < res.total) {
												ul.append('<li data-act="load-more" data-start="'+(res.items.length+res.start)+'">'+lumise.i(94)+' &rarr;</li>');
											}
										} else if (res.start === 0) {
											ul.append('<li data-view="noitems">'+lumise.i(169)+'</li>');
										}
										
									}
								});
								
							break;
							
						}
						
					},
					
					new_stage: function(e) {
						
						var wrp = $(this).closest('div.lumise_tabs_wrapper'),
							navs = wrp.find('.lumise_tab_nav_wrap ul.lumise_tab_nav');
						
						if (navs.find('>li').length > cfg.max_stages) {
							alert(lumise.i(163));
							e.preventDefault();
							return;	
						};
						
						var nav = navs.find('li[data-add="tab"]').prev().clone(true),
							bod = wrp.find('div.lumise_tabs div.lumise_tab_content').last().clone(true),
							num = new Date().getTime().toString(36);
							
						navs.find('li[data-add="tab"]').before(nav);
						
						nav.find('a').attr({"href": '#lumise-stage-'+num, "data-label": "Untitled"}).find('text').html('Untitled');
						nav.find('>a>span').remove();
						
						bod.attr({"id": "lumise-stage-"+num, "data-stage": num});
						
						wrp.find('div.lumise_tabs').append(bod);
						
						wrp.find('.lumise_tab_content.active, ul.lumise_tab_nav li.active').removeClass('active');
						   
						bod.find('div.lumise-stage-settings').attr({id: 'lumise-product-design-'+num});
						bod.find('input.product-upload').attr({"name": "product-upload-"+num});
						
						bod.find('.lumise-stage-btn button[data-btn="reset"]').click();
						wrp.find('div.lumise_tab_nav_wrap>i[data-move="right"]').click();
						
						setTimeout(function(el) {el.click()}, 10, nav.find('a'));
						
						e.preventDefault();
						
					},
					
					upload_image_helper: function(e) {
						
						if (typeof this.cb == 'function' && this.files && this.files[0]) {
							var reader = new FileReader();
							reader.onload = this.cb;
							reader.file = this.files[0];
					        reader.readAsDataURL(this.files[0]);
						};
						
						this.type = 'text';
						this.value = '';
						this.type = 'file';
						this.cb = null;
						
					},
					
					is_stage_accord: function(e) {
						
						var el = $(this),
							nav = el.find('ul.lumise_tab_nav'),
							wrp = el.find('div.lumise_tab_nav_inner'),
							act = nav.find('li.active');
							
						if (nav.width() !== 0 && nav.width() > wrp.width()) {
							el.addClass('is_accord');
							if (nav.get(0).offsetLeft + nav.width() < wrp.width()*0.5)
								nav.css({left: (wrp.width()-nav.width())+'px'});
						} else {
							el.removeClass('is_accord');
							nav.css({left: '0px'});
						};
						
						if (
							this.first_time === undefined && 
							act.get(0) && 
							act.get(0).offsetLeft > wrp.parent().width()
						) nav.css({left:(-act.get(0).offsetLeft+(wrp.width()-act.width()))+'px'});
							
						this.first_time = true;
						
					},
					
					stage_accord: function(e) {
						
						var dir = this.getAttribute('data-move'),
							wrp = $(this.parentNode).find('div.lumise_tab_nav_inner'),
							inner = wrp.find('ul.lumise_tab_nav'),
							ww = wrp.width(),
							iw = inner.width(),
							il = parseFloat(inner.css('left'));
						
						if (dir == 'left' && il < 0)
							inner.css({left: (il+(ww*0.75) < 0 ? il+(ww*0.75) : 0)+'px'});
						
						if (dir == 'right' && il>-(iw-ww))
							inner.css({left: (il-(ww*0.75) > -(iw-ww) ? il-(ww*0.75) : -(iw-ww))+'px'});
						
					}
					
				});
				
				this.stage_events();
				this.att_events();
				
			},
			
			stage_events: function() {
				
				trigger({
					
					el: $('#lumise-product-page'),
					
					events: {
						
						'button[data-func="select"]': 'select_base',
						'button[data-func="download"]': 'download_mockup',
						'button[data-func="reset"]': 'reset_base',
						'select[data-name="sizes"]:change': 'select_size',
						'input[data-name="width"]:change,input[data-name="height"]:change': 'change_size',
						'input[data-name="width"]:keydown,input[data-name="height"]:keydown': function(e) {
							if (e.keyCode === 13)
								this.blur();	
						},
						'input[data-name="crop_marks_bleed"]:change': 'crop_marks_bleed',
						'.constrain-aspect-ratio': function() {
							if ($(this).hasClass('active'))
								$(this).removeClass('active');
							else $(this).addClass('active');
						},
						'i[data-func="resize"]': 'input_size',
						'.lumise-stage-editzone:mousedown': 'start_drag',
						'.lumise-stage-editzone:mouseup': function() {
							lumise.product.update_pos($(this).closest('div.lumise-stage-design-view'));
						},
						'.editzone-ranges .design-scale input:input': 'design_scale',
						'.editzone-ranges .editzone-radius input:input': 'editradius',
						'.lumise-stage-editzone button': 'edit_funcs',
												
						'#lumise_template': 'template', 
						'#lumise_template_btn': 'select_template',
						'#lumise-stages-wrp ul.lumise_tab_nav li a': 'edit_stage_label',
						
						'.lumise_form_content input[name="is_mask"]:change': 'is_mask',
						'#lumise-stages-wrp ul.lumise_tab_nav a:dragstart': function(e){e.preventDefault();}
						
					},
										
					select_base: function(e) {
						
						if ($('#lumise-popup').attr('data-moved') !== true) {
							$('body').append($('#lumise-popup').attr({'data-moved': true}));
						}
						
						$('#lumise-popup').show().attr({
							'data-stage': $(this).closest('div.lumise_tab_content').attr('data-stage')
						});
						
						if ($('#lumise-uploaded-bases li[data-act="load-more"][data-start="0"]').length > 0)
							$('#lumise-uploaded-bases li[data-act="load-more"]').click();
						
						popup_actions('open');
							
						e.preventDefault();
						
					},
					
					download_mockup : function(e) {
						
						e.preventDefault();
						
						let canvas = document.createElement('canvas'),
							editcanvas = document.createElement('canvas'),
							sbody =  $(this).closest('.lumise-stage-body'),
							img = sbody.find('img.lumise-stage-image').get(0),
							temp = sbody.find('div.design-template-inner>img').get(0),
							editzone = sbody.find('.lumise-stage-editzone').get(0),
							ctx = canvas.getContext('2d'),
							ectx = editcanvas.getContext('2d'),
							ratio = img.width/img.naturalWidth;
						
						canvas.width = img.naturalWidth;
						canvas.height = img.naturalHeight;
						
						editcanvas.width = editzone.offsetWidth/ratio;
						editcanvas.height = editzone.offsetHeight/ratio;
						
						ctx.fillStyle = img.parentNode.style.backgroundColor;
						ctx.fillRect(0, 0, canvas.width, canvas.height);
						
						ectx.drawImage(temp, temp.offsetLeft/ratio, temp.offsetTop/ratio, temp.width/ratio, temp.height/ratio);
						
						let top = parseFloat(editzone.style.marginTop.replace('px', ''))/ratio,
							left = parseFloat(editzone.style.marginLeft.replace('px', ''))/ratio;
						
						if (isNaN(top))
							top = 0;
						if (isNaN(left))
							left = 0;
							
						left += (canvas.width/2) - (editcanvas.width/2);
						top += (canvas.height/2) - (editcanvas.height/2);
						
						let x = left,
							y = top,
							width = editcanvas.width,
							height = editcanvas.height,
							radius = parseInt(editzone.style.borderRadius.replace('px', ''));
						
						ctx.save();
						ctx.beginPath();
						ctx.moveTo(x + radius, y);
						ctx.lineTo(x + width - radius, y);
						ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
						ctx.lineTo(x + width, y + height - radius);
						ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
						ctx.lineTo(x + radius, y + height);
						ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
						ctx.lineTo(x, y + radius);
						ctx.quadraticCurveTo(x, y, x + radius, y);
						ctx.closePath();
						ctx.clip();
						ctx.drawImage(editcanvas, x, y, width, height);
						ctx.restore();
						
						ctx.drawImage(img, 0, 0);
						
						
						
						let dataURL = canvas.toDataURL('image/jpeg', 1).split(','),
							binStr = atob(dataURL[1]),
							len = binStr.length,
							arr = new Uint8Array(len);
		
						for (var i = 0; i < len; i++) {
							arr[i] = binStr.charCodeAt(i);
						}
		
						let blob = new Blob([arr], {
								type: dataURL[0].substring(dataURL[0].indexOf('image/'), dataURL[0].indexOf(';')-1)
							}),
							a = document.createElement('a'),
							name = 'lumise-mockup'
						
						a.download = name+'.jpg';
						a.href = URL.createObjectURL(blob);
						a.click();
						URL.revokeObjectURL(a.href);
						
					},
					
					reset_base: function(e) {
						
						var wrp = $(this).closest('.lumise-stage-settings');
						
						wrp.find('img.lumise-stage-image').attr({'src': '', 'data-url': ''});
						wrp.removeClass('stage-enabled').addClass('stage-disabled');
						
						e.preventDefault();
								
					},
					
					select_size: function(e) {
						
						var size = this.value,
							wrp = $(this).closest('div.lumise-stage-body'),
							edz = wrp.find('div.lumise-stage-editzone'),
							img = wrp.find('img.lumise-stage-image');
					
						if (size != 'custom' && size !== '') {
							
							edz.css({height: (edz.width()/0.7069555302166477)+'px'});
							
							if (edz.height() > img.height()) {
								edz.css({
									height: (img.height()*0.9)+'px',
									width: (img.height()*0.9*0.7069555302166477)+'px'
								});
							}
							
							if (edz.height()+edz.get(0).offsetTop > img.height()*0.9) {
								edz.css({
									top: ((img.height()-edz.height())/2)+'px'
								});
							}
							
							edz.find('i[data-func="resize"]').attr({'data-info': edz.width()+'x'+edz.height()});
							
							wrp.find('div.edr-row[data-row="values"], div.edr-row[data-row="unit"]').hide();
							
						} else if (size == 'custom'){
							
							wrp.find('div.edr-row[data-row="values"], div.edr-row[data-row="unit"]').show();
							
							if (
								e.data.el.find('input[data-name="width"]').val() === '' &&
								e.data.el.find('input[data-name="height"]').val() === ''
							) {
								e.data.el.find('input[data-name="width"]').val('21');
								e.data.el.find('input[data-name="height"]').val('29.7');
							} else if (
								e.data.el.find('input[data-name="width"]').val() !== '' &&
								e.data.el.find('input[data-name="height"]').val() === ''
							) {
								e.data.el.find('input[data-name="height"]').val(
									parseFloat(e.data.el.find('input[data-name="width"]').val())*(29.7/21)
								);
							} else if (
								e.data.el.find('input[data-name="width"]').val() === '' &&
								e.data.el.find('input[data-name="height"]').val() !== ''
							) {
								e.data.el.find('input[data-name="width"]').val(
									parseFloat(e.data.el.find('input[data-name="width"]').val())*(21/29.7)
								);
							};
							
							e.data.el.find('input[data-name="height"]').change();
							
						} else {
							wrp.find('div.edr-row[data-row="values"], div.edr-row[data-row="unit"]').hide();
						}
						
						lumise.product.update_pos(wrp);
						
					},
					
					change_size: function(e) {
						
						var wrp = $(this).closest('div.lumise-stage-design-view'),
							iel = wrp.find('img.lumise-stage-image'),
							wel = wrp.find('input[data-name="width"]'),
							hel = wrp.find('input[data-name="height"]'),
							edz = wrp.find('div.lumise-stage-editzone'),
							nw = 0, nh = 0;
							
						if (hel.val() !== '' && wel.val() !== '') {
							
							if (this.getAttribute('data-name') == 'width') {
								nw = (edz.height()*(parseFloat(wel.val())/parseFloat(hel.val())));
								nh = edz.height();
							} else if (this.getAttribute('data-name') == 'height') {
								nh = (edz.width()*(parseFloat(hel.val())/parseFloat(wel.val())));
								nw = edz.width();
								
							}
							
							if (nw > iel.width()) {
								nh *= (iel.width()/nw);
								nw = iel.width();
								edz.css({left: '0px'});
							}
							
							if (nh > iel.height()) {
								nw *= (iel.height()/nh);
								nh = iel.height();
								edz.css({top: '0px'});
							}
							
							edz.css({width: nw+'px', height: nh+'px'});
							wrp.attr({'data-info': wrp.attr('data-info').split(':')[0]+': '+parseInt(nw)+'x'+parseInt(nh)});
							
							if (edz.get(0).offsetTop+edz.height() > iel.height())
								edz.css({top: (iel.height()-edz.get(0).offsetTop)+'px'});
							
							if (edz.get(0).offsetLeft+edz.width() > iel.width())
								edz.css({left: (iel.width()-edz.get(0).offsetLeft)+'px'});
							
						}
						
						lumise.product.update_pos(wrp);
						
					},
					
					crop_marks_bleed : function(e) {
						
						let wrp = $(this).closest('div.lumise-stage-body');
						if (this.checked)
							wrp.find('div.edr-row[data-row="bleed-range"]').show();
						else
							wrp.find('div.edr-row[data-row="bleed-range"]').hide();
					},
					
					edit_funcs: function(e) {
						
						var func = this.getAttribute('data-func');
						
						if (func == 'select-design') {
							e.data.select_template(e);
						} else if (func == 'clear-design') {
							
							var view = $('#lumise-stages-wrp .lumise_tab_content.active .lumise-stage-editzone');
							if (view.length > 0 && confirm(lumise.i(101)))
								view.find('.design-template-inner').remove();
							
							this.style.display = 'none';
							
							view.append(
								'<button data-func="select-design" class="design-template-btn">\
								<i class="fa fa-paint-brush"></i> \
								'+lumise.i(91)+'\
								</button>'
							);
							
							view.find('button').on('click', e.data.select_template);
							$('.lumise_field_stages .lumise_tab_content.active .editzone-ranges .design-scale').hide();
							
						};
						
						e.preventDefault();
						return false;
							
					},
					
					design_scale: function(e) {
						
						var img = $(this).closest('.lumise-stage-design-view').find('.design-template-inner img');
						if (img.length === 0)
							return;
							
						var im = img.get(0),
							s = parseFloat(this.value), 
							w = im.naturalWidth,
							h = im.naturalHeight,
							ow = parseFloat(im.style.width.replace('px', '')),
							oh = parseFloat(im.style.height.replace('px', '')),
							cl = parseFloat(im.style.left.replace('px', '')),
							ct = parseFloat(im.style.top.replace('px', ''));
						
						if (!s || isNaN(s))
							s = 1;
						
						if (isNaN(ow))
							ow = im.offsetWidth;
						if (isNaN(oh))
							oh = im.offsetHeight;
						if (isNaN(ct))
							ct = im.offsetTop;
						if (isNaN(cl))
							cl = im.offsetLeft;
							
						let nw = ((w*s)/100),
							nh = ((h*s)/100),
							nl = (nw-ow)/2,
							nt = (nh-oh)/2;
								
						img.css({
							width :	nw+'px', 
							height : nh+'px',
							left : (cl-nl)+'px',
							top : (ct-nt)+'px'
						});
						
						lumise.product.update_pos($(this).closest('div.lumise-stage-design-view'));
					},
					
					editradius: function(e) {
						
						$(this).closest('.lumise-stage-design-view').find('.lumise-stage-editzone,.design-template-inner').css({
							borderRadius: this.value+'%'
						});	
						
					},
					
					start_drag: function(e) {
						
						if ($(e.target).closest('.design-template-inner').length > 0) {
						
							var $this = $(e.target).closest('.design-template-inner').find('img'),
								clientX = e.clientX,
								clientY = e.clientY,
								left = $this.get(0).offsetLeft,
								top = $this.get(0).offsetTop,
								width = $this.get(0).offsetWidth,
								height = $this.get(0).offsetHeight,
								limit = false,
								resize = false;
						
						}else{
						
							var func = e.target.getAttribute('data-func') || 
									   e.target.parentNode.getAttribute('data-func');
							
							var gui = $(e.target).hasClass('.editzone-gui') || 
									  $(e.target).closest('.editzone-gui').length > 0;
							
							if (func != 'move' && func != 'resize' && gui !== true)
								return false;
								
							var $this = $(this),
								clientX = e.clientX,
								clientY = e.clientY,
								left = this.offsetLeft,
								top = this.offsetTop,
								width = this.offsetWidth,
								height = this.offsetHeight,
								limit = true,
								etarget = $(e.target),
								dview = etarget.closest('.lumise-stage-design-view'),
								size = dview.find('select[data-name="sizes"]').val(),
								constrain = dview.find('span.constrain-aspect-ratio').hasClass('active'),
								width_el = dview.find('input[data-name="width"]'),
								height_el = dview.find('input[data-name="height"]'),
								oratio = parseFloat(width_el.val())/parseFloat(height_el.val()),
								is_size = (size !== '' && size != 'custom'),
								resize = e.target.getAttribute('data-func') == 'resize' || 
										 e.target.parentNode.getAttribute('data-func') == 'resize'
							
							if (is_size)
								oratio = 0.70715;
							
						};
						
						$(document)
						.on('mousemove', function(e){
							
							var pw = $this.parent().width();
								ph = $this.parent().height() - $this.parent().find('.editzone-ranges').height();
							
							if (resize) {
								
								var new_width = (width+((e.clientX-clientX)*2)),
									new_height = (height+((e.clientY-clientY)*2));
									
								if (new_width < 30)
									new_width = 30;
								
								if (new_height < 50)
									new_height = 50;
								
								if (new_width > pw-$this.get(0).offsetLeft)
									new_width = pw-$this.get(0).offsetLeft;
								
								if (new_height > ph-$this.get(0).offsetTop)
									new_height = ph-$this.get(0).offsetTop;
								
								if ((left-((new_width-width)/2)) < 0)
									left = ((new_width-width)/2);
								
								if ((top-((new_height-height)/2)) < 0)
									top = ((new_height-height)/2);
									
								if (is_size || (size == 'custom' && constrain === true)) {
									
									if (new_width/oratio > new_height) {
										new_height = new_width/oratio;
										if (new_height > ph-top) {
											new_height = ph-top;
											new_width = new_height*oratio;
										}
									} else {
										new_width = new_height*oratio;
										if (new_width > pw-left) {
											new_width = pw-left;
											new_height = new_width/oratio;
										}
									}
									
									if ((left-((new_width-width)/2)) < 0) {
										new_width = width+(2*left);
										new_height = new_width/oratio;
									}
									
									if ((top-((new_height-height)/2)) < 0) {
										new_height = height+(2*top);
										new_width = new_height*oratio;
									}
									
								};
								
								new_width = Math.ceil(new_width);
								new_height = Math.ceil(new_height);
								
								$this.css({
									width: new_width+'px',
									height: new_height+'px',
									left: (left-((new_width-width)/2))+'px',
									top: (top-((new_height-height)/2))+'px',
								}).attr({
									'data-pos': '{width: '+new_width+', height: '+new_height+', top: '+(
										$this.get(0).offsetTop-($this.parent().height()/2)+($this.height()/2)
									)+', left: '+(
										$this.get(0).offsetLeft-($this.parent().width()/2)+($this.width()/2)
									)+'}'
								});
								
								dview.attr({'data-info': dview.attr('data-info').split(':')[0]+': '+new_width+'x'+new_height});
								
								if (size == 'custom' && (!constrain || width_el.val() === '' || height_el.val() === '')) {
									if (width_el.val() !== '') {
										height_el.val((parseFloat(width_el.val())*(new_height/new_width)).toFixed(5));
									} else if (height_el.val() !== '') {
										width_el.val((parseFloat(height_el.val())*(new_width/new_height)).toFixed(5));
									}
								}
								
							}else{
								
								var new_left = (left+(e.clientX-clientX)),
									new_top = (top+(e.clientY-clientY));
								
								if (limit) {
										
									if (new_left < 0)
										new_left = 0;
									
									if (new_top < 0)
										new_top = 0;
										
									if (new_left > pw-width)
										new_left = pw-width;
									
									if (new_top > ph-height)
										new_top = ph-height;
										
								}else{
									
									if (new_left < -width*0.85)
										new_left = -width*0.85;
									
									if (new_top < -height*0.85)
										new_top = -height*0.85;
										
									if (new_left > pw-(width*0.15))
										new_left = pw-(width*0.15);
									
									if (new_top > ph-(height*0.15))
										new_top = ph-(height*0.15);
										
								};
								
								if (Math.abs((new_left+(width/2))-(pw/2)) <= 2)
									new_left = Math.ceil((pw/2)-(width/2));
								
								if (Math.abs((new_top+(height/2))-(ph/2)) <= 2)
									new_top = Math.ceil((ph/2)-(height/2));
								
								$this.css({
									left: new_left+'px',
									top: new_top+'px'
								});
								
							}
						})
						.on('mouseup', function(e){
							$(document).off('mousemove mouseup');
							if (resize) {
								if ($this.get(0).offsetLeft < 0)
									$this.css({left: '0px'});
								if ($this.get(0).offsetTop < 0)
									$this.css({top: '0px'});
							}
						});
						
						if (resize) {
							etarget.off('mouseup').on('mouseup', function(e) {
								
								if (
									e.originalEvent.offsetX > this.offsetHeight &&
									Math.abs(clientX-e.clientX) <= 3 &&
									Math.abs(clientY-e.clientY) <= 3
								) {
									
									var inp = prompt(
										lumise.i(167), 
										this.getAttribute('data-info')
									),
									old = this.getAttribute('data-info').split('x');
									
									if (inp !== null && inp.indexOf('x') > -1) {
										
										inp = inp.split('x');
										
										var wrp = $(this).closest('div.lumise-stage-design-view').
													find('img.lumise-stage-image'),
											edt = $(this).closest('div.lumise-stage-editzone');
										
										if (parseFloat(inp[0]) > wrp.width()) {
											inp[0] = wrp.width();
											edt.css({left: 0});
										};
										
										if (parseFloat(inp[1]) > wrp.height()) {
											inp[1] = wrp.height();
											edt.css({top: 0});
										};
										
										if (is_size) {
											
											if (inp[0] != old[0])
												inp[1] = inp[0]/0.70715;
											else
												inp[0] = inp[1]*0.70715;
											
											if (parseFloat(inp[0]) > wrp.width()) {
												inp[0] = wrp.width();
												inp[1] = inp[0]/0.70715;
												edt.css({left: 0});
											};
											if (parseFloat(inp[1]) > wrp.height()) {
												inp[1] = wrp.height();
												inp[0] = inp[1]*0.70715;
												edt.css({top: 0});
											};
										};
										
										this.setAttribute('data-info', Math.ceil(inp[0])+'x'+Math.ceil(inp[1]));
										
										edt.css({
											width: inp[0]+'px',
											height: inp[1]+'px'
										});
										
									}
								}
							});
						}
						
					},
					
					template: function(e) {
						
						if (e.target.tagName == 'I' && e.target.parentNode.getAttribute('href') == '#delete') {
							this.innerHTML = '';
							$('#lumise_template_inp').val('');
						}else{
							e.data.select_template(e);
						}	
						
						e.preventDefault();
						
					},
					
					select_template: function(e) {
						lumise.product.load_designs({});
						e.preventDefault();
					},
					
					edit_stage_label: function(e) {
						
						if (
							this.parentNode.className.indexOf('active') > -1 && 
							e.target.getAttribute('data-func') == 'delete-thumbn'
						) {
							$(this).find('span').remove();
							e.preventDefault();
							return;
						};
						
						if (
							this.parentNode.className.indexOf('active') > -1 && 
							(
								e.target.getAttribute('data-edit') == "thumbnail" ||
								e.target.getAttribute('data-func') == 'upload-thumbn'
							)
						) {
							var el = e.target.getAttribute('data-func') == "upload-thumbn" ? 
									 $(e.target).closest('li').find('a>i') : 
									 $(e.target);
									 
							lumise.product.upload({
								max_height: 160,
								callback: function(url) {
									
									el.prevAll('span').remove();
									el.before(
										'<span>\
											<img src="'+url+'" data-func="upload-thumbn" />\
											<i data-func="delete-thumbn" class="fa fa-times"></i>\
									    </span>'
									);
								}
							});
							
							e.preventDefault();
							return;
							
						};
						
						if (
							this.parentNode.className.indexOf('active') > -1 && 
							e.target.tagName == 'TEXT'
						) {
							
							var text = prompt(
								LumiseDesign.js_lang['151'], 
								decodeURIComponent(e.target.parentNode.getAttribute('data-label'))
									.replace(/\&gt\;/, '>')
									.replace(/\&lt\;/, '<')
									.replace(/\&Prime\;/, '"')
									.replace(/\&prime\;/, "'")
							);
							if (text === null || text === '')
								return;
								
							text = text.replace(/\>/g, '&gt;')
									   .replace(/\</g, '&lt;')
									   .replace(/\"/g, '&Prime;')
									   .replace(/\'/g, '&prime;');
							
							if (text === '')
								return;
								
							$(e.target).parent().attr({'data-label': encodeURIComponent(text)});
							$(this).parent().find('text').html(text);
							
						};
						
					},
					
					is_mask: function(e) {
						let inps = $(this).closest('div.lumise-stages-wrp').
							find('div.lumise-stage-body').
							attr({'data-is-mask': this.checked}).
							find('input[name="is_mask"]');
						if (this.checked)
							inps.attr({checked: 'checked'});
						else
							inps.removeAttr('checked');
					},
					
				}, true);
				
				$('.lumise-stages-wrp ul.lumise_tab_nav').sortable({
					items : '>li:not([data-add="tab"])',
					tolerance: 'pointer'
				});
				
				$('.lumise-stages-wrp .lumise_tab_nav_wrap').click();
				
			},
			
			att_events: function() {
				
				/*Attributes &Variations*/
				
				trigger({
					
					init: function(e) {
						e.data.add_attribute_events(e);
						e.data.add_variation_events(e);
						e.data.load_attributes(e);
						e.data.load_variations(e);
					},
					
					el: $('#lumise-product-form'),
					
					events: {
						':click': function(e) {
							
							var act = e.target.getAttribute('data-act');
							
							if (act !== null && typeof e.data.global_actions[act] == 'function')	
								e.data.global_actions[act](e);
								 
						},
						'[data-act="add_attribute"]': 'add_attribute',
						'[data-act="add_variation"]': 'add_variation',
						'[data-act="bulk_edit_variation"]': 'bulk_edit_variation',
						'[data-act="expand"]': 'expand',
						'[data-act="close"]': 'close',
					},
					
					global_actions : {
						
						delete: function(e) {
							if (confirm('Are you sure?')) {
								$(e.target).closest('.lumise-att-layout-item').remove();
								e.data.refresh_variations(e);
							}
						},
						
						toggle: function(e) {
							
							$(e.target).closest('.lumise-att-layout-item').find('.att-layout-body').slideToggle(
								250,
								function() {
									if (this.style.display == 'none') {
										$(this).closest('.lumise-att-layout-item')
										.find('.att-layout-headitem i[data-act="toggle"]')
										.removeClass('fa-caret-up')
										.addClass('fa-caret-down');
									} else {
										$(this).closest('.lumise-att-layout-item')
										.find('.att-layout-headitem i[data-act="toggle"]')
										.removeClass('fa-caret-down')
										.addClass('fa-caret-up');
									}
								}
							);
						}
						
					},
					
					add_attribute_events: function(e) {
						
						var edata = e.data;
						
						$('#lumise-product-form .lumise-att-layout-item:not([data-event="added"])').each(function() {
							
							this.setAttribute('data-event', 'added');
							
							trigger({
								
								el: $(this),
								
								events: {
									'input[data-name="name"]:input': 'name',
									'input[data-name="name"]:change, textarea[data-name="values"]:change': edata.refresh_variations,
									'input[data-name="use_variation"]:change': 'use_variation',
									'select[data-name="type"]:change': 'type'
								},
								
								name: function(e) {
									$(this).closest('.lumise-att-layout-item')
									.find('.att-layout-headitem strong[data-name]')
									.html(this.value);
								},
								
								use_variation: function(e) {
									
									var select = $(this).closest('.att-layout-body-field').find('[data-name="type"]'),
										ops = select.find('option'),
										vaz = [];
										
									ops.each(function(){
										if (this.getAttribute('data-use-variation') == 'true')
											vaz.push(this.value);
									});
									
									if (this.checked === true) {
										ops.each(function() {
											if (vaz.indexOf(this.value) === -1)
												this.disabled = true;
											if (vaz.indexOf(select.val()) === -1 && this.value == (vaz.length > 0 ? vaz[0] : '')) {
												this.selected = true;
												select.trigger('change');
											}
										});
									} else {
										ops.each(function() {
											this.disabled = null;
										});	
									}
									
									edata.refresh_variations(e);
									
								},
								
								type : function(e) {
									
									if ($(this).find('option:selected').attr('data-unique') == 'true') {
										var exist = false, _this = this, val = $(this).val();
										$(this).closest('#lumise-field-attributes-items')
										.find('select[data-name="type"]').each(function() {
											if (this !== _this && this.value == val)
												exist = true;	
										});
										if (exist === true) {
											alert(document.lumiseconfig.noc);
											$(this).val('');
											return false;
										}
									};
									
									$(this).closest('.lumise-att-layout-item')
									.find('.att-layout-headitem em[data-view="attr-type"]')
									.html(this.options[this.selectedIndex].text);
									
									lumise.product.render_values(
										$(this).closest('div.lumise-att-layout-item').find('div[data-field="values"]'), 
										this.value
									);
									
									e.data.el.find('textarea[data-name="values"]').on('change', edata.refresh_variations);
									
								}
								
							});
							
						});
						
						$('#lumise-product-form .lumise-field-layout-items').sortable({
							//items : '>li:not([data-add="tab"])',
							handle: 'a[data-act="arrange"]',
							update: function(e) {
								edata.refresh_variations(e);
							}
						});
						
					},
					
					add_variation_events: function(e) {
						
						$('#lumise-product-form .lumise-att-layout-item:not([data-event="added"])').each(function() {
							
							this.setAttribute('data-event', 'added');
							this.edata = e.data;
							
							trigger({
								
								el: $(this),
								
								events: {
									'input[data-cfgstages]:change': 'cfgstages',
									'input[data-cfgprinting]:change': 'cfgprinting'
								},
								
								cfgstages: function(e) {
									
									var wrp = $(this).closest('.att-layout-body-field').find('.att-layout-cfgstages');
									
									if (this.checked !== true) {
										wrp.slideToggle(250, function() {
											$(this).html('').hide();
										});
									} else {
										lumise.product.clone_stages(wrp);
									}
								},
								
								cfgprinting: function(e) {
									
									var wrp = $(this).closest('.att-layout-body-field').find('.att-layout-cfgprinting');
									
									if (this.checked !== true) {
										wrp.slideToggle(250, function() {
											$(this).html('').hide();
										});
									} else {
										lumise.product.clone_printing(wrp);
									}
								}
								
							});
							
						});
						
						$('#lumise-product-form .lumise-field-layout-items').sortable({
							handle: 'a[data-act="arrange"]'
						});
						
					},
					
					bulk_edit_variation: function(e) {
						
						var attrs = lumise.product.get_attributes(true),
							condi = '';
						
						if (Object.keys(attrs).length > 0) {

							Object.keys(attrs).map(function(k) {
								var ops = '<option value="">Any '+esc_html(attrs[k].name)+'</option>';
								if (
									attrs[k].values !== undefined && 
									attrs[k].values !== ''
								) {
									
									if (typeof attrs[k].values == 'string') {
										try {
											attrs[k].values = JSON.parse(attrs[k].values);
										} catch (ex) {
											attrs[k].values = [];
										}
									};
									
									if (
										typeof attrs[k].values == 'object' &&
										typeof attrs[k].values.options == 'object'
									) {
										attrs[k].values.options.map(function(op) {
											ops += '<option value="'+esc_html(op.value)+'">'+esc_html(op.title !== undefined ? op.title.trim() : op.value)+'</option>';
										});
									}
								};
								
								condi += '<select data-name="'+k+'">'+ops+'</select>';
									
							});
						
						};
						
						lightbox({
							width: 750,
							content: '<div class="lumise-bulk-form">\
									<h3>'+LumiseDesign.js_lang[203]+'</h3>\
									<p class="lumise-update-notice success hidden" style="width: 100%;"></p>\
									<p data-view="conditions">\
										<label>'+LumiseDesign.js_lang[200]+':</label>\
										'+condi+'\
									</p>\
									<p>\
										<label>'+LumiseDesign.js_lang[201]+':</label>\
										<select name="apply-for">\
											<option value="price">'+LumiseDesign.js_lang[104]+'</option>\
											<option value="min-qty">'+LumiseDesign.js_lang[205]+'</option>\
											<option value="max-qty">'+LumiseDesign.js_lang[206]+'</option>\
											<option value="description">'+LumiseDesign.js_lang[207]+'</option>\
										</select>\
									</p>\
									<p>\
										<label>'+LumiseDesign.js_lang[202]+':</label>\
										<textarea name="new-value" style="width: 550px;height: 120px;"></textarea>\
									</p>\
									<hr>\
									<p><center><button class="apply lumise-button lumise-button-primary lumise-button-large">'+LumiseDesign.js_lang.apply+'</button> &nbsp; <button class="cancel lumise-button lumise-button-large">'+LumiseDesign.js_lang.cancel+'</button></center></p>\
								</div>'
						});	
						
						trigger({
							el: $('#lumise-lightbox'),
							events: {
								'button.apply': 'apply',
								'button.cancel': 'cancel',
								'select:focus,textarea:focus': 'remove_notice'
							},
							apply: function(e) {
								
								var conditions = {},
									apply_for = e.data.el.find('select[name="apply-for"]').val(),
									new_value = e.data.el.find('textarea[name="new-value"]').val(),
									count = 0;
								
								e.data.el.find('p[data-view="conditions"] select').each(function() {
									conditions[this.getAttribute('data-name')] = $(this).val();
								});
								
								$('#lumise-field-variations-items>div.lumise-att-layout-item').each(function() {
									
									var valid = true;
									
									$(this).find('.att-layout-conditions select[data-name]').each(function() {
										
										var n = this.getAttribute('data-name');
										
										if (
											valid === true &&
											(
												conditions[n] === undefined ||
												(
													conditions[n] !== '' &&
													conditions[n] != $(this).val()
												)
											)
										) valid = false;
						
									});
									
									if (valid === true) {
										count ++;
										$(this).find('[data-name="'+apply_for+'"]').val(new_value).trigger('change');
									}
									
								});
								
								e.data.el.find('.lumise-update-notice').
									removeClass('hidden').
									html(LumiseDesign.js_lang[204].replace('%s', count));
								
							},
							cancel: function(e) {
								$('#lumise-lightbox').remove();
								e.preventDefault();
							},
							remove_notice: function(e) {
								e.data.el.find('.lumise-update-notice').addClass('hidden');
							}
						});
						
						e.preventDefault();
						
					},
					
					add_att: function(el) {
						
						var wrp = $(el).closest('.lumise-att-layout'),
							tmpl = wrp.find('div.lumise-att-layout-tmpl>div.lumise-att-layout-item').clone(),
							body = wrp.find('.lumise-field-layout-items'),
							id = new Date().getTime().toString(36).substr(4).toUpperCase();
						
						wrp.find('.lumise-att-layout-item>.att-layout-body')
							.hide()
							.closest('.lumise-att-layout-item')
							.find('.att-layout-headitem i[data-act="toggle"]')
							.removeClass('fa-caret-up')
							.addClass('fa-caret-down');
						
						body.append(tmpl);
						
						tmpl.attr({'data-id': id})
							.removeAttr('data-event')
							.find('div.att-layout-body')
							.show()
							.find('input').first().focus()
							.closest('.lumise-att-layout-item')
							.find('.att-layout-headitem i[data-act="toggle"]')
							.removeClass('fa-caret-down')
							.addClass('fa-caret-up');
						
						return tmpl;
						
					},
					
					add_attribute : function(e) {
						
						var tmpl = e.data.add_att($(e.target).closest('.lumise-att-layout')),
							id = tmpl.attr('data-id');
							
						tmpl.find('[data-field="use_variation"] label').attr({'for': 'use-var-'+id});
						tmpl.find('[data-field="use_variation"] input').attr({'id': 'use-var-'+id});
						
						tmpl.find('[data-field="required"] label').attr({'for': 'required-'+id});
						tmpl.find('[data-field="required"] input').attr({'id': 'required-'+id});
						
						lumise.product.render_values(tmpl.find('div[data-field="values"]'), 'input');
						
						e.data.add_attribute_events(e);
						e.preventDefault();
						
					},
					
					add_variation : function(e) {
						
						var tmpl = e.data.add_att($(e.target).closest('.lumise-att-layout')),
							id = tmpl.attr('data-id');
						
						tmpl.find('.att-layout-conditions>strong').html('#'+id);
						tmpl.find('.att-layout-conditions').append(
							$(e.target).closest('.lumise-att-layout')
							.find('.lumise-att-layout-default .att-layout-conditions select')
							.clone()
						);
						tmpl.find('.att-layout-conditions select').each(function() {
							$(this).find('option').first().prop({selected: true});
						});
						
						tmpl.find('input[data-cfgstages="no"]').attr({id: 'custom-config-no-'+id, name: 'cfgstages-'+id}).prop({checked: true});
						tmpl.find('label[data-cfgstages="no"]').attr({'for': 'custom-config-no-'+id});
						tmpl.find('input[data-cfgstages="yes"]').attr({id: 'custom-config-yes-'+id, name: 'cfgstages-'+id});
						tmpl.find('label[data-cfgstages="yes"]').attr({'for': 'custom-config-yes-'+id});
						
						$(e.target).closest('.lumise-att-layout').find('.lumise-att-layout-default').removeClass('hidden');
						
						e.data.add_variation_events(e);
						e.data.refresh_variations(e);
						e.preventDefault();
						
					},
					
					expand: function(e) {
						
						e.preventDefault();
						
						$(e.target).closest('.lumise-att-layout')
							.find('.att-layout-body')
							.show()
							.closest('.lumise-att-layout-item')
							.find('.att-layout-headitem i[data-act="toggle"]')
							.removeClass('fa-caret-down')
							.addClass('fa-caret-up');
						
					},
					
					close: function(e) {
						
						e.preventDefault();
						
						$(e.target).closest('.lumise-att-layout')
							.find('.att-layout-body')
							.hide()
							.closest('.lumise-att-layout-item')
							.find('.att-layout-headitem i[data-act="toggle"]')
							.removeClass('fa-caret-up')
							.addClass('fa-caret-down');
						
					},
						
					load_attributes: function(e) {
						
						var edata = e.data,
							wrp = $('#lumise-tab-attributes .lumise-att-layout'),
							data = $('#lumise-field-attributes-inp').val();
						
						if (data === undefined || data === '')
							return;
						
						data = dejson(data.trim());
						
						Object.keys(data).map(function(id) {
							
							var tmpl = e.data.add_att(wrp),
								attr = data[id];
							
							lumise.product.render_values(
								tmpl.find('div[data-field="values"]'), 
								attr.type != '' ? attr.type : 'input',
								attr.values
							);
							
							if (/^\d+$/.test(id))
								id = new Date().getTime().toString(36).substr(4).toUpperCase();
								
							tmpl.attr({'data-id': id});	
							tmpl.find('[data-field="use_variation"] label').attr({'for': 'use-var-'+id});
							tmpl.find('[data-field="use_variation"] input').attr({'id': 'use-var-'+id});
							
							tmpl.find('[data-field="required"] label').attr({'for': 'required-'+id});
							tmpl.find('[data-field="required"] input').attr({'id': 'required-'+id});
							
							tmpl.find('.att-layout-headitem strong[data-name]').html(attr.name);
							tmpl.find('input[data-name="name"]').val(attr.name);
							tmpl.find('select[data-name="type"]').val(attr.type);
							tmpl.find('.att-layout-headitem em[data-view="attr-type"]').html(tmpl.find('select[data-name="type"] option:selected').text());
							tmpl.find('input[data-name="use_variation"]').prop({checked: attr.use_variation});
							tmpl.find('input[data-name="required"]').prop({checked: attr.required});
							tmpl.find('textarea[data-name="values"]').val(
								typeof attr.values == 'object' ? JSON.stringify(attr.values) : attr.values
							);
							
							edata.add_attribute_events(e);
							
							if (attr.use_variation)
								tmpl.find('input[data-name="use_variation"]').change();
							
						});
						
					},
					
					load_variations: function(e) {
						
						var edata = e.data,
							wrp = $('#lumise-tab-variations .lumise-att-layout'),
							data = $('#lumise-field-variations-inp').val();
						
						if (data === undefined || data === '')
							return;
							
						data = dejson(data.trim());
						
						Object.keys(data.variations).map(function(id) {
							
							var tmpl = e.data.add_att(wrp),
								vari = data.variations[id];
							
							tmpl.attr({'data-id': id}).find('.att-layout-conditions>strong').html('#'+id);
							tmpl.find('input[data-cfgstages="no"]').attr({id: 'custom-config-no-'+id, name: 'cfgstages-'+id});
							tmpl.find('label[data-cfgstages="no"]').attr({'for': 'custom-config-no-'+id});
							tmpl.find('input[data-cfgstages="yes"]').attr({id: 'custom-config-yes-'+id, name: 'cfgstages-'+id});
							tmpl.find('label[data-cfgstages="yes"]').attr({'for': 'custom-config-yes-'+id});
						
							tmpl.find('input[data-name="price"]').val(vari.price);
							tmpl.find('input[data-name="min-qty"]').val(vari.minqty);
							tmpl.find('input[data-name="max-qty"]').val(vari.maxqty);
							tmpl.find('textarea[data-name="description"]').val(vari.description);
							tmpl.find('input[data-name="cfgstages"]').prop({checked: vari.cfgstages});
							tmpl.find('input[data-name="cfgprinting"]').prop({checked: vari.cfgprinting});
							
							Object.keys(vari.conditions).map(function(c) {
								tmpl.find('.att-layout-conditions').append(
									'<select data-name="'+c+'">\
										<option selected value="'+vari.conditions[c]+'"></option>\
									</select>'
								);
							});
							
							if (vari.stages !== null && typeof vari.stages == 'object') {
								lumise.product.clone_stages(tmpl.find('.att-layout-cfgstages'), vari.stages);
							}
							
							if (vari.printings !== null && typeof vari.printings == 'object') {
								lumise.product.clone_printing(tmpl.find('.att-layout-cfgprinting'), vari.printings);
							}
							
							edata.add_variation_events(e);
							
						});
						
						Object.keys(data.default).map(function(id) {
							wrp.find('.lumise-att-layout-default .att-layout-conditions').append(
								'<select data-name="'+id+'"><option selected value="'+data.default[id]+'"></option></select>'
							);
						});
						
						delete window.old_refresh_variations;
						edata.refresh_variations(e);
						
					},
					
					refresh_variations: function(e) {
						
						var attrs = lumise.product.get_attributes(true)
						
						if (
							window.old_refresh_variations !== undefined &&
							window.old_refresh_variations == JSON.stringify(attrs)
						) return;
						
						window.old_refresh_variations = JSON.stringify(attrs);
						
						var vars = $('#lumise-variations'),
							items = vars.find('.lumise-field-layout-items>.lumise-att-layout-item'),
							current = {};
							
						vars.find('.lumise-att-layout-default select').each(function() {
							current[this.getAttribute('data-name')] = this.value;
						});
						vars.find('.lumise-att-layout-default select').remove();
						
						if (Object.keys(attrs).length === 0) {
							
							items.remove();
							
							vars.find('.lumise-att-layout-default').addClass('hidden');
							vars.find('[data-act="add_variation"], a[data-act], [data-act="bulk_edit_variation"]').hide();
							vars.find('p[data-view="notice"]').show();
							
						} else {
							
							vars.find('[data-act="add_variation"], a[data-act], [data-act="bulk_edit_variation"]').show();
							vars.find('p[data-view="notice"]').hide();
							
							Object.keys(attrs).map(function(k) {
								var ops = '<option value="">Any '+esc_html(attrs[k].name)+'</option>';
								if (
									attrs[k].values !== undefined && 
									attrs[k].values !== ''
								) {
									
									if (typeof attrs[k].values == 'string') {
										try {
											attrs[k].values = JSON.parse(attrs[k].values);
										} catch (ex) {
											attrs[k].values = [];
										}
									};
									
									if (
										typeof attrs[k].values == 'object' &&
										typeof attrs[k].values.options == 'object'
									) {
										attrs[k].values.options.map(function(op) {
											ops += '<option '+(
												current[k] !== undefined && current[k] == op.value ? 'selected' : ''
											)+' value="'+esc_html(op.value)+'">'+esc_html(op.title !== undefined ? op.title.trim() : op.value)+'</option>';
										});
									}
								};
								vars.find('.lumise-att-layout-default .att-layout-conditions').
									append('<select data-name="'+k+'">'+ops+'</select>');
									
							});
							
							if (items.length > 0)
								vars.find('.lumise-att-layout-default').removeClass('hidden');
							else vars.find('.lumise-att-layout-default').addClass('hidden');
							
							items.each(function() {
								
								var current = {},
									_this = $(this);
								
								_this.find('.att-layout-conditions select').each(function() {
									current[this.getAttribute('data-name')] = this.value;
								});
								_this.find('.att-layout-conditions select').remove();
								Object.keys(attrs).map(function(k) {
									
									var ops = '<option value="">Any '+esc_html(attrs[k].name)+'</option>';
									
									if (
										attrs[k].values !== undefined &&
										attrs[k].values !== ''
									) {
										
										if (typeof attrs[k].values == 'string') {
											try {
												attrs[k].values = JSON.parse(attrs[k].values);
											} catch (ex) {
												attrs[k].values = {
													options: []
												};
											}
										};
										
										if (
											typeof attrs[k].values == 'object' &&
											typeof attrs[k].values.options == 'object'
										) {
											attrs[k].values.options.map(function(op) {
												ops += '<option '+(
													current[k] !== undefined && current[k] == op.value ? 'selected' : ''
												)+' value="'+esc_html(op.value)+'">'+esc_html(op.title !== undefined ? op.title.trim() : op.value)+'</option>';
											});
										}
										
									};
									_this.find('.att-layout-conditions').
										append('<select data-name="'+k+'">'+ops+'</select>');
								});
							});
							
						}
						
					}
					
				}, 'varattr');
					
			},
			
			render_designs: function(res) {
				
				var _this = this,
					cates = [
						'<ul data-view="categories">',
						'<h3>'+lumise.i(90)+'</h3>',
						'<li data-id="" '+(res.category === '' ? 'class="active"' : '')+' data-lv="0"> '+lumise.i(57)+'</li>'
					],
					prods = [
						'<h3 data-view="top">'+lumise.i(91)+'<input id="search-templates-inp" type="search" placeholder="'+lumise.i(92)+'" value="'+encodeURIComponent(res.q)+'" /></h3>',
						'<ul data-view="items">'
					];
				
				if (res.categories_full) {
					res.categories_full.map(function(c) {
						cates.push('<li data-id="'+c.id+'" '+(c.id == res.category ? 'class="active"' : '')+' data-lv="'+(c.lv ? c.lv : 0)+'">'+('&mdash;'.repeat(c.lv))+' '+c.name+'</li>');
					});
				};
	
				if (res.items && res.items.length > 0) {
					var current_design = $('#lumise_template').val();
					window.ops_designs = res.items;
					res.items.map(function(p) {
							
						prods.push(
							'<li data-id="'+p.id+'"'+((current_design == p.id)?' data-current="true"':'')+'>\
								<span data-view="thumbn" data-start="'+lumise.i(93)+'">\
									<img src="'+p.screenshot+'" />\
								</span>\
								<span data-view="name">'+p.name+'</span>\
							</li>'
						);
					});
					
					if (res.index+res.limit < res.total) {
						prods.push(
							'<li data-loadmore="'+(res.index+res.limit)+'">\
								<span>'+lumise.i(94)+'</span>\
							</li>'
						);
					}
					
				}
				else prods.push('<li data-view="noitem" data-category="'+res.category+'">'+lumise.i(42)+'</li>');
				
					
				if (res.index == 0) {
					
					cates.push('</ul>');
					prods.push('</ul>');
					
					$('#lumise-lightbox-content').html('<div id="lumise-list-items-wrp"></div>');
					$('#lumise-list-items-wrp').html(cates.join('')).append(prods.join(''));
					
				}else{
					
					$('#lumise-lightbox-content ul[data-view="items"] li[data-loadmore]').remove();
					prods[0] = '';
					prods[1] = '';
					$('#lumise-lightbox-content ul[data-view="items"]').append(prods.join(''));
				}
				
				trigger({
					
					el: $('#lumise-list-items-wrp'),
					
					events: {
						'ul[data-view="categories"] li': 'category',
						'ul[data-view="items"] li': 'design',
						'h3[data-view="top"] input:keyup': 'search',
						'li[data-loadmore]': 'load_more',
					},
					
					category: function(e) {
	
						lumise.product.load_designs({
							category: this.getAttribute('data-id'), 
							index: 0, 
							q: $('#search-templates-inp').val()
						});
						
						e.preventDefault();
						
					},
					
					design: function(e) {
						
						if (this.getAttribute('data-loadmore') !== null)
							return e.data.load_more(e);
						
						var id = this.getAttribute('data-id'),
							design = ops_designs.filter(function(p){return p.id == id;});
						
						$(this).closest('#lumise-lightbox').remove();
						popup_actions('close');
						if($('body.LumiseDesign').width() > 1000){
							$('body').css({overflow: ''});
						}
						
						lumise.product.render_design(design[0]);
						
					},
					
					load_more: function(e) {
						
						this.innerHTML = '<i class="lumise-spinner x3"></i>';
						this.style.background = 'transparent';
						$(this).off('click');
						
						lumise.product.load_designs({
							category: this.getAttribute('data-category'),
							index: this.getAttribute('data-loadmore'),
							q: $('#search-templates-inp').val()
						});
							
					},
					
					search: function(e) {
						
						if (e.keyCode !== undefined && e.keyCode === 13)
							lumise.product.load_designs({q: this.value});
						
					}
					
				});
	
			},
			
			render_design: function(data) {
				
				var view = $('#lumise-stages-wrp .lumise_tab_content.active .lumise-stage-editzone');
				
				if (view.length === 0)
					return;
				
				var img = new Image();
				img.src = data.screenshot;
				
				view.find('.design-template-inner,.design-template-btn').remove();
				view.append('<div class="design-template-inner" style="border-radius:'+view.css('border-radius')+'" data-id="'+data.id+'"></div>');
				
				view.find('button[data-func="clear-design"]').css({display: ''});
				
				view.find('.design-template-inner').append(img);
				
				img.onload = function(){
					
					if (this.width > this.parentNode.offsetWidth) {
						this.width = this.parentNode.offsetWidth;
						this.height = this.parentNode.offsetWidth*(this.naturalHeight/this.naturalWidth);
					};
					
					this.style.left = ((this.parentNode.offsetWidth/2)-(this.width/2))+'px';
					this.style.top = ((this.parentNode.offsetHeight/2)-(this.height/2))+'px';
					
					var rang = $(this).closest('.lumise-stage-design-view').find('.editzone-ranges .design-scale');
					
					rang.show();
					
					rang.find('input').val((this.width/this.naturalWidth)*100).trigger('input');
					
					lumise.product.update_pos($(this).closest('div.lumise-stage-design-view'));
					
				};
				
			},
			
			load_designs: function(ops) {
				
				if (ops.index === undefined || ops.index === 0) {
					lightbox({
						content: '<center><i class="lumise-spinner x3"></i></center>'
					});
				};
				
				$.ajax({
					url: window.parent && window.parent.lumisejs && window.parent.lumisejs.is_admin === false ? window.parent.lumisejs.admin_ajax_url : LumiseDesign.ajax,
					method: 'POST',
					data: {
						nonce: window.parent && window.parent.lumisejs && window.parent.lumisejs.is_admin === false ? 'LUMISE-SECURITY-BACKEND:'+window.parent.lumisejs.nonce_backend : 'LUMISE_ADMIN:'+LumiseDesign.nonce,
						ajax: 'backend',
						action: 'templates',
						category: ops.category !== undefined ? ops.category : '',
						q: ops.q !== undefined ? ops.q : '',
						index: ops.index !== undefined ? ops.index : 0
					},
					statusCode: {
						403: function(){
							alert(LumiseDesign.js_lang.error_403);
						}
					},
					success: lumise.product.render_designs
				});
				
			},
			
			set_image: function(url, source) {
				
				var stage = $('#lumise-popup').hide().attr('data-stage'),
					wrp = $('#lumise-product-design-'+stage);
				
				popup_actions('close');
				
				if (url.indexOf("image/svg+xml") > -1 || url.split('.').pop() == 'svg')
					wrp.find('img.lumise-stage-image').attr({'data-svg': '1'});
				else wrp.find('img.lumise-stage-image').attr({'data-svg': ''});
				
				var _url = url;
				
				if (source == 'raws')
					_url = lumise_assets_url+'raws/'+url;
				else if (url.indexOf('data:image/') === -1)
					_url = url.match(/^(?:http|https):\/\//gm) ? url: lumise_upload_url+url;
				
				if (_url.split('.').pop() == 'jpg' || _url.indexOf('data:image/jpeg') === 0)
					wrp.find('input[name="is_mask"]').prop({checked: false});
				console.log(wrp.find('img.lumise-stage-image'));	
				wrp.find('img.lumise-stage-image').attr({
					'src': _url, 
					'data-url': url, 
					'data-source': source
				}).off('load').on('load', function() {
					
					var edz = wrp.find('div.lumise-stage-editzone'),
						img = wrp.find('.lumise-stage-image');
					
					edz.css({left: '', top: '', height: '', width: ''});
					wrp.addClass('stage-enabled').removeClass('stage-disabled');
					
					if (img.height() <= 280) {
						edz.css({
							top: '10px', 
							height: (wrp.find('.lumise-stage-image').height()-20)+'px'
						});
						edz.css({
							width: edz.width()+'px',
							left: edz.get(0).offsetLeft+'px'
						});
					};
					
					if (img.width() <= 175) {
						edz.css({
							left: '10px', 
							width: (img.width()-20)+'px'
						});
						edz.css({
							height: edz.height()+'px',
							top: edz.get(0).offsetTop+'px'
						});
					};
					
					if (wrp.find('input[name="old-product-upload-'+stage+'"]').val() !== '')
						wrp.find('button[data-btn="revert"]').show();
					
					$('html,body').scrollTop($('#lumise-stages-wrp').offset().top+30);
					
				});
					
			},
			
			upload_images_submit: function(stages, variations) {
				
				var maps = {},
					formData = new FormData();
					id = '';
				
				if (typeof stages == 'object') {
					Object.keys(stages).map(function(s) {
						if (
							stages[s].url.indexOf('data:image/') > -1 &&
							stages[s].source == 'uploads'
						) {
							id = new Date().getTime().toString(36);
							maps[id] = {
								type: 'stages',
								stage: s
							};
							formData.append(id, new Blob([stages[s].url]));
						}
					});
				};
				
				if (typeof variations == 'object' && typeof variations.variations == 'object') {
					Object.keys(variations.variations).map(function(v) {
						if (variations.variations[v].cfgstages && typeof variations.variations[v].stages == 'object') {
							Object.keys(variations.variations[v].stages).map(function(s) {
								if (
									variations.variations[v].stages[s].url.indexOf('data:image/') > -1 &&
									variations.variations[v].stages[s].source == 'uploads'
								) {
									id = new Date().getTime().toString(36);
									maps[id] = {
										type: 'variations',
										variation: v,
										stage: s
									};
									formData.append(id, new Blob([variations.variations[v].stages[s].url]));
								}
							});
						}
					});
				};
				
				if (Object.keys(maps).length === 0)
					return true;
				
				formData.append('action', 'upload_product_images'); 
				formData.append('nonce', 'LUMISE_ADMIN:'+LumiseDesign.nonce); 
				
				$('#lumise-product-form input[type="submit"]').hide().after('<button disabled="true" class="lumise-btn" id="lumise-product-form-submitting"><i class="fa fa-spin fa-spinner"></i> Uploading..</button>');
				
				$.ajax({
				    data	:	 formData,
				    type	:	 "POST",
				    url		:	 LumiseDesign.ajax,
				    contentType: false,
				    processData: false,
				    xhr		:	 function() {
					    var xhr = new window.XMLHttpRequest();
					    xhr.upload.addEventListener("progress", function(evt){
						    
						    if (evt.lengthComputable) {
						        var percentComplete = evt.loaded / evt.total;
						        if (percentComplete < 1)
						       		$('#lumise-product-form-submitting').html(
						       			'<i class="fa fa-spin fa-spinner"></i> '+
						       			parseInt(percentComplete*100)+'% upload complete'
						       		);
						        else $('#lumise-product-form-submitting').html('Submiting..');
						    }
						    
					    }, false);
					    return xhr;
					},
				    success: function (res, status) {
					    
					    if (res.indexOf('Error') === 0) {
						    alert(res);
						    return;
					    };
					    
					    res = JSON.parse(res);
					    
					    Object.keys(res).map(function(r) {
						    if (maps[r] !== undefined) {
							    if (maps[r].type == 'stages') {
								    stages[maps[r].stage].url = res[r];
							    } else if (maps[r].type == 'variations') {
								    variations.variations[maps[r].variation].stages[maps[r].stage].url = res[r];
							    }
						    }
					    });
					    
					    $('textarea#lumise-field-stages-inp').val(enjson(stages));
					    $('textarea#lumise-field-variations-inp').val(enjson(variations));
					    
					    $('#lumise-product-form').off('submit').submit();
					    
				    },
				    error: function() {
					    alert('Error: could not upload images this time, please try again later');
				    }
				});
				
				return false;
				
			},
			
			upload: function(ops) {
				
				$('#lumise-stages-upload-helper').get(0).cb = function(e) {
					
					if (
						this.file.type.indexOf('image/') !== 0 &&
						this.file.name.indexOf('.jpg') != this.file.name.length-4 &&
						this.file.name.indexOf('.jpeg') != this.file.name.length-5 &&
						this.file.name.indexOf('.png') != this.file.name.length-4 &&
						this.file.name.indexOf('.svg') != this.file.name.length-4
					) {
						if (
							ops.type !== undefined &&
							ops.type.value.indexOf(this.file.type) === -1
						) {
							alert(ops.type.err_msg+this.file.type);
						}
						return;
					}
					
					if (
						ops.type !== undefined &&
						ops.type.value.indexOf(this.file.type) === -1
					) {
						alert(ops.type.err_msg+this.file.type);
						return;
					}
					
					if (
						ops.min_size !== undefined &&
						this.file.size < ops.min_size.value
					) {
						alert(ops.min_size.err_msg+Math.round(this.file.size/1024)+'KB');
						return;
					}
					
					if (
						ops.max_size !== undefined &&
						this.file.size > ops.max_size.value
					) {
						alert(ops.max_size.err_msg+Math.round(this.file.size/1024)+'KB');
						return;
					}
					
					if (e.target.result.indexOf('data:image/svg+xml') === 0) {
						return ops.callback(lumise.svguni(e.target.result));
					}
					
					var img = new Image();
								
					img.onload = function() {
						
						if (
							typeof ops.fit != 'object' &&
							ops.height === undefined &&
							ops.width === undefined &&
							(
								ops.max_height === undefined || 
								ops.max_height >= this.height
							)
							&&
							(
								ops.max_width === undefined || 
								ops.max_width >= this.width
							)
						)return ops.callback(e.target.result);
							
						var cv = document.createElement('canvas'),
							w = ops.width !== undefined ? 
								ops.width : 
								(
									ops.max_width === undefined || 
									this.width < ops.max_width ? 
									this.width : 
									ops.max_width
								),
							h = w*(this.height/this.width),
							l = 0, t = 0;
						
						if (ops.max_width !== undefined && w > ops.max_width) {
							w = ops.max_width;
							h = w*(this.height/this.width);
						};
						
						if (ops.max_height !== undefined && h > ops.max_height) {
							w = ops.max_height*(w/h);
							h = ops.max_height;
						};
						
						if (ops.min_width !== undefined && w < ops.min_width) {
							h = ops.min_width*(h/w);
							w = ops.min_width;
						};
						
						if (ops.min_height !== undefined && h < ops.min_height) {
							w = ops.min_height*(w/h);
							h = ops.min_height;
						};
						
						
						
						if (typeof ops.fit == 'object') {
							
							w = ops.fit[0];
							h = ops.fit[1];
							
							if (this.width*(h/w) <= h) {
								cv.width = this.width;
								cv.height = this.width*(h/w);
								l = 0;
								t = -(this.height-cv.height)/2;
							} else {
								cv.height = this.height;
								cv.width = this.height*(w/h);
								t = 0;
								l =  -(this.width-cv.width)/2;
							};
							
							var ctx = cv.getContext('2d');
							
							ctx.drawImage(this, l, t);
						
						} else {
						
							cv.width = this.width;
							cv.height = this.height;
							
							var ctx = cv.getContext('2d');
							ctx.drawImage(this, 0, 0);
						
						};
						
						w = parseInt(w);
						h = parseInt(h);
						
						
						HERMITE.resample_single(cv, w, h, true);
						
						ops.callback(
							cv.toDataURL(
								e.target.result.indexOf('data:image/png') === 0 ? 
								'image/png' : 
								'image/jpeg', 
								ops.quanlity !== undefined ? ops.quanlity : 1
							)
						);
						
						delete cv;
						delete img;
						
					};
					
					img.src = e.target.result;
					
				};
				$('#lumise-stages-upload-helper').click();
			},
			
			get_attributes: function(is_vari) {
				
				var data = {}, 
					type,
					el,
					color = '#f0f0f0';
				
				if (
					$('div#lumise-stages-wrp div.fill-base-color').length > 0 &&
					$('div#lumise-stages-wrp div.fill-base-color')[0].value !== ''
				) {
					color = $('div#lumise-stages-wrp div.fill-base-color')[0].value;
				};
				
				$('#lumise-field-attributes-items>.lumise-att-layout-item').each(function() {
					
					el = $(this);
					type = el.find('select[data-name="type"]').val();
					
					if (
						is_vari === undefined ||
						(
							is_vari === true && 
							el.find('input[data-name="use_variation"]').prop('checked') === true
						)
					) {
						
						data[el.attr('data-id')] = {
							id: el.attr('data-id'),
							name: el.find('input[data-name="name"]').val(),
							type: type,
							required: el.find('input[data-name="required"]').prop('checked'),
							use_variation: el.find('input[data-name="use_variation"]').prop('checked'),
							values: el.find('textarea[data-name="values"]').val().trim()
						};
						
					};
					
					if (type == 'product_color') {
						var val_color = el.find('textarea[data-name="values"]').val().trim();
						if (val_color !== '') {
							try {
								val_color = JSON.parse(val_color);
								if (val_color.options.length > 0)
									color = val_color.options[0].value;
								val_color.options.map(function(o) {
									if (o.default)
										color = o.value;
								});
							} catch (ex) {};
						}
					}
					
				});
				
				$('.lumise-stage-editzone').css({'border-color': lumise.invert_color(color), 'color': lumise.invert_color(color)});
				$('div.lumise-stage-body .lumise-stage-design-view').css({background: color});
						
				return data;
				
			},
			
			get_variations: function() {
				
				var data = {
					default: {},
					attrs: [],
					variations: {}
				}, is_default = false;
				
				$('#lumise-variations .lumise-att-layout-default select').each(function() {
					data.default[this.getAttribute('data-name')] = this.value;
					data.attrs.push(this.getAttribute('data-name'));
					if (this.value !== '')
						is_default = true;
				});
				
				if (is_default === false)
					data.default = '';
					
				$('#lumise-field-variations-items>.lumise-att-layout-item').each(function() {
					
					var condi = {}, 
						wrp = $(this),
						id = wrp.attr('data-id');
						
					wrp.find('.att-layout-headitem .att-layout-conditions select').each(function() {
						condi[this.getAttribute('data-name')] = this.value;
					});
					
					data.variations[id] = {
						id: id,
						conditions: condi,
						price: wrp.find('input[data-name="price"]').val(),
						sku: wrp.find('input[data-name="sku"]').val(),
						minqty: wrp.find('input[data-name="min-qty"]').val(),
						maxqty: wrp.find('input[data-name="max-qty"]').val(),
						description: wrp.find('textarea[data-name="description"]').val(),
						cfgstages: wrp.find('input[data-name="cfgstages"]:checked').prop('checked'),
						cfgprinting: wrp.find('input[data-name="cfgprinting"]:checked').prop('checked'),
						stages: null,
						printings: null
					};
					
					if (data.variations[id].cfgstages)
						data.variations[id].stages = lumise.product.get_stages(wrp.find('div.lumise-stages-wrp'));
					if (data.variations[id].cfgprinting)
						data.variations[id].printings = lumise.product.get_printing(wrp.find('div.att-layout-cfgprinting'));
					
				});
				
				return data;
				
			},
			
			get_stages: function(wrp) {
				
				var data = {};
				
				/*
				*	Has any product image?
				*/
				
				var has_stage = false;
				
				wrp.find('.lumise_tab_content img.lumise-stage-image').each(function(){
					if (this.getAttribute('data-url') !== '')
						has_stage = true;
				});
				
				if (has_stage === false) {
					alert(cfg.hs);
					return false;
				};
			
				wrp.find('.lumise_tab_nav li:not([data-add]) a').each(function() {
				   
					let tab = $(this.getAttribute('href')),
				    	url = tab.find('img.lumise-stage-image').attr('data-url'),
						source = tab.find('img.lumise-stage-image').attr('data-source'),
						overlay = tab.find('input[name="is_mask"]').prop('checked'),
						pos = tab.find('input[name="pos"]').val(),
						ret = {},
						b = tab.find('.lumise-stage-design-view img.lumise-stage-image').get(0),
						l = tab.find('.lumise-stage-editzone').get(0),
						templ = {},
						stg = tab.attr('data-stage');
				   
				    try {
						pos = JSON.parse(pos);
					} catch (ex) {
						
						pos = {
							template : {},
							edit_zone : {
								height: l.offsetHeight,
								width: l.offsetWidth,
								left: l.offsetLeft-(b.offsetWidth/2)+(l.offsetWidth/2),
								top: l.offsetTop-(b.offsetHeight/2)+(l.offsetHeight/2)
							},
							product_width: b.offsetWidth,
							product_height: b.offsetHeight
						};
						
						if (
					   		tab.find('.design-template-inner').length > 0 && 
					   		tab.find('.design-template-inner').attr('data-id')
					   	) {
						   	var im = tab.find('.design-template-inner img').get(0);
							pos.template = {
								top: im.offsetTop,
								left: im.offsetLeft,
								width: im.offsetWidth,
								height: im.offsetHeight,
								natural_width: im.naturalWidth,
								natural_height: im.naturalHeight
							};
					   	};
					};
				   
				   	if (
				   		tab.find('.design-template-inner').length > 0 && 
				   		tab.find('.design-template-inner').attr('data-id')
				   	) {
					   	let im = tab.find('.design-template-inner img').get(0);
					   	templ = {
							id: tab.find('.design-template-inner').attr('data-id'),
							scale: tab.find('.design-scale input').val(),
							css: tab.find('.design-template-inner img').attr('style'),
							offset: pos.template
						};
				   	};
				   	
				   	let tab_thumbn = $(this).find('img[data-func="upload-thumbn"]'),
				   		size = tab.find('select[data-name="sizes"]').val(),
				   		include_base = tab.find('input[data-name="include_base"]').prop('checked'),
				   		crop_marks_bleed = tab.find('input[data-name="crop_marks_bleed"]').prop('checked'),
				   		bleed_range = tab.find('input[data-name="bleed_range"]').val();
				   	
				   	if (size == 'custom') {
					   	size = {
							width: tab.find('input[data-name="width"]').val(),
							height: tab.find('input[data-name="height"]').val(),
							constrain: tab.find('span.constrain-aspect-ratio').hasClass('active'),
							unit: tab.find('select[data-name="unit"]').val()
						}
				   	} else if (size !== '') {
					  	pos.edit_zone.width = pos.edit_zone.height*0.7069555302166477;
				   	};
				   	
				   	pos.edit_zone.radius = tab.find('.editzone-radius input').val();
					
					data[stg] = lumise.apply_filter('save_stage', {
						edit_zone: pos.edit_zone,
						url: url,
						source: source,
						overlay: overlay,
						product_width: pos.product_width,
						product_height: pos.product_height,
						template: templ,
						size: size,
						include_base: include_base,
						crop_marks_bleed: crop_marks_bleed,
						bleed_range: bleed_range,
						orientation: tab.find('select[data-name="orientation"]').val(),
						label: $(this).find('text').text(),
						thumbnail: tab_thumbn.attr('data-url') ? tab_thumbn.attr('data-url') : tab_thumbn.attr('src')
					}, tab);
					
					if (wrp.find('div.fill-base-color').length > 0) {
						data[stg].color = wrp.find('div.fill-base-color input')[0].value;
					};
					
				});
				
				data = lumise.apply_filter('save_stages', data, wrp);
				
				return data;
						
			},
			
			get_printing: function(wrp) {
				
				var vals = {};
				wrp.find('.lumise_checkbox').each(function(){
					if ($(this).find('input.action_check').prop('checked')) {
						var v = $(this).find('input.action_check').val();
						vals['_'+v] = '';
						if (this.getAttribute('data-type') == 'size') {
							vals['_'+v] = $(this).find('.lumise_radios input[type="radio"]:checked').val();
						}
					}
				});
				
				return vals;
				
			},
			
			render_values: function(wrp, type, values) {
				
				if (wrp.find('textarea[data-name="values"]').length > 0)
					var values = wrp.find('textarea[data-name="values"]').val();
				
				if (lumise_attribute_values_render[type] !== undefined) {
									
					if (typeof lumise_attribute_values_render[type] == 'string') {
						try {
							lumise_attribute_values_render[type] = Function(
								"wrp", "$", "lumise", "values", 
								lumise_attribute_values_render[type]
							);
						} catch (ex) {
							console.warn(ex);
						}
					}
							
					if (typeof lumise_attribute_values_render[type] == 'function') {
						lumise_attribute_values_render[type](
							wrp,
							$,
							lumise,
							values
						);
					}
					
				} else {
					if (typeof lumise_attribute_values_render._values == 'string') {
						try {
							lumise_attribute_values_render._values = Function(
								"wrp", "$", "lumise", "values", 
								lumise_attribute_values_render._values
							);
						} catch (ex) {
							console.warn(ex);
						};
					}
					if (typeof lumise_attribute_values_render._values == 'function') {
						lumise_attribute_values_render._values(
							wrp,
							$,
							lumise,
							values
						);
					}
				}
					
			},
			
			clone_stages: function(wrp, data) {
				
				$('#lumise-stages-wrp ul.lumise_tab_nav').sortable('destroy');
								
				var clone = $('#lumise-stages-wrp').clone(true);
				/*
				* Disable select design in variation
				*/
				clone.find('.design-template-inner,.design-scale, [data-func="select-design"], [data-func="clear-design"]').remove();
				
				clone.removeAttr('id').find('ul.lumise_tab_nav>li>a[data-label]').each(function(i){
				
					var id = Math.random().toString(36).substr(6);
					
					this.setAttribute('href', '#lumise-stage-'+id);
					
					clone.find('.lumise_tabs>.lumise_tab_content').eq(i).attr({
						id: 'lumise-stage-'+id,
						'data-stage': id
					}).find('div.lumise-stage-settings').attr({
						id: 'lumise-product-design-'+id
					});
					
				});
				
				wrp.hide().html('').append(clone).slideToggle(250);
				
				clone.find('ul.lumise_tab_nav').sortable({
					items : '>li:not([data-add="tab"])',
					tolerance: 'pointer'
				});
				$('#lumise-stages-wrp ul.lumise_tab_nav').sortable({
					items : '>li:not([data-add="tab"])',
					tolerance: 'pointer'
				});
				
				if (data !== undefined && data !== null && typeof data == 'object') {
					
					clone.find('ul.lumise_tab_nav>li:not([data-add]):not(:first-child)').remove();
					clone.find('div.lumise_tabs>div.lumise_tab_content:not(:first-child)').remove();
						
					Object.keys(data).map(function(id, i) {
						
						clone.find('ul.lumise_tab_nav>li[data-add]').click();
						
						if (i === 0) {
							clone.find('ul.lumise_tab_nav>li:not([data-add])').first().remove();
							clone.find('div.lumise_tabs>div.lumise_tab_content').first().remove();
						};
						
						var new_nav = clone.find('ul.lumise_tab_nav>li:not([data-add])').last(),
							new_body = clone.find('div.lumise_tabs>div.lumise_tab_content').last(),
							url = data[id].url;
						
						if (data[id].source == 'raws')
							_url = lumise_assets_url+'raws/'+data[id].url;
						else if (url.indexOf('data:image/') === -1)
							_url = lumise_upload_url+data[id].url;
						
						new_nav.find('a')
							.attr({
								 href: '#lumise-stage-'+id
							 })
							 .attr({
								 'data-label': data[id].label.replace(/\"/g, '%22')
							 })
							 .find('text').html(
							 	data[id].label.replace(/\>/g, '%3E').replace(/\</g, '%3C')
							 );
						
						new_body.find('img.lumise-stage-image').attr({
							src: _url, 
							'data-url': data[id].url, 
							'data-source': data[id].source
						});
						
						new_body.find('div.lumise-stage-editzone').css({
							height: data[id].edit_zone.height+'px',
							width: data[id].edit_zone.width+'px',
							left: (data[id].edit_zone.left+(data[id].product_width/2)-(data[id].edit_zone.width/2))+'px',
							top: (data[id].edit_zone.top+(data[id].product_height/2)-(data[id].edit_zone.height/2))+'px',
							borderRadius: data[id].edit_zone.radius+'px'
						});
						
						new_body.attr({'data-id': id, 'id': 'lumise-stage-'+id, 'data-stage': id})
								  .find('div.lumise-stage-settings')
								  .attr({id: 'lumise-product-design-'+id});
						
						new_body.find('.lumise-stage-body').attr({'data-is-mask': data[id].overlay ? 'true' : 'false'});
						
						var dsview = new_body.find('.lumise-stage-design-view');
						
						dsview.attr({
							'data-info': dsview.attr('data-info').split(':')[0]+': '
							+data[id].edit_zone.width+'x'+data[id].edit_zone.height
						}).find('input[name="is_mask"]').prop({checked: data[id].overlay});
						
						dsview.find('.editzone-radius input').val(data[id].edit_zone.radius);
						
						if (data[id].size !== '') {
							if (typeof data[id].size == 'object') {
								dsview.find('select[data-name="sizes"] option[value="custom"]').prop({selected: true});
								
								if (data[id].size.constrain === true)
									dsview.find('span.constrain-aspect-ratio').addClass('active');
								else dsview.find('span.constrain-aspect-ratio').removeClass('active');
								
								dsview.find('div.edr-row[data-row="values"],div.edr-row[data-row="unit"]').show();
								dsview.find('input[data-name="width"]').val(data[id].size.width);
								dsview.find('input[data-name="height"]').val(data[id].size.height);
								dsview.find('select[data-name="unit"] option[value="'+data[id].size.unit+'"]').prop({
									selected: true
								});
							} else {
								dsview.find('select[data-name="sizes"] option[value="'+data[id].size+'"]').prop({
									selected: true
								});
								dsview.find('div.edr-row[data-row="values"],div.edr-row[data-row="unit"]').hide();
							}
						} else {
							dsview.find('select[data-name="sizes"] option').first().prop({selected: true});
							dsview.find('div.edr-row[data-row="values"],div.edr-row[data-row="unit"]').hide();
						}
						
						new_body.find('input[name="is_mask"]').prop({checked: data[id].overlay === false ? false : true});
						
						dsview.find('select[data-name="orientation"] option[value="'+data[id].orientation+'"]').prop({selected: true});
						dsview.find('input[name="pos"]').val(JSON.stringify({
							template: data[id].template.offset !== undefined ? data[id].template.offset : {},
							edit_zone : data[id].edit_zone,
							product_width: data[id].product_width,
							product_height: data[id].product_height
						}));
						
					});
					
				}
				
			},
			
			clone_printing: function(wrp, data) {
				
				$('#lumise-tab-details .lumise_field_printing .lumise_checkboxes').sortable('destroy');
				
				var clone = $('#lumise-tab-details .lumise_field_printing .lumise_form_content').clone(true),
					uni = wrp.closest('.lumise-att-layout-item').attr('data-id');
				
				clone.find('.field_children').each(function() {
					$(this).find('.radio input[type="radio"]').each(function() {
						this.name += '-'+uni;
						this.id += '-'+uni;
						$(this).prop({checked: false});
					});
				});
				
				clone.find('.lumise_checkbox[data-type]').each(function() {
					$(this).find('input.action_check').prop({checked: false}).get(0).id += '-'+uni;
				});
				
				clone.find('label[for]').each(function() {
					this.setAttribute('for', this.getAttribute('for')+'-'+uni);
				});
				
				clone.find('em.notice, input.field-value').remove();
				
				wrp.hide().html('').append(clone).slideToggle(250);
				
				$('#lumise-tab-details .lumise_field_printing .lumise_checkboxes').sortable();
				clone.find('.lumise_checkboxes').sortable();
				
				if (data !== undefined && typeof data == 'object') {
					Object.keys(data).reverse().map(function(prnt, i) {
						var id = prnt.substr(1);
						clone.find('div.lumise_checkboxes').prepend(
							clone.find('input.action_check[value="'+id+'"]').prop({checked: true}).closest('div.lumise_checkbox')
						);
						if (data[prnt] !== '') {
							clone.find('.field_children[data-parent="'+id+'"] input[type="radio"][value="'+data[prnt]+'"]').prop({checked: true});
						}
					});
				}
				
			},
			
			update_pos: function(stage) {
				
				var e = stage.find('.lumise-stage-editzone').get(0),
					b = stage.find('img.lumise-stage-image').get(0),
					pos = {
						template : {},
						edit_zone : {
							height: e.offsetHeight,
							width: e.offsetWidth,
							left: e.offsetLeft-(b.offsetWidth/2)+(e.offsetWidth/2),
							top: e.offsetTop-(b.offsetHeight/2)+(e.offsetHeight/2)
						},
						product_width: b.offsetWidth,
						product_height: b.offsetHeight
					};
				
				if (
			   		stage.find('.design-template-inner').length > 0 && 
			   		stage.find('.design-template-inner').attr('data-id')
			   	) {
				   	var im = stage.find('.design-template-inner img').get(0);
					pos.template = {
						top: im.offsetTop,
						left: im.offsetLeft,
						width: im.offsetWidth,
						height: im.offsetHeight,
						natural_width: im.naturalWidth,
						natural_height: im.naturalHeight
					};
			   	};
				stage.find('input[name="pos"]').val(JSON.stringify(pos));
				
			}
			
		},
		
		template: {
			
			init: function(cfg) {
				
				if (cfg.bases !== undefined)
					lumise.product.bases = cfg.bases;
				
				trigger({
					
					el: $('#lumise-template-page'),
					
					events: {
						'#lumise-template-form:submit': 'before_submit_template'
					},
					
					before_submit_template: function(e) {
						
						var form = $(this),
							inp = form.find('#lumise-upload-input'), 
							old = form.find('#lumise-upload-input-old')
						
						if (inp.val() === '' || inp.val() == old.val()) {
							return true;
						}
						
						form.find('.lumise_form_submit *').hide();
						form.find('.lumise_form_submit').append('<button disabled="true" class="lumise-btn" id="lumise-files-form-submitting" style="margin-left: 180px;"><i class="fa fa-spin fa-spinner"></i> Uploading..</button>');
						
						
						var formData = new FormData();
						
						formData.append(inp.attr('name'), new Blob([btoa(encodeURIComponent(inp.val()))]));

						formData.append('action', 'upload_fields');
						formData.append('nonce', 'LUMISE_ADMIN:'+LumiseDesign.nonce); 
					
						$.ajax({
						    data	:	 formData,
						    type	:	 "POST",
						    url		:	 LumiseDesign.ajax,
						    contentType: false,
						    processData: false,
						    xhr: function() {
							    var xhr = new window.XMLHttpRequest();
							    xhr.upload.addEventListener("progress", function(evt){
							      if (evt.lengthComputable) {
							        var percentComplete = evt.loaded / evt.total,
							        	txt = '<i class="fa fa-spin fa-spinner"></i>  '+parseInt(percentComplete*100)+'% upload complete';
							        if (percentComplete === 1)
							        	txt = '<i class="fa fa-spin fa-refresh"></i> Submitting..';
							       	$('#lumise-form-submitting').html(txt);
							      }
							    }, false);
							    return xhr;
							},
						    success: function (res, status) {
							    
							    res = JSON.parse(res);
							    
							    if (res.error) {
								    alert(res.error);
								    return;
							    };
							    
							    files = JSON.parse(decodeURIComponent(res.success));
							    inp.val(files[inp.attr('name')]);
							    
							    $(form).off('submit').submit();
							    
							}
						});
						
						return false;
						
						var submit_url = LumiseDesign.ajax+'&action=upload&task=files&nonce=LUMISE_ADMIN:'+LumiseDesign.nonce,
							boundary = "---------------------------7da24f2e50046",
							body = '--' + boundary + '\r\n'
						         + 'Content-Disposition: form-data; name="file";'
						         + 'filename="file.txt"\r\n'
						         + 'Content-type: plain/text\r\n\r\n'
						         + btoa(encodeURIComponent(inp.val()))
						         + '\r\n'
						         + '--'+ boundary + '--';
								 
						$.ajax({
						    contentType: "multipart/form-data; boundary="+boundary,
						    data: body,
						    type: "POST",
						    url: submit_url,
						    xhr: function() {
							    var xhr = new window.XMLHttpRequest();
							    xhr.upload.addEventListener("progress", function(evt){
							      if (evt.lengthComputable) {
							        var percentComplete = evt.loaded / evt.total,
							        	txt = '<i class="fa fa-spin fa-spinner"></i>  '+parseInt(percentComplete*100)+'% upload complete';
							        if (percentComplete === 1)
							        	txt = '<i class="fa fa-spin fa-refresh"></i> Submitting..';
							       	$('#lumise-files-form-submitting').html(txt);
							      }
							    }, false);
							    return xhr;
							},
						    success: function (res, status) {
							    
							    if (res.indexOf('Error') === 0) {
								    alert(res);
								    return;
							    };
							    inp.val(res);
							    $(form).off('submit').submit();
							    
							}
						});
						
						return false;
						
					}
					
				});
			}
		},
		
		printing: {
			
			init: function(cfg) {
				
				var tab_events = function(wrp) {
						
					trigger({
						
						el: wrp,
						
						events: {
							'.lumise_tab_nav li': 'active_tab',
							':click': 'tbody_funcs',
							'input[data-func="show_detail"]:change': 'show_detail'
						},
						
						active_tab: function(e) {
							
							e.preventDefault();
							
							var s = this.getAttribute('data-stage');
							
							if (this.getAttribute('data-add') == 'stage') {
								return e.data.new_tab(e, this);
							} else if (e.target.getAttribute('data-func') == 'remove') {
								return e.data.remove_tab(e, this);
							};
							
							e.data.el.find('.lumise_tab_nav li.active').removeClass('active');
							e.data.el.find('.lumise_tabs .lumise_tab_content').hide();
							
							$(this).addClass('active');
							
							e.data.el.find('.lumise_tabs .lumise_tab_content[data-stage="'+s+'"]').show().addClass('active');
							
						},
						
						remove_tab: function(e, el) {
							
							if ($(el).closest('ul.lumise_tab_nav').find('>li').length === 2) {
								alert(lumise.i(162));
								return;	
							};
							
							if ($(el).next('li:not([data-add])').length > 0)
								$(el).next('li:not([data-add])').click();
							else $(el).prev('li:not([data-add])').click();
							
							var ul = $(el).closest('ul.lumise_tab_nav');
							
							$(el).remove();
							
							e.data.el.find('.lumise_tabs .lumise_tab_content[data-stage="'+el.getAttribute('data-stage')+'"]').remove();
							ul.find('>li:not([data-add])').each(function(i) {
								$(this).find('text').html('Stage '+(i+1));
							});	
						},
						
						new_tab: function(e, el) {
							
							e.preventDefault();
							
							var ul = $(el).closest('ul.lumise_tab_nav'),
								tabs = ul.parent().find('div.lumise_tabs')
								id = new Date().getTime().toString(36);
							
							if (ul.find('>li').length > cfg.ops.max_stages) {
								alert(lumise.i(163));
								return;	
							};
							
							ul.find('>li[data-add]').before(
								ul.find('>li').first().clone().removeClass('active').attr({'data-stage': id})
							);
							tabs.append(
								tabs.find('>div.lumise_tab_content').first().clone().css({display: ''}).attr({'data-stage': id})
							);
							
							ul.find('>li:not([data-add])').each(function(i) {
								$(this).find('text').html('Stage '+(i+1));
							});
							
							tab_events(e.data.el);
							ul.find('>li:not([data-add])').last().click();
								
						},
						
						tbody_funcs: function(e) {
							
							var func = e.target.getAttribute('data-func');
							
							if (!func)
								return;
							
							if (typeof e.data[func] == 'function')
								e.data[func](e);
							
						},
						
						delete_row: function(e) {
							if ($(e.target).closest('.lumise_tab_content').find('tbody tr').length > 1)
								$(e.target).closest('tr').remove();
							else alert(cfg.ops.langs.nd);
						},
						
						add_row: function(e) {
							
							var tbody = $(e.target).closest('.lumise_tab_content').find('tbody');
								last = tbody.find('tr').last();
							
							tbody.append(last.clone());
							
							var qty = tbody.find('tr').last().find('input[data-name="qty"]');
							qty.val(parseInt(qty.val())+5);
							
							e.preventDefault();
						},
						
						add_column: function(e) {
							
							e.preventDefault();
							
							var type = e.target.getAttribute('data-type'),
								label = '', 
								tabs = $(e.target).closest('div.lumise_tabs').find('div.lumise_tab_content');
							
							if (type == 'color') {
								label = prompt(LumiseDesign.js_lang['152'], (tabs.eq(0).find('thead tr td').length-2));
								label = label.replace(/\D/g, '');
								if (tabs.eq(0).find('tbody tr input[data-name="color_'+label+'"]').length > 0)
									label = '';
								if (label !== '')
									label = label+'-color';
							} else { 
								label = prompt(LumiseDesign.js_lang['155'], '');
								label = encodeURIComponent(
									label.replace(/\"/g, '&quot;').
										  replace(/\'/g, '&apos;').
										  replace(/\%/g, '&#37;').
										  replace(/\'/g, '&apos;').
										  replace(/\>/g, '&gt;').
										  replace(/\</g, '&lt;')
									);
							};
							
							tabs.each(function() {
								
								var tabl = $(this);
								
								if (label !== null && label !== '') {
									
									tabl.find('thead tr td').last().before('<td>'+decodeURIComponent(label)+'</td>');
									tabl.find('tbody tr').each(function(){
										$(this).find('td').last().before(
											'<td><input data-name="'+label+'" value="1"></td>'
									    );
									});
									tabl.parent().scrollLeft(tabl.width());
								};
							});
							
						},
						
						reduce_column: function(e) {
							
							var type = e.target.getAttribute('data-type');
							
							$(e.target).closest('div.lumise_tabs').find('div.lumise_tab_content').each(function() {
								var tabl = $(this).find('table');
								if (tabl.find('thead tr td').length > (type == 'color' ? 3 : 2)) {
									tabl.find('thead tr td').last().prev().remove();
									tabl.find('tbody tr').each(function(){
										$(this).find('td').last().prev().remove();
									});
									tabl.parent().scrollLeft(tabl.width());
								};
							});
							
							e.preventDefault();
							
						},
						
						show_detail: function(e) {
							e.data.el.find('input[data-func="show_detail"]').prop({'checked': this.checked});
							cfg.ops.show_detail = this.checked ? '1' : '0';
						}
						
						
					}, true);
				};
					
				trigger({
					
					el: $('.lumise_field_print'),
					events: {
						'input[data-func="type"]:change': 'change_type',
						'input[data-func="multi"]:change': 'change_multi'
					},
					
					change_type: function(e) {
						
						var multi = e.data.el.find('input[data-func="multi"]').is(':checked'),
							content = $(this).closest('.lumise_radios').find('.lumise_radio_content');
							
						e.data.el.find('.lumise_radio_content').removeClass('lumise-open').attr({'data-multi': multi ? 'yes' : 'no'});
						content.addClass('lumise-open');
						
						if (content.html() === '')
							e.data.render_tabs(e, this);
						
					},
					
					change_multi: function(e) {
						
						e.data.el.find('.lumise_radio_content').attr({'data-multi': this.checked ? 'yes' : 'no'});
						
						if (!this.checked) {
							$(e.data.el).find('.lumise_radio_content.lumise-open').find('.lumise_tab_nav a').first().trigger('click');
						}
							
					},
					
					render_tabs: function(e, el) {
						
						var wrp = $(el).closest('.lumise_radios').find('.lumise_radio_content'),
							id = new Date().getTime().toString(36),
							nav = $('<ul class="lumise_tab_nav"></ul>'),
							tabs = $('<div class="lumise_tabs"></div>');

						if (
							typeof cfg.ops.data[el.value].values != 'object' || 
							Object.keys(cfg.ops.data[el.value].values).length == 0
						) cfg.ops.data[el.value].values = {id: cfg.ops.data[el.value].default};
						
						Object.keys(cfg.ops.data[el.value].values).map(function(s, i) {
							nav.append(
								'<li class="active" data-stage="'+s+'">\
									<a href="#active-tab">\
										<text>Stage '+(i+1)+'</text>\
										<svg data-func="remove" height="16px" width="16px" viewBox="-75 -75 370 370"><path data-func="remove" d="M131.804,106.491l75.936-75.936c6.99-6.99,6.99-18.323,0-25.312   c-6.99-6.99-18.322-6.99-25.312,0l-75.937,75.937L30.554,5.242c-6.99-6.99-18.322-6.99-25.312,0c-6.989,6.99-6.989,18.323,0,25.312   l75.937,75.936L5.242,182.427c-6.989,6.99-6.989,18.323,0,25.312c6.99,6.99,18.322,6.99,25.312,0l75.937-75.937l75.937,75.937   c6.989,6.99,18.322,6.99,25.312,0c6.99-6.99,6.99-18.322,0-25.312L131.804,106.491z"></path></svg>\
									</a>\
								</li>');
							tabs.append(
								'<div class="lumise_tab_content" data-stage="'+s+'">'+
									e.data.render_table(e, el.value, s)+
								'</div>'
							);
						});	
						
						nav.append('<li data-add="stage"><a href="#new"><i class="fa fa-plus"></i></a></li>');		  
						wrp.append(nav).append(tabs);
						
						tab_events(wrp);
						
						nav.find('>li').first().click();
						
					},
					
					render_table: function(e, type, s) {
						
						var th = '<thead><tr><td>'+cfg.ops.langs.qr+'</td>', ops;
						
						if (cfg.ops.data[type].values !== undefined && cfg.ops.data[type].values[s] !== undefined) {
							ops = cfg.ops.data[type].values[s];
						} else {
							if (cfg.ops.data[type].values !== undefined)
								ops = cfg.ops.data[type].values[Object.keys(cfg.ops.data[type].values)[0]];
							else ops = cfg.ops.data[type].default;
							Object.keys(ops).map(function(o, i){
								if (i>0) delete ops[o];
							});
						};
							
						Object.keys(ops[Object.keys(ops)[0]]).map(function(o){
							th += '<td>'+decodeURIComponent(o)+'</td>';
						});
						
						var tb = e.data.render_rows(ops);
						
						th += '<td></td></tr></thead>';
						
						return '<div data-view="table"><table>'+th+tb+'</table></div>\
								<a href="#" data-func="add_row">'+cfg.ops.langs.aqr+'</a>'+(
									(type == 'color' || type == 'size') ? 
									' <a href="#" data-func="add_column" data-type="'+type+'">'+LumiseDesign.js_lang['153']+'</a> \
									 <a href="#" data-func="reduce_column" data-type="'+type+'">'+LumiseDesign.js_lang['154']+'</a>' 
									 : '')+'<input type="checkbox" data-func="show_detail" id="showindt-'+type+'"'+(
										 cfg.ops.show_detail == '1' ? ' checked' : ''
									 )+' /> &nbsp; <label for="showindt-'+type+'">Show in details?</label>';
						
					},
					
					render_rows: function(values) {
						
						var tb = '<tbody>';
						
						Object.keys(values).map(function(v) {
							tb += '<tr><td><input data-name="qty" value="'+v+'" /></td>';
							Object.keys(values[v]).map(function(c){
								var val = '';
								if(typeof values[v][c] !== 'undefined')
									val = values[v][c];
										
								tb += '<td><input data-name="'+c+'" value="'+val+'" /></td>';
							});
							tb += '<td><i class="fa fa-times" data-func="delete_row"></i></td></tr>';
						});
						
						return tb+'</tbody>';
						
					}
					
					
				}, 'prt');
					
				$('.lumise_field_print input[data-func="type"]:checked').trigger('change');
				
				$('.lumise_form').on('submit', function() {
					
					$('.lumise_field_print').each(function(){
						
						var el = $(this),
							vals = function(el) {
								var result = {}, qty;
								el.find('tbody tr').each(function(){
									qty = $(this).find('input[data-name="qty"]').val();
									result[qty] = {};
									$(this).find('input:not([data-name="qty"])').each(function(){
										result[qty][this.getAttribute('data-name')] = this.value;
									});
								});
								return result;
							},
							data = {
								multi: el.find('input[data-func="multi"]').is(':checked'),
								type: el.find('input[data-func="type"]:checked').val(),
								show_detail: el.find('.lumise-open input[data-func="show_detail"]').
											eq(0).is(':checked') ? '1' : '0',
								values: {}
							};
							
						var active = el.find('.lumise_radio_content.lumise-open');
						
						if (data.multi) {
							active.find('.lumise_tab_content').each(function(){
								data.values[this.getAttribute('data-stage')] = vals($(this));
							});
						}else{
							var ctn = active.find('.lumise_tab_content').first();
							data.values[ctn.attr('data-stage')] = vals(ctn);
						}
						
						el.find('input[data-func="data-saved"]').val(btoa(encodeURIComponent(JSON.stringify(data))));
						
					});
					
				});
				
			}
			
		},
		
		svguni: function(data) {
			
			if (data.indexOf('image/svg+xml') === -1)
				return data;
			
			data = data.split(',');
			data[1] = $('<div>'+atob(data[1].replace('viewbox=', 'viewBox='))+'</div>');
			
			data[1].find('[id]').each(function(){
				this.id = this.id.replace(/[\u{0080}-\u{FFFF}]/gu,"");
			});
			
			var svg = data[1].find('svg').get(0);
			
			if (!svg.getAttribute('width'))
				svg.setAttribute('width', '1000px');
			
			if (!svg.getAttribute('height')) {
				var vb = svg.getAttribute('viewBox').trim().split(' ');
				svg.setAttribute('height', (1000*(parseFloat(vb[3])/parseFloat(vb[2])))+'px');
			};
			
			data[1] = btoa(data[1].html());
			
			return data[0]+','+data[1];
			
		},
		
		slugify: function(text) {
			
		  var a = 'àáạäâãấầẫậạăắằẵặèéëêếềễẹệìíĩïîịòóöôốồỗộọùúüûũụùúũđñçßÿœæŕśńṕẃǵǹḿǘẍźḧ·/_,:;',
		  	  b = 'aaaaaaaaaaaaaaaaeeeeeeeeeiiiiiiooooooooouuuuuuuuudncsyoarsnpwgnmuxzh------',
		  	  p = new RegExp(a.split('').join('|'), 'g');
		
		  return text.toString().toLowerCase()
				.replace(/\s+/g, '-')
				.replace(p, function(c) {return b.charAt(a.indexOf(c));}) 
				.replace(/&/g, '-and-')
				.replace(/[^\w\-]+/g, '')
				.replace(/\-\-+/g, '-')
				.replace(/^-+/, '')
				.replace(/-+$/, '');
	
		},
		
		invert_color: function(color) {
			
			var r,g,b;
			
			if (color.indexOf('rgb') > -1) {
				
				color = color.split(',');
				r = parseInt(color[0].trim());
				g = parseInt(color[1].trim());
				b = parseInt(color[2].trim());
				
			} else {
				if (color.length < 6)
					color += color.replace('#', '');
				var cut = (color.charAt(0)=="#" ? color.substring(1,7) : color.substring(0,6));
				r = (parseInt(cut.substring(0,2),16)/255)*0.213;
				g = (parseInt(cut.substring(2,4),16)/255)*0.715;
				b = (parseInt(cut.substring(4,6),16)/255)*0.072;
			}
			
			return (r+g+b < 0.5) ? '#DDD' : '#333';
		},
		
		esc: function(s) {
			return typeof s == 'string' ? s.trim().replace(/\"/g, '&#x22;') : '';
		}
		
	}
	
	$(document).ready(function($) {

		$('div.lumise_form_content .lumise-toggle input[name="is_mask"]').parents('div.lumise_form_content').append('<button class="button btn_fix_bug" style="cursor: pointer;margin-left: 7px;padding: 5px 5px;">Update position</button>');
		
		var color_maps = {"#000000":"black","#000080":"navy","#00008b":"darkblue","#0000cd":"mediumblue","#0000ff":"blue","#006400":"darkgreen","#008000":"green","#008080":"teal","#008b8b":"darkcyan","#00bfff":"deepskyblue","#00ced1":"darkturquoise","#00fa9a":"mediumspringgreen","#00ff00":"lime","#00ff7f":"springgreen","#00ffff":"cyan","#191970":"midnightblue","#1e90ff":"dodgerblue","#20b2aa":"lightseagreen","#228b22":"forestgreen","#2e8b57":"seagreen","#2f4f4f":"darkslategrey","#32cd32":"limegreen","#3cb371":"mediumseagreen","#40e0d0":"turquoise","#4169e1":"royalblue","#4682b4":"steelblue","#483d8b":"darkslateblue","#48d1cc":"mediumturquoise","#4b0082":"indigo","#556b2f":"darkolivegreen","#5f9ea0":"cadetblue","#6495ed":"cornflowerblue","#663399":"rebeccapurple","#66cdaa":"mediumaquamarine","#696969":"dimgrey","#6a5acd":"slateblue","#6b8e23":"olivedrab","#708090":"slategrey","#778899":"lightslategrey","#7b68ee":"mediumslateblue","#7cfc00":"lawngreen","#7fff00":"chartreuse","#7fffd4":"aquamarine","#800000":"maroon","#800080":"purple","#808000":"olive","#808080":"grey","#87ceeb":"skyblue","#87cefa":"lightskyblue","#8a2be2":"blueviolet","#8b0000":"darkred","#8b008b":"darkmagenta","#8b4513":"saddlebrown","#8fbc8f":"darkseagreen","#90ee90":"lightgreen","#9370db":"mediumpurple","#9400d3":"darkviolet","#98fb98":"palegreen","#9932cc":"darkorchid","#9acd32":"yellowgreen","#a0522d":"sienna","#a52a2a":"brown","#a9a9a9":"darkgrey","#add8e6":"lightblue","#adff2f":"greenyellow","#afeeee":"paleturquoise","#b0c4de":"lightsteelblue","#b0e0e6":"powderblue","#b22222":"firebrick","#b8860b":"darkgoldenrod","#ba55d3":"mediumorchid","#bc8f8f":"rosybrown","#bdb76b":"darkkhaki","#c0c0c0":"silver","#c71585":"mediumvioletred","#cd5c5c":"indianred","#cd853f":"peru","#d2691e":"chocolate","#d2b48c":"tan","#d3d3d3":"lightgrey","#d8bfd8":"thistle","#da70d6":"orchid","#daa520":"goldenrod","#db7093":"palevioletred","#dc143c":"crimson","#dcdcdc":"gainsboro","#dda0dd":"plum","#deb887":"burlywood","#e0ffff":"lightcyan","#e6e6fa":"lavender","#e9967a":"darksalmon","#ee82ee":"violet","#eee8aa":"palegoldenrod","#f08080":"lightcoral","#f0e68c":"khaki","#f0f8ff":"aliceblue","#f0fff0":"honeydew","#f0ffff":"azure","#f4a460":"sandybrown","#f5deb3":"wheat","#f5f5dc":"beige","#f5f5f5":"whitesmoke","#f5fffa":"mintcream","#f8f8ff":"ghostwhite","#fa8072":"salmon","#faebd7":"antiquewhite","#faf0e6":"linen","#fafad2":"lightgoldenrodyellow","#fdf5e6":"oldlace","#ff0000":"red","#ff00ff":"magenta","#ff1493":"deeppink","#ff4500":"orangered","#ff6347":"tomato","#ff69b4":"hotpink","#ff7f50":"coral","#ff8c00":"darkorange","#ffa07a":"lightsalmon","#ffa500":"orange","#ffb6c1":"lightpink","#ffc0cb":"pink","#ffd700":"gold","#ffdab9":"peachpuff","#ffdead":"navajowhite","#ffe4b5":"moccasin","#ffe4c4":"bisque","#ffe4e1":"mistyrose","#ffebcd":"blanchedalmond","#ffefd5":"papayawhip","#fff0f5":"lavenderblush","#fff5ee":"seashell","#fff8dc":"cornsilk","#fffacd":"lemonchiffon","#fffaf0":"floralwhite","#fffafa":"snow","#ffff00":"yellow","#ffffe0":"lightyellow","#fffff0":"ivory","#ffffff":"white"},
		rgb2hex = function(rgb){
			if (rgb.indexOf('#') === 0)
				return rgb;
			rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
			return (rgb && rgb.length === 4) ? "#" +
			("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
			("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
			("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
		};

		var lumise_action = function(event) {
				
				event.preventDefault();
				
				var data = {
			        "type": $(this).attr('data-type'),
			        "action": $(this).attr('data-action'),
			        "id": $(this).attr('data-id'),
			        "status": $(this).attr('data-status'),
			    }, that = $(this);

			    that.html('<i class="fa fa-spinner fa-spin"></i>');

				$.ajax({
					url: LumiseDesign.ajax,
					method: 'POST',
					data: LumiseDesign.filter_ajax({
						action: 'switch_status',
						nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
						data: data
					}),
					dataType: 'json',
					statusCode: {
						403: function(){
							alert(LumiseDesign.js_lang.error_403);
						}
					},
					success: function(res){
						if (res.status == 'success') {
							if (res.action == 'switch_feature') {
								if (res.value == 1) {
									that.html("<i class='fa fa-star'></i>");
									that.attr('data-status', 1);
								} else {
									that.html("<i class='none fa fa-star-o'></i>");
									that.attr('data-status', 0);
								}
							}
							var tdname = that.closest('tr').find('td[data-name]');
							if (res.action == 'switch_active') {
								if (res.value == 1) {
									that.html('<em class="pub">'+lumise.i(85)+'</em>');
									that.attr('data-status', 1);
									if (data.type == 'addons') {
										tdname.html(
											'<a href="'+LumiseDesign.admin_url+'lumise-page=addon&name='+tdname.attr('data-slug')+'">'+decodeURIComponent(tdname.attr('data-name'))+'</a>'
										);
									}
								} else {
									that.html('<em class="un pub">'+lumise.i(86)+'</em>');
									that.attr('data-status', 0);
									if (data.type == 'addons') {
										tdname.html(decodeURIComponent(tdname.attr('data-name')));
									}
								}
							}
						} else {
							alert(res.value);
						}
					}
				});
			},

			lumise_action_duplicate = function(event) {
				
				event.preventDefault();
				
				this.setAttribute('data-working', 'true');
				
				var data = {
				        "id": $(this).attr('data-id'),
				        "table": $(this).attr('data-table')
				    }, 
				    that = $(this),
				    toptr = that.closest('tr');
				
				that.attr({'data-working': 'true'});
				
				$.ajax({
					url: LumiseDesign.ajax,
					method: 'POST',
					data: LumiseDesign.filter_ajax({
						action: 'duplicate_item',
						nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
						data: data
					}),
					dataType: 'json',
					statusCode: {
						403: function(){
							alert(LumiseDesign.js_lang.error_403);
						}
					},
					success: function(data){
						
						that.attr({'data-working': 'false'});
						
						if (data !== null && data.status == 'success') {

							var elm = toptr.clone(),
								check_input = elm.find('.lumise_checkbox input'),
								check_label = elm.find('.lumise_checkbox label'),
								action = elm.find('.lumise_action'),
								name = elm.find('.name');

							name.attr('href', data.data.url);
							name.html(data.data.name);
							check_input.attr('value', data.data.id);
							check_input.attr('id', data.data.id);
							check_input.attr('id', data.data.id);
							check_label.attr('for', data.data.id);
							action.attr('data-id', data.data.id);
							toptr.after(elm);

							var values = elm.find("input[name='checked[]']:checked").map(function(){return $(this).val();}).get();
							elm.find('.id_action').val(values);

							trigger({
			
								el: elm,
								
								events: {
									'.lumise_action': lumise_action,
									'.lumise_action_duplicate': lumise_action_duplicate,
									'.action_check:change': function(e) {
										var values = $("input[name='checked[]']:checked").map(function(){return $(this).val();}).get();
										$('.id_action').val(values);
									}
								}
							});


						} else if (data !== null){
							alert(data.value);
						}
					}
				});
			};
		
		trigger({
			
			el: $('body'),
			
			events: {
				
				'#check_all': 'check_all',
				'.action_check': 'action_check',
				'.lumise_action': lumise_action,
				'.lumise_action_duplicate': lumise_action_duplicate,
				'.lumise_menu > li > a': 'left_menu',
				'.btn-toggle-sidebar': 'toggle_menu',
				'.btn-toggle-sidebar-mb': 'toggle_menu_mb',
				'.overlay_mb': 'overlay_mb',
				'.lumise_tab_nav': 'tab_click',
				'[data-file-select]:change': 'select_file',
				'[data-file-delete]': 'delete_upload',
				'.lumise_support_icon': 'button_help',
				
				'.lumise-field-color': 'change_color',
				'.lumise-field-color-wrp [data-func="create-color"]': 'create_color',
				'.lumise-field-color-wrp button[data-func="clear-color"]': 'clear_color',
				
				'[data-action="submit"]:change': 'do_submit',
				'#lumise-product-form:submit': 'check_submit',
				
				'.lumise-field-google_fonts': 'google_fonts',
				
				'.lumise-item-action' : 'items_action',
				'a[href="#report-bug"]': 'report_bug',
				'button.loaclik': function() {
					this.innerHTML = '<i style="font-size: 16px;" class="fa fa-circle-o-notch fa-spin fa-fw"></i> please wait..';
				}
			},
			
			items_action : function (e){
				
				e.preventDefault();
				
				var func = $(this).attr('data-func'),
					item_id = $(this).attr('data-item');
				
				switch (func) {
					case 'delete':
						var conf = confirm(lumise.i(121));
						if (conf == true) {
							$('<form>', {
				                "id": "lumise-item-delete",
				                "method": "POST",
				                "html": '<input type="hidden" name="id" value="' + item_id + '"/><input type="hidden" name="do" value="action"/><input type="hidden" name="action_submit" value="action"/><input type="hidden" name="action" value="delete"/><input type="hidden" name="nonce" value="LUMISE_ADMIN:' + LumiseDesign.nonce + '"/>',
				                "action": window.location.href
				            }).appendTo(document.body).submit();
						}
						break;
					default:
						
				}
			},

			button_help: function(e) {
				$(this).toggleClass('open');
				$(this).parents().find('.lumise_list_icon').toggleClass('open');
			},
			
			check_all: function(){
				
				$('.action_check').prop('checked', this.checked);
				
				var values = $("input[name='checked[]']:checked").map(function(){
					return $(this).val();
				}).get();
				
				$('.id_action').val(values);
			},
			
			action_check: function() {
				var values = $("input[name='checked[]']:checked").map(function(){return $(this).val();}).get();
				$('.id_action').val(values);
			},

			left_menu: function(event) {
				
				var sub = $(this).next();
				if (sub.length == 0) {
					return;
				}
				event.preventDefault();
	
				var height=0,
					wrp = $(this).parent(),
					target = this,
					sub = wrp.find('.lumise_sub_menu');
				$('.lumise_icon_dropdown').removeClass('open');
				$(".lumise_sub_menu.open").css({'height': 0}).removeClass('open');
	
				if( $(this).attr('data-height') === undefined) {
					$(sub).find('li').each(function (i){
						height += $(this).outerHeight();
					});	
					$(this).attr('data-height', height);
				}
	
				if($(this).next().css('height') == '0px'){
	
					$(this).find('.lumise_icon_dropdown').addClass('open');
	
					$(sub).toggleClass(function (){
						if($(this).is('.open'))
							$(sub).css({'height': 0});
						else
							$(sub).css({'height': $(target).attr('data-height')});
	
						return 'open';
					});
	
				}
				
			},
			
			toggle_menu: function() {
				$(this).parents(".lumise_sidebar").toggleClass('menu_icon');
				$(this).parents("body").toggleClass('page_sidebar_mini');
			},

			toggle_menu_mb: function() {
				$(this).parents(".lumise_mobile").toggleClass('open');
			},

			overlay_mb: function() {
				$(this).parent(".lumise_mobile").toggleClass('open');
			},
			
			tab_click: function(e) {
				
				if (e.target.getAttribute('data-func') == "remove") {
					
					e.preventDefault();
					
					if ($(this).find('>li').length <= 2) {
						alert(lumise.i(162));
						return;
					};
					
					var s = $(e.target).closest('a').attr('href');
					
					$('.lumise-stages-wrp').each(function() {
						if ($(this).find('ul.lumise_tab_nav a[href="'+s+'"]').parent().prev().length > 0)
							$(this).find('ul.lumise_tab_nav a[href="'+s+'"]').parent().prev().find('a').click();
						else if ($(this).find('ul.lumise_tab_nav a[href="'+s+'"]').parent().next().length > 0)
							$(this).find('ul.lumise_tab_nav a[href="'+s+'"]').parent().next().find('a').click();
						$(this).find('ul.lumise_tab_nav a[href="'+s+'"]').parent().remove();
					});
					
					$(s).remove();
					
					return;
						
				};
					
				if (e.target.tagName != 'A' && e.target.parentNode.tagName != 'A')
					return;
					
				var el = e.target.tagName == 'A' ? $(e.target) : $(e.target).closest('a'),
					tid = el.attr('href'),
					nav = $(this).closest('.lumise_tab_nav'),
					wrp = $(this).closest('.lumise_tabs_wrapper');
				
				if (el.parent().hasClass('active') || $(tid).length === 0) {
					e.preventDefault();
					return;
				}
				
				wrp.find('>.lumise_tabs>.lumise_tab_content').hide().removeClass('active');
				nav.find('>li').removeClass('active');
				el.parent().addClass('active');
				
				$(tid).css("display", "block").addClass('active');
				
				if (wrp.attr('data-id') !== '') {
					
					var hist = localStorage.getItem('LUMISE-TABS');
					
					if (!hist)
						hist = {};
					else hist = JSON.parse(hist);
					
					hist[wrp.attr('data-id')] = tid;
					
					localStorage.setItem('LUMISE-TABS', JSON.stringify(hist));
					
				}
				
				e.preventDefault();
				
			},
			
			select_file: function(e) {
				
				var type = this.getAttribute('data-file-select'),
					preview = this.getAttribute('data-file-preview'),
					_this = this,
					attr = function(s){
						return _this.getAttribute('data-'+s);
					}
				
				if (this.files && this.files[0]) {
					
					if (type != 'font' && type != 'design' && !lumise_validate_file(this.files[0]))
						return alert('Error: Invalid upload file');
					
			        var reader = new FileReader();
					reader.file = this.files[0];
			        reader.onload = function (e) {
				       
				       	var result = lumise.svguni(e.target.result);
				       	
						if (type == 'font') {
							lumise_font_preview(
								Math.random().toString(36).substr(2).replace(/\d/g,''), 
								'url('+result+')', 
								preview
							);
						} else if (type != 'design' && attr('file-preview')) {
							$(attr('file-preview')).attr('src', result);
						};
						
						if (attr('file-input')) {
							
							var data = {
								data: result,
								size: this.file.size,
								name: 'lumise-media-'+this.file.name.replace(/[^0-9a-zA-Z\.\-\_]/g, "").trim().replace(/\ /g, '+'),
								type: this.file.type ? this.file.type : this.file.name.split('.').pop(),
								old: $(attr('file-input')+'-old').val(),
								path: $(attr('file-input')).attr('data-path')
							}
							
							if (attr('file-thumbn-width') || attr('file-thumbn-height')) {
							
								lumise_create_thumbn({
									source: result,
									width: attr('file-thumbn-width') || null,
									height: attr('file-thumbn-height') || null,
									callback: function(res) {
										data.thumbn = res;
										if (attr('file-preview'))
											$(attr('file-preview')).attr('src', res);
										$(attr('file-input')).val(JSON.stringify(data));
									}
								});
							
							} else if (type == 'design') {
									
								data.thumbn = scre;
								if (result.indexOf('data:application/octet-stream') > -1) {	
									var deco = JSON.parse(decodeURIComponent(atob(result.split('base64,')[1]))),
										scre = deco.stages[Object.keys(deco.stages)[0]].screenshot;
									
									$(attr('file-preview')).attr('src', scre);
									data.thumbn = scre;
									
									$(attr('file-input')).val(JSON.stringify(data));
									
								} else if (result.indexOf('data:image/') > -1) {
									
									lumise_create_thumbn({
										source: result,
										width: 500,
										height: null,
										callback: function(thumbn, img) {
											data.thumbn = thumbn;
											data.data = img.src;
											data.name = (new Date().getTime().toString(36))+
														'.'+(img.src.indexOf('data:image/png') > -1 ? 'png' : 'jpg');
											/*data.data = build_lumi(thumbn, img);
											data.name = 'PRINTREADY-'+(new Date().getTime().toString(36))+'.lumi';*/
											$(attr('file-preview')).attr('src', thumbn);
											$(attr('file-input')).val(JSON.stringify(data));
										}
									});
									
								}
								
							} else {
								$(attr('file-input')).val(JSON.stringify(data));
							}
							
						}
							
			        };
			        
			        reader.readAsDataURL(this.files[0]);
			        
			    }
				
			},
			
			delete_upload: function(e) {
				
				var _this = this,
				attr = function(s){
					return _this.getAttribute('data-'+s);
				}
				
				if (attr('file-preview'))
					$(attr('file-preview')).attr('src', '').html('');
				if (attr('file-input'))
					$(attr('file-input')).val('');
				if (attr('file-thumbn')) {
					$(attr('file-thumbn')).val('');
				}
				
				$('#font-ttf-upload').hide();
				
				e.preventDefault();
				return false;
				
			},
			
			change_color: function(e) {

				var color = e.target.getAttribute('data-color'),
					wrp = $(this).closest('.lumise-field-color-wrp');
				
				if (color) {
					
					if (color == 'delete'){
						$(e.target.parentNode).remove();
						e.data.return_colors(wrp);
						return;
					}
					
					wrp.find('li[data-color].choosed').removeClass('choosed');
					
					wrp.find('input[data-el="select"]').val(color);
					$(e.target).addClass('choosed');
					
					e.data.return_colors(wrp);
					
				}

			},
			
			create_color: function(e) {
				
				var _this = $(e.target),
					render = function(colors, active) {
					
						if (typeof active != 'object')
							active = [];
						
						if (colors.length > 0) {
						
							colors.map(function(l, i){
								l = decodeURIComponent(l).split('@');
								colors[i] = '<li style="border-left: 20px solid '+l[0]+'" data-color="'+l[0]+'"><span>'+(
											(l[1] !== undefined && l[1] !== '') ? 
											decodeURIComponent(l[1]).replace(/\"/g, '') : 
											(color_maps[l] !== undefined ? color_maps[l] : l)
										)+'</span> <input type="checkbox" '+((active.indexOf(l) > -1) ? 'checked ': '')+' />\
										<i class="fa fa-check"></i>\
									</li>';
							});
						} else {
							colors = ['<p class="empty">'+LumiseDesign.js_lang['144']+'</p>'];	
						};
						
						var lis = '';
						
						Object.keys(color_maps).map(function(c){
							lis += '<li data-color="'+c+'" style="color: '+c+'">'+color_maps[c]+'</li>';
						});
						
						$('#lumise-list-colors-body').html(
							'<div class="col">\
								<h3>'+LumiseDesign.js_lang['145']+'</h3>\
								<div class="create-color-grp">\
									<input type="text" name="label" placeholder="Color Label" />\
									<input type="text" name="hex" placeholder="Color HEX" />\
									<input type="color" /> \
									<span>Color <br>picker</span>\
									<p class="style_color_btn">\
										<button data-func="apply-now">'+
										LumiseDesign.js_lang.apply+
										' <i class="fa fa-check"></i></button>\
										<button data-func="add-list">'+
										LumiseDesign.js_lang['140']+
										' <i class="fa fa-arrow-right"></i></button>\
									</p>\
									<ul class="color-names">'+lis+'</ul>\
								</div>\
							</div>\
							<div class="col">\
								<h3>\
									'+LumiseDesign.js_lang['143']+'\
									<a href="#unselectall">'+LumiseDesign.js_lang['142']+'</a>\
									<a href="#selectall">'+LumiseDesign.js_lang['141']+'</a>\
									<a href="#delete">'+LumiseDesign.js_lang['146']+'</a>\
								</h3>\
								<ul class="colors-ul">'+colors.join('')+'</ul>\
							</div>'
						);
						
						
						trigger({
							
							el: $('#lumise-list-colors'),
							
							events: {
								':click': function(e) {
									if (e.target.id == 'lumise-list-colors')
										e.data.el.remove();
								},
								'.close-pop': 'close_pop',
								'.pop-save': 'pop_save',
								'.colors-ul li': 'select_color',
								'a[href="#selectall"]': 'select_all',
								'a[href="#unselectall"]': 'unselect_all',
								'a[href="#delete"]': 'delete_selection',
								'.create-color-grp input[type="color"]:input': 'color_picker',
								'.color-names li': 'color_name',
								'button[data-func="add-list"]': 'add_list',
								'button[data-func="apply-now"]': 'apply_now'
							},
							
							close_pop: function(e) {
								e.data.el.remove();
								e.preventDefault();
							},
							
							pop_save: function(e) {
								
								var active = [];
								
								e.data.el.find('ul.colors-ul li').each(function(){
									if ($(this).find('input[type="checkbox"]').prop('checked') === true)
										active.push(this.getAttribute('data-color')+'@'+encodeURIComponent($(this).find('span').text()));
								});
								
								if (active.length > 0) {
									apply(active);
								};
								
								e.preventDefault();
								$('#lumise-list-colors').remove();
								
							},
							
							select_color: function(e) {
								$(this).find('input[type="checkbox"]').click();
							},
							
							select_all: function(e) {
								e.data.el.find('.colors-ul input[type="checkbox"]').attr({'checked': true});
								e.preventDefault();
							},
							
							unselect_all: function(e) {
								e.data.el.find('.colors-ul input[type="checkbox"]').attr({'checked': false});
								e.preventDefault();
							},
							
							delete_selection: function(e) {
								
								if (confirm(LumiseDesign.js_lang.sure)) {
									
									var colors = [];
									
									e.data.el.find('ul.colors-ul li').each(function(){
										if ($(this).find('input[type="checkbox"]').prop('checked') === true) {
											$(this).remove();
										} else {
											colors.push(
												this.getAttribute('data-color')+'@'+
												encodeURIComponent($(this).find('span').text())
											);	
										}
									});
									
									$.ajax({
										url: LumiseDesign.ajax,
										method: 'POST',
										data: {
											nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
											ajax: 'backend',
											action: 'list_colors',
											save_action: colors.join(',')
										}
									});
								}
							},
							
							color_picker: function(e) {
								
								e.data.el.find('.create-color-grp input[name="hex"]').val(this.value);
								if (e.data.el.find('.create-color-grp input[name="label"]').val() === '') {
									e.data.el.find('.create-color-grp input[name="label"]').val(
										color_maps[this.value] !== undefined ? color_maps[this.value] : ''
									);
								}
								
							},
							
							color_name: function(e) {
								
								e.data.el.
									find('.create-color-grp input[type="color"]').
									val(this.getAttribute('data-color'));
									
								e.data.el.find('.create-color-grp input[name="label"]').val(color_maps[this.getAttribute('data-color')]);
								e.data.el.find('.create-color-grp input[name="hex"]').val(this.getAttribute('data-color'));
								
							},
							
							check_color: function(e) {
								
								var el = e.data.el.find('.create-color-grp input[name="hex"]'),
									label = e.data.el.find('.create-color-grp input[name="label"]').val(),
									cl = el.val().toLowerCase().trim();
								
								if (cl.indexOf('rgb') > -1) {
									cl = rgb2hex(cl);
									el.val(cl);
								};
								
								if (Object.values(color_maps).indexOf(cl) > -1) {
									cl = Object.keys(color_maps).filter(function(s){
										return color_maps[s].toLowerCase() == cl;
									})[0];
									el.val(cl);
								};
									
								if (cl === '' || cl.length != 7 || cl.indexOf('#') !== 0) {
									e.data.el.find('.create-color-grp input[name="hex"]').shake();
									return false;
								} else return cl+'@'+encodeURIComponent(label);
									
							},
							
							add_list: function(e) {
								
								e.preventDefault();
								
								var cl = e.data.check_color(e);
								
								if (cl === false)
									return;
									
								var colors = [], active = [];
								colors.push(encodeURIComponent(cl));
								
								e.data.el.find('.colors-ul li').each(function(){
									
									colors.push(encodeURIComponent(this.getAttribute('data-color')+'@'+encodeURIComponent($(this).find('span').text().trim())));
									
									if ($(this).find('input[type="checkbox"]').prop('checked') === true)
										active.push(this.getAttribute('data-color'));
								});
								
								$.ajax({
									url: LumiseDesign.ajax,
									method: 'POST',
									data: {
										nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
										ajax: 'backend',
										action: 'list_colors',
										save_action: colors.join(',')
									}
								});
								
								render(colors, active);
								
							},
							
							apply_now: function(e) {
								
								e.preventDefault();
								
								var cl = e.data.check_color(e);
								
								if (cl === false)
									return;
								
								apply([cl]);
									
							}
							
						});
							
					},
					apply = function(colors) {
					
						var val = _this.prev().val(),
							wrp = _this.closest('.lumise-field-color-wrp');
						
						colors.map(function(c) {
							
							c = c.split('@');
							
							if (wrp.find('li[data-color="'+c[0]+'"]').length === 0) {
								
								if (wrp.attr('id') == 'lumise-product-colors') {
									wrp.find('ul.lumise-field-color li[data-add="color"]').before(
										'<li data-color="'+c[0]+'" data-label="'+(c[1] !== undefined ? c[1].toString() : '')+'">'+
									 	'<span data-func="color" style="background:'+c[0]+'"></span>'+
									 	'<i class="fa fa-times" data-func="delete"></i>'+
									 '</li>');
								} else {
									wrp.find('ul.lumise-field-color').append('<li data-color="'+c[0]+'" style="background:'+c[0]+'" data-label="'+(c[1] !== undefined ? decodeURIComponent(c[1]).replace(/\"/g, '&quot;') : '')+'"><i class="fa fa-times" data-color="delete"></i></li>');
								}
							};
							
							wrp.find('li[data-color="'+c[0]+'"]').animate({'opacity': 0.1}, 350).delay(50).animate({'opacity': 1}, 350);
							
						});
						
						e.preventDefault();
						
						if (typeof e.data.ex_return_colors == 'function')
							e.data.ex_return_colors(wrp);
						else 
							e.data.return_colors(wrp);
						
						$('#lumise-list-colors').remove();
						
					};
				
				$('body').append(
					'<div id="lumise-list-colors" class="lumise-popup" style="display:block">\
						<div class="lumise-popup-content">\
							<header>\
								<h3>'+LumiseDesign.js_lang['139']+'</h3>\
								<span class="pop-save" title="'+LumiseDesign.js_lang.save+'"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="-150 -150 750 750" height="32px" width="32px" xml:space="preserve"><path d="M506.231,75.508c-7.689-7.69-20.158-7.69-27.849,0l-319.21,319.211L33.617,269.163c-7.689-7.691-20.158-7.691-27.849,0    c-7.69,7.69-7.69,20.158,0,27.849l139.481,139.481c7.687,7.687,20.16,7.689,27.849,0l333.133-333.136    C513.921,95.666,513.921,83.198,506.231,75.508z"></path></svg></span>\
								<span class="close-pop" title="'+LumiseDesign.js_lang.close+'"><svg enable-background="new 0 0 32 32" height="32px" id="close" version="1.1" viewBox="-4 -4 40 40" width="32px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M17.459,16.014l8.239-8.194c0.395-0.391,0.395-1.024,0-1.414c-0.394-0.391-1.034-0.391-1.428,0  l-8.232,8.187L7.73,6.284c-0.394-0.395-1.034-0.395-1.428,0c-0.394,0.396-0.394,1.037,0,1.432l8.302,8.303l-8.332,8.286  c-0.394,0.391-0.394,1.024,0,1.414c0.394,0.391,1.034,0.391,1.428,0l8.325-8.279l8.275,8.276c0.394,0.395,1.034,0.395,1.428,0  c0.394-0.396,0.394-1.037,0-1.432L17.459,16.014z" fill="#121313" id="Close"></path></svg></span>\
							</header>\
							<div id="lumise-list-colors-body">\
								<img src="'+LumiseDesign.assets+'assets/images/loading.gif" height="36" style="margin-top: 200px;" />\
							</div>\
						</div>\
					</div>'
				);
				
				$.ajax({
					url: LumiseDesign.ajax,
					method: 'POST',
					data: {
						nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
						ajax: 'backend',
						action: 'list_colors'
					},
					statusCode: {
						403: function(){
							alert(LumiseDesign.js_lang.error_403);
						}
					},
					success: function(res) {
						
						render(res.split(','));
						
					}
				});
				
				e.preventDefault();
				return;
				
				
			},
			
			clear_color: function(e) {
				
				if (confirm(LumiseDesign.js_lang.sure)) {
					$(this).closest('.lumise-field-color-wrp').find('li[data-color]').remove();
				};
				
				e.preventDefault();
					
			},
			
			return_colors: function(wrp) {
				
				var color = wrp.find('input[data-el="select"]').val(),
					cur_color = wrp.find('li.choosed[data-color]'),
					val = [];
					
				if(cur_color.get(0)){
					color = cur_color.attr('data-color');
				}
				
				wrp.find('li[data-color]').each(function(){
					
					val.push(this.getAttribute('data-color')+'@'+this.getAttribute('data-label'));
					var label = decodeURIComponent(this.getAttribute('data-label')).replace(/\"/g, '');
					
					if (label === '') {
						label = color_maps[this.getAttribute('data-color')] !== undefined ? 
								color_maps[this.getAttribute('data-color')] : 
								this.getAttribute('data-color');
					};
					
					this.setAttribute('title', label);
					
				});
				
				val = color+':'+val.join(',');
				
				wrp.find('input[data-el="hide"]').val(val);
					
			},
			
			do_submit: function() {
				$(this).closest('form').submit();
			},
			
			check_submit: function (e){
				
				return true;
				
			},
			
			google_fonts: function(e) {
				
				var wrp = $(this),
					el = $(e.target);
					act = el.attr('data-act') ? el.attr('data-act') : el.closest('[data-act]').attr('data-act');
				
				switch (act) {
					case 'delete' : 
					
						el.closest('li').remove();
						
						var data = {};
						wrp.find('li').each(function(){
							data[this.getAttribute('data-n')] = [this.getAttribute('data-f'), this.getAttribute('data-s')];
						});
						wrp.find('textarea[data-func="value"]').val(JSON.stringify(data));
						
					break;
					case 'add' :
					
						lightbox({
							content: '<iframe src="https://services.lumise.com/fonts/?mode=select"></iframe>'
						});
						
						$('#lumise-lightbox iframe').on('load', function() {
							this.contentWindow.postMessage({
								action: 'fonts',
								fonts: wrp.find('textarea[data-func="value"]').val()
							}, "*");
						});
						e.preventDefault();
					break;
				}
				
			},
			
			report_bug: function(e) {
				
				e.preventDefault();
				$(this).after('<i class="fa fa-spinner fa-spin fa-2x fa-fw margin-bottom"></i>');
				var wrp = this.parentNode;
				$(this).remove();
				
				$.ajax({
					url: LumiseDesign.ajax,
					method: 'POST',
					data: {
						nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
						ajax: 'backend',
						action: 'report_bug',
						id: this.getAttribute('data-id')
					},
					statusCode: {
						403: function(){
							alert(LumiseDesign.js_lang.error_403);
						}
					},
					success: function(res) {
						wrp.innerHTML = JSON.stringify(res);
					}
				});
				
				
			}
			
		}, 'general_events');
		
		$('input.lumise-upload-helper-inp').eq(0).closest('form').on('submit', function(e) {
			if (!process_submit_upload(this)) {
				e.preventDefault();
				return false;
			};
		});
		
		// Set Price(Cliparts)
		$(".lumise_set_price").on('change', function(event) {

			event.preventDefault();
			var data = {
				"type": $(this).attr('data-type'),
				"id": $(this).attr("data-id"),
		        "value": $(this).val()
		    }, that = $(this);

			$.ajax({
				url: LumiseDesign.ajax,
				method: 'POST',
				data: LumiseDesign.filter_ajax({
					action: 'lumise_set_price',
					nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
					data: data
				}),
				dataType: 'json',
				statusCode: {
					403: function(){
						alert(LumiseDesign.js_lang.error_403);
					}
				},
				success: function(data){
					if (data.status == 'success') {
						var obj = jQuery('<span class="set_success">'+lumise.i(79)+'</span>');
						that.parent().append(obj);
						that.val(data.value);
						setTimeout(function(){
						    obj.remove();
						}, 600);
					} else {
						var obj = jQuery('<span class="set_error">'+lumise.i(97)+'</span>');
						that.parent().append(obj);
						setTimeout(function(){
						    obj.remove();
						}, 600);
					}
				}
			});
			
		});

		// Select Currency
		$( function() {
		    $.widget( "custom.combobox", {
				_create: function() {
					this.wrapper = $( "<div>" )
					  .addClass( "lumise-combobox" )
					  .insertAfter( this.element );

					this.element.hide();
					this._createAutocomplete();
					this._createShowAllButton();
				},
		 
				_createAutocomplete: function() {
					var selected = this.element.children( ":selected" ),
						value = selected.val() ? selected.text() : "";

					this.input = $( "<input>" )
						.appendTo( this.wrapper )
						.val( value )
						.addClass( "lumise-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
						.autocomplete({
							delay: 0,
							minLength: 0,
							source: $.proxy( this, "_source" )
						})
						.tooltip({
							classes: {
							  "ui-tooltip": "ui-state-highlight"
							}
					});

					this._on( this.input, {
						autocompleteselect: function( event, ui ) {
							ui.item.option.selected = true;
							this._trigger( "select", event, {
								item: ui.item.option
							});
						},
						autocompletechange: "_removeIfInvalid"
					});
				},
		 
		      	_createShowAllButton: function() {
			        var input = this.input,
			          wasOpen = false;
			 
			        $( "<a>" )
						.attr( "tabIndex", -1 )
						.tooltip()
						.appendTo( this.wrapper )
						.removeClass( "ui-corner-all" )
						.addClass( "lumise-combobox-toggle ui-corner-right" )
						.on( "mousedown", function() {
							wasOpen = input.autocomplete( "widget" ).is( ":visible" );
						})
						.on( "click", function() {
							input.trigger( "focus" );

							// Close if already visible
							if ( wasOpen ) {
							 	return;
							}

							// Pass empty string as value to search for, displaying all results
							input.autocomplete( "search", "" );
						});
		      	},
		 
				_source: function( request, response ) {
					var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
					response( this.element.children( "option" ).map(function() {
						var text = $( this ).text();
						if ( this.value && ( !request.term || matcher.test(text) ) )
						return {
							label: text,
							value: text,
							option: this
						};
					}));
				},
		 
		      	_removeIfInvalid: function( event, ui ) {
		 
					// Selected an item, nothing to do
					if ( ui.item ) {
					  return;
					}

					// Search for a match (case-insensitive)
					var value = this.input.val(),
						valueLowerCase = value.toLowerCase(),
						valid = false;
					this.element.children( "option" ).each(function() {
						if ( $( this ).text().toLowerCase() === valueLowerCase ) {
							this.selected = valid = true;
							return false;
						}
					});

					// Found a match, nothing to do
					if ( valid ) {
						return;
					}

					// Remove invalid value
					this.input
						.val( "" )
						.tooltip( "open" );
					this.element.val( "" );
					this._delay(function() {
						this.input.tooltip( "close" ).attr( "title", "" );
					}, 2500 );
					this.input.autocomplete( "instance" ).term = "";
					},

					_destroy: function() {
						this.wrapper.remove();
						this.element.show();
					}

		   		});
		 
		    $( ".lumise_currency" ).combobox();
		});

		// Multi Select Tag
		$( function() {

			function split( val ) {
			  return val.split( /,\s*/ );
			}
			function extractLast( term ) {
			  return split( term ).pop();
			}

			$( "#tags" )
			// don't navigate away from the field on tab when selecting an item
			.on( "keydown", function( event ) {
				if ( event.keyCode === $.ui.keyCode.TAB &&
					$( this ).autocomplete( "instance" ).menu.active ) {
					event.preventDefault();
				}
			})
			.autocomplete({
				minLength: 0,
				source: function( request, response ) {
					// delegate back to autocomplete, but extract the last term
					if (window.lumise_tag_values !== undefined) {
						response( $.ui.autocomplete.filter(
							lumise_tag_values, 
							extractLast( request.term ) ) 
						);
					};
				},
				focus: function() {
					// prevent value inserted on focus
					return false;
				},
				select: function( event, ui ) {
					var terms = split( this.value );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
					return false;
				}
			});
		});

		// Box Tab
		if (typeof window.lumise_sampleTags !== 'undefined') {
			$('.tagsfield').tagit({
				availableTags: lumise_sampleTags,
				autocomplete: {delay: 0, minLength: 2},
				removeConfirmation: true,
				afterTagAdded: function(event, ui) {
					var data = {
				        "type": $(this).attr('data-type'),
				        "id": $(this).attr('data-id'),
				        'value': ui.tag.find('.tagit-label').html()
				    }, that = $(this);
				    if(!ui.duringInitialization)
						$.ajax({
							url: LumiseDesign.ajax,
							method: 'POST',
							data: LumiseDesign.filter_ajax({
								action: 'add_tags',
								nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
								data: data
							}),
							dataType: 'json',
							statusCode: {
								403: function(){
									alert(LumiseDesign.js_lang.error_403);
								}
							},
							success: function(data){
								if (data.status == 'success') {
									var obj = jQuery('<span class="set_success">'+lumise.i(79)+'</span>');
									that.parent().append(obj);
									setTimeout(function(){
									    obj.remove();
									}, 600);
								} else{
									var obj = jQuery('<span class="set_error">'+lumise.i(97)+'</span>');
									that.parent().append(obj);
									setTimeout(function(){
									    obj.remove();
									}, 600);
								}
							}
						});
				},
				beforeTagRemoved: function(event, ui) {
			        var data = {
				        "type": $(this).attr('data-type'),
				        "id": $(this).attr('data-id'),
				        'value': ui.tag.find('.tagit-label').html()
				    }, that = $(this);

					$.ajax({
						url: LumiseDesign.ajax,
						method: 'POST',
						data: LumiseDesign.filter_ajax({
							action: 'remove_tags',
							nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce,
							data: data
						}),
						dataType: 'json',
						statusCode: {
							403: function(){
								alert(LumiseDesign.js_lang.error_403);
							}
						},
						success: function(data){
							if (data.status == 'success') {
								var obj = jQuery('<span class="set_success">'+lumise.i(79)+'</span>');
								that.parent().append(obj);
								setTimeout(function(){
								    obj.remove();
								}, 600);
							} else{
								var obj = jQuery('<span class="set_error">'+lumise.i(97)+'</span>');
								that.parent().append(obj);
								setTimeout(function(){
								    obj.remove();
								}, 600);
							}
						}
					});
			    }
	        });
		}

		// Menu admin		
		$(".lumise_sub_menu.open").each( function(){
			var height = 0;
			$(this).find('li').each(function (i){
				height += $(this).outerHeight();
			});
			$(this).css({'height': height});
		});

		// Back To Top
		if ($(".lumise_backtotop").length > 0) {

			$(window).scroll(function () {
				var e = $(window).scrollTop();
				if (e > 250) {
					$(".lumise_backtotop").addClass('show')
				} else {
					$(".lumise_backtotop").removeClass('show')
				}
			});

			$(".lumise_backtotop").click(function () {
				$('body,html').animate({
					scrollTop: 0
				}, 500)
			})

		}
		
		// Active the first tab
		$('.lumise_tab_nav').each(function(){
			
			var wrp = $(this).closest('.lumise_tabs_wrapper'),
				hist = localStorage.getItem('LUMISE-TABS'),
				id = wrp.attr('data-id');
					
			if (!hist)
				hist = {};
			else hist = JSON.parse(hist);
			
			if (id === '' || !hist[id] || $(this).find('a[href="'+hist[id]+'"]').length === 0)
				$(this).find('a').first().trigger('click');
			else $(this).find('a[href="'+hist[id]+'"]').first().trigger('click');
			
		});
		
		$('.lumise_checkboxes').sortable({
			start: function(e, ui){
				var wrp = $(ui.item[0]).parent();
				wrp.css({'min-height': 0});
				wrp.css({'min-height': wrp.height()});
			}
		}).find('.field_children input[type="radio"]').on('change', function() {
			$(this).closest('div.lumise_checkbox').find('input.action_check').prop({checked: true});
		});
				
		if (document.lumiseconfig && lumise[document.lumiseconfig.main])
			lumise[document.lumiseconfig.main].init(document.lumiseconfig);
			
		if (document.getElementById('lumise-rss-display')) {
			$.ajax({
				url: LumiseDesign.ajax,
				method: 'POST',
				data: LumiseDesign.filter_ajax({
					action: 'get_rss',
					nonce: 'LUMISE_ADMIN:'+LumiseDesign.nonce
				}),
				statusCode: {
					403: function(){
						alert(LumiseDesign.js_lang.error_403);
					}
				},
				success: function(data){
					document.getElementById('lumise-rss-display').innerHTML = data;
				}
			});
		}	
		
	});
	
	window.lumise_font_preview = function(family, url, preview) {
		
		$('#font-ttf-upload').show();
		
		try {
			if (!document.fonts.check('1px '+family)) {
				new FontFace(family, url).load('1px '+family, 'a').then(function(font){
					
					document.fonts.add(font);
					$(preview).css({fontFamily: family, display: 'inline-block'}).html('Font Preview');
					
				});
			}else $(preview).css({fontFamily: family, display: 'inline-block'}).html('Font Preview');
		} catch (ex) {
			$(preview).css({fontFamily: family, display: 'inline-block'}).html('Font Preview');
		}
	}
	
	window.addEventListener('message', function(e) {
				
		if (e.origin != 'https://services.lumise.com')
			return;

		if (e.data && e.data.action) {
			switch (e.data.action) {
				case 'fonts' : 
					$('.lumise-field-google_fonts textarea[data-func="value"]').val(JSON.stringify(e.data.fonts));
					var txt, list = '';
					Object.keys(e.data.fonts).map(function(f){
						txt = decodeURIComponent(f).replace(/\ /g, '+')+':'+e.data.fonts[f][1];
						list += '<li data-n="'+f+'" data-f="'+e.data.fonts[f][0]+'" data-s="'+e.data.fonts[f][1]+'">';
						list += '<link rel="stylesheet" href="//fonts.googleapis.com/css?family='+txt+'" />';
						list += '<font style="font-family: '+decodeURIComponent(f)+';">'+decodeURIComponent(f)+'</font>';
						list += '<delete data-act="delete">Delete</delete>';
						list += '</li>';
					});
					$('.lumise-field-google_fonts').find('ul').html(list);
				break;
			}
		}
	});
		
	$.fn.shake = function(){
		return this.focus().
			animate({marginLeft: -30}, 100).
			animate({marginLeft: 20}, 100).
			animate({marginLeft: -10}, 100).
			animate({marginLeft: 5}, 100).
			animate({marginLeft: 0}, 100);
	};
	
	(function (original) {
		jQuery.fn.clone = function () {
			var result           = original.apply(this, arguments),
			    my_textareas     = this.find('textarea').add(this.filter('textarea')),
			    result_textareas = result.find('textarea').add(result.filter('textarea')),
			    my_selects       = this.find('select').add(this.filter('select')),
			    result_selects   = result.find('select').add(result.filter('select'));
			
			for (var i = 0, l = my_textareas.length; i < l; ++i) $(result_textareas[i]).val($(my_textareas[i]).val());
			for (var i = 0, l = my_selects.length;   i < l; ++i) result_selects[i].selectedIndex = my_selects[i].selectedIndex;
			
			return result;
		};
	}) (jQuery.fn.clone);
	
})(jQuery);

/**
 * hermite-resize - Canvas image resize/resample using Hermite filter with JavaScript.
 * @version v2.2.4
 * @link https://github.com/viliusle/miniPaint
 * @license MIT
 */
function Hermite_class(){var t,a,e=[];this.init=void(t=navigator.hardwareConcurrency||4),this.getCores=function(){return t},this.resample_auto=function(t,a,e,r,i){var h=this.getCores();window.Worker&&h>1?this.resample(t,a,e,r,i):(this.resample_single(t,a,e,!0),void 0!=i&&i())},this.resize_image=function(t,a,e,r,i){var h=document.getElementById(t),o=document.createElement("canvas");if(o.width=h.width,o.height=h.height,o.getContext("2d").drawImage(h,0,0),void 0==a&&void 0==e&&void 0!=r&&(a=h.width/100*r,e=h.height/100*r),void 0==e){var n=h.width/a;e=h.height/n}a=Math.round(a),e=Math.round(e);var s=function(){var t=o.toDataURL();h.width=a,h.height=e,h.src=t,t=null,o=null};void 0==i||1==i?this.resample(o,a,e,!0,s):(this.resample_single(o,a,e,!0),s())},this.resample=function(r,i,h,o,n){var s=r.width,d=r.height;i=Math.round(i);var c=d/(h=Math.round(h));if(e.length>0)for(u=0;u<t;u++)void 0!=e[u]&&(e[u].terminate(),delete e[u]);e=new Array(t);for(var g=r.getContext("2d"),v=[],l=2*Math.ceil(d/t/2),f=-1,u=0;u<t;u++){var M=f+1;if(!(M>=d)){f=M+l-1,f=Math.min(f,d-1);var m=l;m=Math.min(l,d-M),v[u]={},v[u].source=g.getImageData(0,M,s,l),v[u].target=!0,v[u].start_y=Math.ceil(M/c),v[u].height=m}}!0===o?(r.width=i,r.height=h):g.clearRect(0,0,s,d);for(var w=0,u=0;u<t;u++)if(void 0!=v[u].target){w++;var p=new Worker(a);e[u]=p,p.onmessage=function(t){w--;var a=t.data.core;e[a].terminate(),delete e[a];var r=Math.ceil(v[a].height/c);v[a].target=g.createImageData(i,r),v[a].target.data.set(t.data.target),g.putImageData(v[a].target,0,v[a].start_y),w<=0&&void 0!=n&&n()};var _={width_source:s,height_source:v[u].height,width:i,height:Math.ceil(v[u].height/c),core:u,source:v[u].source.data.buffer};p.postMessage(_,[_.source])}},a=window.URL.createObjectURL(new Blob(["(",function(){onmessage=function(t){for(var a=t.data.core,e=t.data.width_source,r=t.data.height_source,i=t.data.width,h=t.data.height,o=e/i,n=r/h,s=Math.ceil(o/2),d=Math.ceil(n/2),c=new Uint8ClampedArray(t.data.source),g=(c.length,i*h*4),v=new ArrayBuffer(g),l=new Uint8ClampedArray(v,0,g),f=0;f<h;f++)for(var u=0;u<i;u++){var M=4*(u+f*i),m=0,w=0,p=0,_=0,y=0,b=0,C=0,I=f*n,D=Math.floor(u*o),R=Math.ceil((u+1)*o),U=Math.floor(f*n),A=Math.ceil((f+1)*n);R=Math.min(R,e),A=Math.min(A,r);for(var x=U;x<A;x++)for(var B=Math.abs(I-x)/d,L=u*o,j=B*B,k=D;k<R;k++){var q=Math.abs(L-k)/s,E=Math.sqrt(j+q*q);if(!(E>=1)){var W=4*(k+x*e);C+=(m=2*E*E*E-3*E*E+1)*c[W+3],p+=m,c[W+3]<255&&(m=m*c[W+3]/250),_+=m*c[W],y+=m*c[W+1],b+=m*c[W+2],w+=m}}l[M]=_/w,l[M+1]=y/w,l[M+2]=b/w,l[M+3]=C/p}var z={core:a,target:l};postMessage(z,[l.buffer])}}.toString(),")()"],{type:"application/javascript"})),this.resample_single=function(t,a,e,r){for(var i=t.width,h=t.height,o=i/(a=Math.round(a)),n=h/(e=Math.round(e)),s=Math.ceil(o/2),d=Math.ceil(n/2),c=t.getContext("2d"),g=c.getImageData(0,0,i,h),v=c.createImageData(a,e),l=g.data,f=v.data,u=0;u<e;u++)for(var M=0;M<a;M++){var m=4*(M+u*a),w=0,p=0,_=0,y=0,b=0,C=0,I=0,D=u*n,R=Math.floor(M*o),U=Math.ceil((M+1)*o),A=Math.floor(u*n),x=Math.ceil((u+1)*n);U=Math.min(U,i),x=Math.min(x,h);for(var B=A;B<x;B++)for(var L=Math.abs(D-B)/d,j=M*o,k=L*L,q=R;q<U;q++){var E=Math.abs(j-q)/s,W=Math.sqrt(k+E*E);if(!(W>=1)){var z=4*(q+B*i);I+=(w=2*W*W*W-3*W*W+1)*l[z+3],_+=w,l[z+3]<255&&(w=w*l[z+3]/250),y+=w*l[z],b+=w*l[z+1],C+=w*l[z+2],p+=w}}f[m]=y/p,f[m+1]=b/p,f[m+2]=C/p,f[m+3]=I/_}!0===r?(t.width=a,t.height=e):c.clearRect(0,0,i,h),c.putImageData(v,0,0)}};window.HERMITE = new Hermite_class();
