jQuery(document).ready(function($){
	
	//$('#woocommerce-product-data ul.wc-tabs li.lumise_tab a').trigger('click');
	
	var storage_products = {},
		trigger = function( obj ) {

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

				if (evs[0] === '')
					obj.el.off(evs[1]).on( evs[1], obj, func );
				else obj.el.find( evs[0] ).off(evs[1]).on( evs[1], obj, func );

			});

		}
	},
		invert = function(color) {

			var r,g,b;

			if ( color !== undefined && color.indexOf('rgb') > -1) {

				color = color.split(',');
				r = parseInt(color[0].trim());
				g = parseInt(color[1].trim());
				b = parseInt(color[2].trim());

			} else if(color !== undefined) {
				if (color.length < 6)
					color += color.replace('#', '');
				var cut = (color.charAt(0)=="#" ? color.substring(1,7) : color.substring(0,6));
				r = (parseInt(cut.substring(0,2),16)/255)*0.213;
				g = (parseInt(cut.substring(2,4),16)/255)*0.715;
				b = (parseInt(cut.substring(4,6),16)/255)*0.072;
			}

			return (r+g+b < 0.5) ? '#DDD' : '#333';
		},
		lightbox = function(ops) {

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

		},
		render_products = function(res) {
			
			storage_products = res;
			
			var cates = ['<ul data-view="categories">',
							'<h3>'+lumisejs._i56+'</h3>',
							'<li data-id="" '+(res.category === '' ? 'class="active"' : '')+' data-lv="0"> '+lumisejs._i57+'</li>'],
				prods = ['<h3 data-view="top"><input type="search" value="'+res.s+'" placeholder="'+lumisejs._i63+'" />'+(
					ops.products.cfg.can_create_new ? '<a href="#new-product"><i class="dashicons dashicons-plus"></i> '+lumisejs._i59+'</a>' : ''
				)+'</h3>','<ul data-view="items">'];

			if (res.categories) {
				res.categories.map(function(c) {
					cates.push('<li '+(res.category == c.id ? 'class="active"' : '')+' data-id="'+c.id+'" data-lv="'+c.lv+'">'+'&mdash;'.repeat(c.lv)+' '+c.name+'</li>');
				});
			}

			if (res.products && res.products.length > 0) {

				res.products.map(function(p) {
					
					try {
						var stages = JSON.parse(decodeURIComponent(atob(p.stages)));
					} catch (ex) {
						var stages = {};
					};
					
					try {
						var attrs = JSON.parse(decodeURIComponent(atob(p.attributes)));
					} catch (ex) {
						var attrs = {};
					};
					
					var color = lumisejs.color;
					
					if (stages.stages !== undefined)
						stages = stages.stages;
					
					Object.keys(attrs).map(function(k) {
						if (
							attrs[k].type == 'product_color' && 
							attrs[k].values !== '' &&
							attrs[k].values !== null
						) {
							
							try {
								
								if (typeof attrs[k].values == 'string')
									attrs[k].values = JSON.parse(attrs[k].values);
								
								color = attrs[k].values.options[0]['value'];
								
								attrs[k].values.options.map(function(v) {
									if (v.default)
										color = v.value;
								});
								
							} catch (ex) {};
								
						}
							
					});
						
					var f = Object.keys(stages)[0] !== 'colors' ? Object.keys(stages)[0] : Object.keys(stages)[1],
						fstage = stages[f],
						current_product = $('#lumise_product_base').val(),
						img_url = '';
					
					if(fstage.source === undefined || fstage.source == 'raws')
						img_url = lumisejs.assets_url + 'raws/' + fstage.url;
					else
						img_url = lumisejs.upload_url + fstage.url;
						
					prods.push(
						'<li data-id="'+p.id+'"'+((current_product == p.id)?' data-current="true"':'')+' data-name="'+p.name.toLowerCase().trim().replace(/[^a-z0-9 ]/gmi, "")+'">\
							<span data-view="thumbn" data-start="'+ops.products.cfg.action_text+'">\
								<img style="background: '+(fstage.color ? fstage.color : color)+'" src="'+img_url+'" />\
							</span>\
							<span data-view="name">'+p.name+'</span>\
						</li>'
					)
				});

			}else prods.push('<li data-view="noitem">'+lumisejs._i42+'</li>');
			
			if (res.limit < res.total) {
				var pagination = ['<li data-view="pagination">', '<ul>'],
					pages = Math.ceil(res.total/res.limit),
					page = Math.ceil(res.index/res.limit);
				for(var i=1; i<=pages; i++) {
					pagination.push('<li data-page="'+i+'" '+(page == i ? ' class="lumise-color"' : '')+'>'+i+'</li>');
				}
				pagination.push('</ul></li>');
				prods.push(pagination.join(''));
			}
			
			$('#lumise-lightbox-content').html('<div id="lumise-list-items-wrp"></div>');
			$('#lumise-list-items-wrp').html(cates.join('')).append(prods.join(''));
			
			trigger({
				
				el: $('#lumise-list-items-wrp'),
				
				events: {
					'ul[data-view="categories"] li': 'category',
					'ul[data-view="items"] li[data-id]': 'product',
					'h3[data-view="top"] input:keyup': 'search',
					'h3[data-view="top"] a[href="#new-product"]': 'new_product',
					'li[data-view="pagination"] li[data-page]': 'page'
				},
				
				category: function() {
					
					var wrp = $(this).closest('#lumise-change-products-wrp'),
						id = this.getAttribute('data-id');
					
					$('#lumise-list-items-wrp').css({background: '#f2f2f2'}).html('<center><i class="lumise-spinner x3"></i></center>');
					
					ops.products.query.s = storage_products.s;
					ops.products.query.category = id;
					ops.products.query.index = 0;
					
					$.ajax({
						url: lumisejs.admin_ajax_url,
						method: 'POST',
						data: ops.products.query,
						statusCode: {
							403: function(){
								alert('Error 403');
							}
						},
						success:  function(res) {
							
							ops.products.res = res;
							render_products(ops.products.res);
							
						}
					});
					
				},
				
				product: function(e) {
					
					let id = this.getAttribute('data-id'),
						product = ops.products.res.products.filter(function(p){return p.id == id;});
					
					$(this).closest('#lumise-lightbox').remove();
					$('body').css({overflow: ''});
					
					if (typeof ops.products.cfg.action_fn == 'function')
						return ops.products.cfg.action_fn(product[0]);
						
					$('#lumise_product_base').val(product[0].id);
					render_product(product[0]);
					ops.current_product = product[0].id;
					
					if ($('#_regular_price').length > 0 && $('#_regular_price').val() === '')
						$('#_regular_price').val(10);
					
				},
				
				new_product: function(e) {
					
					$('#lumise-lightbox-content').addClass('full-screen').html('<iframe src="'+lumisejs.admin_url+'&lumise-page=product&callback=edit-cms-product"></iframe>');
						
				},
				
				search: function(e) {
					
					if (e.keyCode !== 13)
						return;
					
					$('#lumise-list-items-wrp').css({background: '#f2f2f2'}).html('<center><i class="lumise-spinner x3"></i></center>');
					
					ops.products.query.s = this.value.toLowerCase();
					ops.products.query.category = (storage_products.category ? storage_products.category : '');
					ops.products.query.index = 0;
						
					$.ajax({
						url: lumisejs.admin_ajax_url,
						method: 'POST',
						data: ops.products.query,
						statusCode: {
							403: function(){
								alert('Error 403');
							}
						},
						success:  function(res) {
							
							ops.products.res = res;
							render_products(ops.products.res);
							
						}
					});
					
					e.preventDefault();
					
				},
				
				page: function(e) {
						
					var p = parseInt(this.getAttribute('data-page'));
					
					$('#lumise-list-items-wrp').css({background: '#f2f2f2'}).html('<center><i class="lumise-spinner x3"></i></center>');
					
					var limit = parseInt(storage_products.limit);
					
					if (isNaN(limit))
						limit = 2;
					
					ops.products.query.s = storage_products.s ? storage_products : '';
					ops.products.query.category = (storage_products.category ? storage_products.category : '');
					ops.products.query.index = (p*limit)-limit;
					
					$.ajax({
						url: lumisejs.admin_ajax_url,
						method: 'POST',
						data: ops.products.query,
						statusCode: {
							403: function(){
								alert('Error 403');
							}
						},
						success:  function(res) {
							
							ops.products.res = res;
							render_products(ops.products.res);
							
						}
					});
					
					e.preventDefault();
					
				}
				
			});

		},
		render_designs = function(res) {
			
			var cates = [
					'<ul data-view="categories">',
					'<h3>'+lumisejs._i56+'</h3>',
					'<li data-id="" '+(res.category === '' ? 'class="active"' : '')+' data-lv="0"> '+lumisejs._i57+'</li>'
				],
				prods = [
					'<h3 data-view="top"><input id="search-templates-inp" type="search" placeholder="'+lumisejs._i67+'" value="'+encodeURIComponent(res.q)+'" /></h3>',
					'<ul data-view="items">'
				];
			
			if (res.categories_full) {
				res.categories_full.map(function(c) {
					cates.push('<li data-id="'+c.id+'" '+(c.id == res.category ? 'class="active"' : '')+' data-lv="'+(c.lv ? c.lv : 0)+'">'+('&mdash;'.repeat(c.lv))+' '+c.name+'</li>');
				});
			}
			
			if (res.items && res.items.length > 0) {

				res.items.map(function(p) {
						
					prods.push(
						'<li data-id="'+p.id+'"'+((ops.current_design == p.id)?' data-current="true"':'')+'>\
							<span data-view="thumbn" data-start="'+lumisejs._i58+'">\
								<img src="'+(p.screenshot.match(/^(?:http|https):\/\//gm) ? p.screenshot : lumisejs.upload_url + p.screenshot  )+'" />\
							</span>\
							<span data-view="name">'+p.name+'</span>\
						</li>'
					);
				});
				
				if (res.index+res.limit < res.total) {
					prods.push(
						'<li data-loadmore="'+(res.index+res.limit)+'">\
							<span>'+lumisejs._i68+'</span>\
						</li>'
					);
				}
				
			}
			else prods.push('<li data-view="noitem" data-category="'+res.category+'">'+lumisejs._i42+'</li>');
			
				
			if (res.index == 0) {
				
				ops.designs = res.items;
				
				cates.push('</ul>');
				prods.push('</ul>');
				
				$('#lumise-lightbox-content').html('<div id="lumise-list-items-wrp"></div>');
				$('#lumise-list-items-wrp').html(cates.join('')).append(prods.join(''));
				
			}else{
				
				ops.designs = ops.designs.concat(res.items);
				
				$('#lumise-lightbox-content ul[data-view="items"] li[data-loadmore]').remove();
				prods[0] = '';
				prods[1] = '';
				$('#lumise-lightbox-content ul[data-view="items"]').append(prods.join(''));
			}
			
			trigger({
				
				el: $('#lumise-list-items-wrp'),
				
				events: {
					'ul[data-view="categories"] li': 'category',
					'ul[data-view="items"] li[data-id]': 'design',
					'h3[data-view="top"] input:keyup': 'search',
					'li[data-loadmore]': 'load_more',
				},
				
				category: function(e) {

					load_designs({
						category: this.getAttribute('data-id'), 
						index: 0, 
						q: $('#search-templates-inp').val()
					});
					
					e.preventDefault();
					
				},
				
				design: function(e) {
					
					var id = this.getAttribute('data-id'),
						design = ops.designs.filter(function(p){return p.id == id;});
					
					$(this).closest('#lumise-lightbox').remove();
					$('body').css({overflow: ''});
					
					render_design(design[0]);
					
				},
				
				load_more: function(e) {
					
					this.innerHTML = '<i class="lumise-spinner x3"></i>';
					this.style.background = 'transparent';
					$(this).off('click');
					
					load_designs({
						category: this.getAttribute('data-category'),
						index: this.getAttribute('data-loadmore'),
						q: $('#search-templates-inp').val()
					});
						
				},
				
				search: function(e) {
					
					if (e.keyCode !== undefined && e.keyCode === 13)
						load_designs({q: this.value});
					
				}
				
			});

		},
		render_product = function(data) {
			
			window['lumise-seclect-base'].style.display = '';
			
			var data_stages;
					
			if (typeof data.stages == 'string')
				var data_stages = JSON.parse(decodeURIComponent(atob(data.stages)));
			else data_stages = data.stages;
				
			var stage = {},
				html = '',
				nav = '',
				btn = '',
				tabs = '',
				design = '',
				color = data.color ? data.color : '#f0f0f0',
				img_url;
				
			if (data.attributes !== undefined && data.attributes !== '') {
				var attrs = JSON.parse(decodeURIComponent(atob(data.attributes)));
				
				Object.keys(attrs).map(function(a) {
					if (attrs[a].type == 'product_color' && attrs[a].values !== '') {
						try {
							if (typeof attrs[a].values == 'string')
								attrs[a].values = JSON.parse(attrs[a].values);
							if (attrs[a].values.options.length > 0)
								color = attrs[a].values.options[0].value;
							attrs[a].values.options.map(function(o) {
								if (o.default)
									color = o.value;
							});
						} catch (ex) {console.log(ex);};
					}
				});
			};
			
			if (typeof data_stages != 'object')
				data_stages = {};
				
			var stages = data_stages.stages	!== undefined ? data_stages.stages : data_stages;
				
			if (Object.keys(stages).length > 0) {
				
				Object.keys(stages).map(function(s, i){
						
					stage = stages[s];
					design = lumisejs.current_design !== undefined && lumisejs.current_design[s] !== undefined ?
							lumisejs.current_design[s] : null;
					
					if (design !== null) {
						design.scr = '<img src="'+(design.screenshot.match(/^(?:http|https):\/\//gm) ? design.screenshot : lumisejs.upload_url + design.screenshot  )+'" height="'+design.offset.natural_height+'" width="'+design.offset.natural_width+'" class="lumise-design-view" style="'+design.css+'" />';	
					};
					
					if(stage.source == 'raws')
						img_url = lumisejs.assets_url + 'raws/' + stage.url;
					else
						img_url = lumisejs.upload_url + stage.url;
					
					nav += '<li'+(i==0 ? ' class="active"' : '')+'><a href="#lumise-tab-'+s+'">'+(stage.label ? stage.label : lumisejs['_'+s])+'</a></li>';
					
					tabs += '<div class="lumise_tab_content'+(i==0 ? ' active' : '')+'" id="lumise-tab-'+s+'" data-stage="'+s+'">\
								<div class="lumise-stage-settings lumise-product-design" id="lumise-product-design-'+s+'">\
									<div class="lumise-stage-body">\
										<div class="lumise-stage-design-view">\
											<img style="background:'+color+'" src="'+img_url+'" width="'+stage.product_width+'" height="'+stage.product_height+'" class="lumise-stage-image" />\
											<div class="lumise-stage-editzone" style="margin-left: '+stage.edit_zone.left+'px;margin-top: '+stage.edit_zone.top+'px;width: '+stage.edit_zone.width+'px;height: '+stage.edit_zone.height+'px;border-color: '+invert(color)+';border-radius:'+stage.edit_zone.radius+'px">\
												<div class="design-template-inner" data-id="'+(design !== null ? design.id : '')+'" style="border-radius:'+stage.edit_zone.radius+'px">'+(design !== null ? design.scr : '')+'</div>\
											</div>\
										</div>\
										<div class="lumise-stage-btn">\
											<button class="lumise-button lumise-button-primary" data-func="select-design">\
												<i class="fa fa-paint-brush"></i>\
												'+lumisejs._i58+'\
											</button> &nbsp; \
											<button class="lumise-button lumise-button-link-delete'+(design === null ? ' hidden' : '')+'" data-func="clear-design" title="'+lumisejs._i69+'">\
												<i class="fa fa-trash"></i>\
												'+lumisejs._i70+'\
											</button> &nbsp; \
											<button class="lumise-button'+(design === null ? ' hidden' : '')+'" data-func="download-design" title="'+lumisejs._i73+'">\
												<i class="fa fa-download"></i>\
												'+lumisejs._i72+'\
											</button>\
										</div>\
										<div class="editzone-ranges'+(design !== null ? '' : ' hidden')+'">\
											<div class="edr-row design-scale" style="">\
												<label>Design scale:</label>\
												<input type="range" min="10" max="200" value="'+(design !== null ? design.scale : '')+'">\
											</div>\
										</div>\
									</div>\
								</div>\
							</div>';
					
				});
				
				if (lumisejs.inline_edit === true) {
					btn = '<span data-view="product-name">\
							<a title="'+data.name+'" href="'+lumisejs.admin_url+'&lumise-page=product&id='+data.id+'&callback=edit-base-product" data-func="edit-base-product" class="lumise-button" title="'+lumisejs._i61+'">\
								<i class="fa fa-pencil"></i> \
								'+lumisejs._i61+'\
							</a>\
						</span>';
				} else {
					btn = '';
				};
				
				nav = '<ul class="lumise_tab_nav">'+nav+'</ul>';
				
				html = '<div class="lumise_tabs_wrapper" id="lumise-stages-wrp" data-id="stages">\
							'+btn+'\
							<div class="lumise_tab_nav_wrap">\
								<i data-move="left" class="fa fa-chevron-left"></i>\
								<div class="lumise_tab_nav_inner">\
									'+nav+'\
								</div>\
								<i data-move="right" class="fa fa-chevron-right"></i>\
							</div>\
							<div class="lumise_tabs">'+
								tabs+
							'</div>\
						</div>';
				
			}
			
			$('#lumise-product-base').html(html).addClass('set-product');
				
			trigger({
				
				el : $('#lumise-product-base'),
				
				events : {
					'button[data-func="select-design"]': 'select_design',
					'button[data-func="clear-design"]': 'delete_design',
					'button[data-func="download-design"]': 'download_design',
					'a[data-func="edit-base-product"]': 'edit_product',
					'a[data-func="remove-base-product"]': 'delete_product',
					'ul.lumise_tab_nav li a': 'tab',
					'.editzone-ranges .design-scale input[type="range"]:input': 'design_scale',
					'div.lumise_tab_nav_wrap': 'is_stage_accord',
					'div.lumise_tab_nav_wrap>i[data-move]': 'stage_accord'
				},
				
				select_design : function(e) {
					e.preventDefault();
					load_designs({category: '', index: 0, s: ''});
				},
				
				delete_design : function(e) {
							
					e.preventDefault();
					
					$(this).addClass('hidden').
							parent().find('button[data-func="download-design"]').addClass('hidden').
							closest('.lumise-stage-body').
							find('.lumise-stage-editzone .design-template-inner').
							html('').attr({'data-id': ''}).
							closest('.lumise-stage-body').
							find('.editzone-ranges').
							addClass('hidden');
							
					before_submit();
					
				},
				
				download_design : function(e) {
					
					e.preventDefault();
					
					var canvas = document.createElement('canvas'),
						editcanvas = document.createElement('canvas'),
						img = $(this).closest('.lumise-stage-body').find('img.lumise-stage-image').get(0),
						temp = $(this).closest('.lumise-stage-body').find('img.lumise-design-view').get(0),
						editzone = $(this).closest('.lumise-stage-body').find('.lumise-stage-editzone').get(0),
						ctx = canvas.getContext('2d'),
						ectx = editcanvas.getContext('2d'),
						ratio = img.width/img.naturalWidth;
					
					canvas.width = img.naturalWidth;
					canvas.height = img.naturalHeight;
					
					editcanvas.width = editzone.offsetWidth/ratio;
					editcanvas.height = editzone.offsetHeight/ratio;
					
					ctx.fillStyle = img.style.backgroundColor;
					ctx.fillRect(0, 0, canvas.width, canvas.height);
					
					ectx.drawImage(temp, temp.offsetLeft/ratio, temp.offsetTop/ratio, temp.width/ratio, temp.height/ratio);
					
					var top = parseFloat(editzone.style.marginTop.replace('px', ''))/ratio,
						left = parseFloat(editzone.style.marginLeft.replace('px', ''))/ratio;
					
					left += (canvas.width/2) - (editcanvas.width/2);
					top += (canvas.height/2) - (editcanvas.height/2);
					
					var x = left,
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
					
					var dataURL = canvas.toDataURL('image/jpeg', 1).split(',');

					var binStr = atob(dataURL[1]),
						len = binStr.length,
						arr = new Uint8Array(len);
	
					for (var i = 0; i < len; i++) {
						arr[i] = binStr.charCodeAt(i);
					}
	
					var blob = new Blob([arr], {
						type: dataURL[0].substring(dataURL[0].indexOf('image/'), dataURL[0].indexOf(';')-1)
					});

					var a = document.createElement('a'),
						name = $('#lumise-stages-wrp [data-func="edit-base-product"]').attr('title');
					
					name += '-'+$('#lumise-stages-wrp ul.lumise_tab_nav li.active a').text();
					
					a.download = name+'.jpg';
					a.href = URL.createObjectURL(blob);
					a.click();
					URL.revokeObjectURL(a.href);
					
					delete a;
					delete blob;
					delete canvas;
					delete editcanvas;
				},
				
				edit_product : function(e) {
					
					lightbox({
						content: '<iframe src="'+this.getAttribute('href')+'"></iframe>',
						class: 'full-width'
					});
					
					e.preventDefault();
					
				},
				
				delete_product : function(e) {
					
					$('#lumise_product_base, #lumise_design_template').val('');
					$('#lumise-product-base').html('').removeClass('set-product');
					
					e.preventDefault();
					
				},
				
				tab: function(e) {
					
					var wrp = $(this).closest('div#lumise-stages-wrp');
					
					wrp.find('div.lumise_tab_content.active, ul.lumise_tab_nav li.active').removeClass('active');
					$(this).parent().addClass('active');
					
					wrp.find(this.getAttribute('href')).addClass('active');
					
					e.preventDefault();
					
				},
					
				design_scale: function(e) {
					
					var img = $(this).closest('.lumise-stage-body').find('.design-template-inner img');
					
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
					
				},
				
				is_stage_accord: function(e) {
					
					var el = $(this),
						nav = el.find('ul.lumise_tab_nav'),
						wrp = el.find('div.lumise_tab_nav_inner'),
						act = nav.find('li.active');
						
					if (nav.width() > wrp.width()) {
						el.addClass('is_accord');
						if (nav.get(0).offsetLeft + nav.width() < wrp.width()*0.5)
							nav.css({left: (wrp.width()-nav.width())+'px'});
					} else {
						el.removeClass('is_accord');
						nav.css({left: '0px'});
					};
					
					if (
						this.first_time === undefined && 
						act && 
						act.get(0).offsetLeft > wrp.parent().width()
					)nav.css({left:(-act.get(0).offsetLeft+(wrp.width()-act.width()))+'px'});
						
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
			
			$('body').css({overflow: ''});
			$('#lumise_product_base').val(data.id);
			$('#lumise-enable-customize, #lumise_product_data a[data-func="remove-base-product"]').removeClass('hidden');
			
			$('#lumise-product-base .lumise_tab_nav_wrap').trigger('click');
			
		},
		render_design = function(data, stage) {
			
			var view = stage !== undefined ? 
						$('#lumise-product-base .lumise_tabs .lumise_tab_content[data-stage="'+stage+'"]')
						: $('#lumise-product-base .lumise_tabs .lumise_tab_content.active')
				
			if (view.length === 0)
				return;
			
			var img = new Image();
			img.src =(data.screenshot.match(/^(?:http|https):\/\//gm) ? data.screenshot : lumisejs.upload_url + data.screenshot  );
			img.className = 'lumise-design-view';
			
			view.find('.lumise-stage-btn button[data-func="clear-design"],.lumise-stage-btn button[data-func="download-design"]').removeClass('hidden');
			view.find('.design-template-inner').
				css({"border-radius" : view.find('.lumise-stage-editzone').css('border-radius') }).
				attr({"data-id": data.id}).html('').append(img);
			
			img.onload = function(){
				
				if (this.width > this.parentNode.offsetWidth) {
					this.width = this.parentNode.offsetWidth;
					this.height = this.parentNode.offsetWidth*(this.naturalHeight/this.naturalWidth);
				};
				
				this.style.left = ((this.parentNode.offsetWidth/2)-(this.width/2))+'px';
				this.style.top = ((this.parentNode.offsetHeight/2)-(this.height/2))+'px';
				
				var rang = $(this).closest('.lumise-stage-body').find('.editzone-ranges');
				
				rang.removeClass('hidden');
				
				rang.find('input').val((this.width/this.naturalWidth)*100).trigger('input');	
				
			};
			
			
		},
		load_designs = function(ops) {
			
			if (ops.index === undefined || ops.index === 0) {
				lightbox({
					content: '<center><i class="lumise-spinner x3"></i></center>'
				});
			};
			
			$.ajax({
				url: lumisejs.admin_ajax_url,
				method: 'POST',
				data: {
					nonce: 'LUMISE-SECURITY-BACKEND:'+lumisejs.nonce_backend,
					ajax: 'backend',
					action: 'templates',
					category: ops.category !== undefined ? ops.category : '',
					q: ops.q !== undefined ? ops.q : '',
					index: ops.index !== undefined ? ops.index : 0
				},
				statusCode: {
					403: function(){
						alert('Your session has expired, please reload the page!');
					}
				},
				success: render_designs
			});
			
		},
		before_submit = function() {
			
			if ($('#lumise_product_base').val() === '') {
				$('#lumise_design_template').val('');
				return;
			};
			
			var templ = {};
			$('#lumise_product_data .lumise_tabs .lumise_tab_content').each(function() {
				var _this = $(this),
					im = _this.find('.design-template-inner img').get(0);
				_this.css({display: 'inline-block'});
				if (im !== undefined) {
				   	templ[this.getAttribute('data-stage')] = {
						id: _this.find('.design-template-inner').data('id'),
						scale: _this.find('.design-scale input').val(),
						css: _this.find('.design-template-inner img').attr('style'),
						offset: {
							top: im.offsetTop,
							left: im.offsetLeft,
							width: im.offsetWidth,
							height: im.offsetHeight,
							natural_width: im.naturalWidth,
							natural_height: im.naturalHeight
						},
						screenshot: _this.find('.design-template-inner img').attr('src')
					}
				};
				_this.css({display: ''});
			});
			lumisejs.current_design = templ;
			$('#lumise_design_template').val(encodeURIComponent(JSON.stringify(templ)));
		},
		ops = {
			designs: [],
			products: {
				query: {},
				cfg: {}
			}
		};
		
	window.lumise_reset_products = function(data) {
		delete ops.products.res;
		$('#lumise-lightbox').remove();
		$('#lumise_product_base').val(data.id);
		before_submit();
		render_product(data);
	};
	
	window.load_product_bases = function(query, cfg) {
		
		lightbox({
			content: '<center><i class="lumise-spinner x3"></i></center>'
		});
		
		ops.products.query = {
			nonce: 'LUMISE-SECURITY-BACKEND:'+lumisejs.nonce_backend,
			ajax: 'backend',
			action: 'list_products',
			task: 'cms_product',
			s: '',
			category: '',
			index: 0
		};
		
		ops.products.cfg = {
			can_create_new: true,
			action_text: lumisejs._i64	
		};
		
		if (
			query !== undefined && 
			query !== null && 
			typeof query == 'object'
		) {
			ops.products.query = $.extend(true, ops.products.query, query);
			delete ops.products.res;
		};
		
		if (
			cfg !== undefined && 
			cfg !== null && 
			typeof cfg == 'object'
		) {
			ops.products.cfg = $.extend(true, ops.products.cfg, cfg);
		};
		
		
		if (ops.products.res !== undefined)
			return render_products(ops.products.res);
		
		$.ajax({
			url: lumisejs.admin_ajax_url,
			method: 'POST',
			data: ops.products.query,
			statusCode: {
				403: function(){
					alert('Error 403');
				}
			},
			success: function(res) {
				
				ops.products.res = res;
				render_products(ops.products.res);
				
			}
		});
		
	};
	
	trigger({
		
		el: $('#lumise_product_data'),
		
		events: {
			'a[data-func="products"]': 'products',
			'a[data-func="remove-base-product"]': 'remove_product',
			'#lumise-product-base:mousedown' : 'start_drag'
		},
		
		products: function(e) {
			
			e.preventDefault();
			
			load_product_bases();
			
		},
		
		remove_product: function(e) {
			$('#lumise-enable-customize, #lumise_product_data a[data-func="remove-base-product"]').addClass('hidden');
			$('#lumise-product-base').html('<p class="notice notice-warning">'+lumisejs._i71+'</p>');
			$('#lumise_product_base, #lumise_design_template').val('');
			$('html,body').animate({scrollTop: $('#lumise_product_data').offset().top-100});
			e.preventDefault();
		},
		
		start_drag: function(e) {
			
			if (e.target.tagName == 'IMG' && e.target.className == 'lumise-design-view') {
						
				var $this = $(e.target),
					clientX = e.clientX,
					clientY = e.clientY,
					left = e.target.offsetLeft,
					top = e.target.offsetTop,
					width = e.target.offsetWidth,
					height = e.target.offsetHeight;
				
				$(document).on('mousemove', function(e){
					
					var pw = $this.parent().width();
						ph = $this.parent().height(),
						new_left = (left+(e.clientX-clientX)),
						new_top = (top+(e.clientY-clientY));
						
					if (new_left < -width*0.85)
						new_left = -width*0.85;
					
					if (new_top < -height*0.85)
						new_top = -height*0.85;
						
					if (new_left > pw-(width*0.15))
						new_left = pw-(width*0.15);
					
					if (new_top > ph-(height*0.15))
						new_top = ph-(height*0.15);
					
					$this.css({
						left: new_left+'px',
						top: new_top+'px'
					});
						
				});
				
				$(document).on('mouseup', function(){
					$(document).off('mousemove mouseup');
				});
				
				e.preventDefault();
				
			}
		}

	});
	
	$('#lumise_product_data').closest('form').on('submit', before_submit);

	$('#product-type').on('change', function(e) {
		
		if (this.value == 'simple') {
			
			$('ul.product_data_tabs li.lumise_options.lumise_tab, #lumise-seclect-base').show();
			
			if (window.lumisejs.current_data !== undefined) {
				render_product(lumisejs.current_data);
			} else {
				$('#lumise-product-base').html('<p class="notice notice-warning">'+lumisejs._i71+'</p>');
				$('#lumise-enable-customize, #lumise_product_data a[data-func="remove-base-product"]').addClass('hidden');
			};
			
			$('#lumise_product_data').closest('div.dokan-other-options.dokan-edit-row').show();
			
		} else if (this.value == "variable") {
			
			$('#lumise-product-base').html(
				'<p class="notice notice-warning">This is variable product, Please set the Lumise Configuration in <a href="#" onclick="jQuery(\'li.variations_options a\').click();return false;">Variations Tab</a></p>'
			);
			
			$('#lumise-seclect-base').hide();
			$('ul.product_data_tabs li.lumise_options.lumise_tab').show();
			
			$('#lumise_product_data').closest('div.dokan-other-options.dokan-edit-row').hide();
			
		} else {
			$('ul.product_data_tabs li.lumise_options.lumise_tab').hide();
			$('#lumise_product_data').closest('div.dokan-other-options.dokan-edit-row').hide();
		};
		
	}).change();
	
	
	$('#product_type').on('change', function(e) {
		
		if (this.value == 'simple') {
			$('#lumise_product_data').closest('div.dokan-other-options.dokan-edit-row').show();
			
		} else {
			$('#lumise_product_data').closest('div.dokan-other-options.dokan-edit-row').hide();
		};
		
	}).change();
	
	$('select#field_to_edit option[value="delete_all"]').after('<option value="delete_all_lumise">Clear all Lumise Configuations</option>');
	$('select#field_to_edit+a').on('click', (e) => {
		if ($('select#field_to_edit').val() == 'delete_all_lumise') {
			if (confirm('Are you sure that you want to clear all Lumise Configuations?'))
			$('textarea.lumise-vari-inp').val('');
			$('div.variable_lumise_data').attr('data-empty', 'true').removeAttr('is').find('iframe').remove();
			$('div#woocommerce-product-data').append(`<div class="UIloading blockUI blockOverlay" style="z-index: 1000; border: none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background: rgb(255, 255, 255); opacity: 0.6; cursor: wait; position: absolute;"></div>`);
			$.ajax({
				url: lumisejs.admin_ajax_url,
				method: 'POST',
				data: {
					nonce: 'LUMISE-SECURITY-BACKEND:'+lumisejs.nonce_backend,
					ajax: 'backend',
					action: 'product_variation',
					clear_all_config: window.post_ID.value
				},
				success: (res) => {
					window.parent.jQuery('div#woocommerce-product-data div.UIloading').remove();
					window.parent.jQuery('div#variable_product_options div.toolbar button')
							.attr({'disabled': 'disabled'});
							
				}
			});
			e.preventDefault();
			return false;
		}
	});

});