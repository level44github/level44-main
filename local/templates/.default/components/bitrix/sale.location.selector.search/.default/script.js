BX.namespace('BX.Sale.component.location.selector');

if(typeof BX.Sale.component.location.selector.search == 'undefined' && typeof BX.ui != 'undefined' && typeof BX.ui.widget != 'undefined'){

	BX.Sale.component.location.selector.search = function(opts, nf){

		this.parentConstruct(BX.Sale.component.location.selector.search, opts);

		BX.merge(this, {
			opts: {

				usePagingOnScroll: 		true,
				pageSize: 				10,
				//scrollThrottleTimeout: 	100,
				arrowScrollAdditional: 	2,
				pageUpWardOffset: 		3,
				provideLinkBy: 			'id',
				isModified:				false,

				bindEvents: {

					'after-input-value-modify': function(){

						this.ctrls.fullRoute.value = '';
						this.isModified = true;

					},
					'after-select-item': function(itemId){

						var so = this.opts;
						var cItem = this.vars.cache.nodes[itemId];

						var path = cItem.DISPLAY;
						if(typeof cItem.PATH == 'object'){
							for(var i = 0; i < cItem.PATH.length; i++){
								path += ', '+this.vars.cache.path[cItem.PATH[i]]; // deprecated
							}
						}

						this.ctrls.inputs.fake.setAttribute('title', path);
						this.ctrls.fullRoute.value = path;

						if(typeof this.opts.callback == 'string' && this.opts.callback.length > 0 && this.opts.callback in window)
							window[this.opts.callback].apply(this, [itemId, this]);
                        if (Number(itemId) && this.isModified) {
                            $(".js-form__control[data-prop='ADDRESS']").val("")
                            this.isModified = false
                        }
					},
					'after-deselect-item': function(){
						this.ctrls.fullRoute.value = '';
						this.ctrls.inputs.fake.setAttribute('title', '');
					},
					'before-render-variant': function(itemData){

						if(itemData.PATH.length > 0){
							var path = '';
							for(var i = 0; i < itemData.PATH.length; i++)
								path += ', '+this.vars.cache.path[itemData.PATH[i]];

							itemData.PATH = path;
						}else
							itemData.PATH = '';

						var query = '';

						if(this.vars && this.vars.lastQuery && this.vars.lastQuery.QUERY)
							query = this.vars.lastQuery.QUERY;

						if(BX.type.isNotEmptyString(query)){
							var chunks = [];
							if(this.opts.wrapSeparate)
								chunks = query.split(/\s+/);
							else
								chunks = [query];

							itemData['=display_wrapped'] = BX.util.wrapSubstring(itemData.DISPLAY+itemData.PATH, chunks, this.opts.wrapTagName, true);
						}else
							itemData['=display_wrapped'] = BX.util.htmlspecialchars(itemData.DISPLAY);
					}
				}
			},
			vars: {
				cache: {
					path: 			{},
					nodesByCode: 	{}
				}
			},
			sys: {
				code: 'sls'
			}
		});

        this.handleInitStack(nf, BX.Sale.component.location.selector.search, opts);
        var $fakeLocationField = $(".bx-ui-sls-input-block.form-control input.bx-ui-sls-fake");

        $fakeLocationField.addClass("js-form__location");

        if (opts.required) {
            $fakeLocationField.addClass("is-required")
        }

        $fakeLocationField.show();

        $(document).trigger("set_validators");
	};
	BX.extend(BX.Sale.component.location.selector.search, BX.ui.autoComplete);
	BX.merge(BX.Sale.component.location.selector.search.prototype, {

		// member of stack of initializers, must be defined even if do nothing
		init: function(){
			// deprecated begin
			if(typeof this.opts.pathNames == 'object')
				BX.merge(this.vars.cache.path, this.opts.pathNames);
			// deprecated end

			this.pushFuncStack('buildUpDOM', BX.Sale.component.location.selector.search);
			this.pushFuncStack('bindEvents', BX.Sale.component.location.selector.search);
			},

		buildUpDOM: function(){

			var sc = this.ctrls,
				so = this.opts,
				sv = this.vars,
				ctx = this,
				code = this.sys.code;

			// full route node
			sc.fullRoute = BX.create('input', {
				props: {
					className: 'bx-ui-'+code+'-route'
				},
				attrs: {
					type: 'text',
					disabled: 'disabled',
					autocomplete: 'off'
				}
			});

			// todo: use metrics instead!
			BX.style(sc.fullRoute, 'paddingTop', BX.style(sc.inputs.fake, 'paddingTop'));
			BX.style(sc.fullRoute, 'paddingLeft', BX.style(sc.inputs.fake, 'paddingLeft'));
			BX.style(sc.fullRoute, 'paddingRight', '0px');
			BX.style(sc.fullRoute, 'paddingBottom', '0px');

			BX.style(sc.fullRoute, 'marginTop', BX.style(sc.inputs.fake, 'marginTop'));
			BX.style(sc.fullRoute, 'marginLeft', BX.style(sc.inputs.fake, 'marginLeft'));
			BX.style(sc.fullRoute, 'marginRight', '0px');
			BX.style(sc.fullRoute, 'marginBottom', '0px');

			if(BX.style(sc.inputs.fake, 'borderTopStyle') != 'none'){
				BX.style(sc.fullRoute, 'borderTopStyle', 'solid');
				BX.style(sc.fullRoute, 'borderTopColor', 'transparent');
				BX.style(sc.fullRoute, 'borderTopWidth', BX.style(sc.inputs.fake, 'borderTopWidth'));
			}

			if(BX.style(sc.inputs.fake, 'borderLeftStyle') != 'none'){
				BX.style(sc.fullRoute, 'borderLeftStyle', 'solid');
				BX.style(sc.fullRoute, 'borderLeftColor', 'transparent');
				BX.style(sc.fullRoute, 'borderLeftWidth', BX.style(sc.inputs.fake, 'borderLeftWidth'));
			}

			BX.prepend(sc.fullRoute, sc.container);

			sc.inputBlock = this.getControl('input-block');
			sc.loader = this.getControl('loader');
        },

		bindEvents: function(){

			var ctx = this;

			// quick links
			BX.bindDelegate(this.getControl('quick-locations', true), 'click', {tag: 'a'}, function(){
				ctx.setValueByLocationId(BX.data(this, 'id'));
			});

			this.vars.outSideClickScope = this.ctrls.inputBlock;
		},

		////////// PUBLIC: free to use outside

		// location id is just a value in terms of autocomplete
		setValueByLocationId: function(id, autoSelect){
			BX.Sale.component.location.selector.search.superclass.setValue.apply(this, [id, autoSelect]);
		},

		setValueByLocationIds: function(locationsData){
			if(locationsData.IDS)
			{
				this.displayPage(
					{
						'VALUE': locationsData.IDS,
						'order': {'TYPE_ID': 'ASC', 'NAME.NAME': 'ASC'}
					}
				);
			}
		},

		setValueByLocationCode: function(code, autoSelect){

			var sv = this.vars,
				so = this.opts,
				sc = this.ctrls,
				ctx = this;

			this.hideError();

			if(code == null || code == false || typeof code == 'undefined' || code.toString().length == 0){ // deselect

				this.resetVariables();

				BX.cleanNode(sc.vars);

				if(BX.type.isElementNode(sc.nothingFound))
					BX.hide(sc.nothingFound);

				this.fireEvent('after-deselect-item');
				this.fireEvent('after-clear-selection');

				return;
			};

			if(autoSelect !== false)
				sv.forceSelectSingeOnce = true;

			if(typeof sv.cache.nodesByCode[code] == 'undefined'){

				// lazyload it...
				this.resetNavVariables();

				ctx.downloadBundle({CODE: code}, function(data){

					ctx.fillCache(data, false); // storing item in the cache

					if(typeof sv.cache.nodesByCode[code] == 'undefined'){ // still not found
						ctx.showNothingFound();
					}else{

						var value = sv.cache.nodesByCode[code].VALUE;

						//////////////////
						if(so.autoSelectIfOneVariant || sv.forceSelectSingeOnce)
							ctx.selectItem(value);
						else
							ctx.displayVariants([value]);
						//////////////////
					}
				}, function(){
					sv.forceSelectSingeOnce = false;
				});

			}else{

				var value = sv.cache.nodesByCode[code].VALUE;

				if(sv.forceSelectSingeOnce)
					this.selectItem(value);
				else
					this.displayVariants([value]);

				sv.forceSelectSingeOnce = false;
			}
		},

		getNodeByValue: function(value){
			if(this.opts.provideLinkBy == 'id')
				return this.vars.cache.nodes[value];
			else
				return this.vars.cache.nodesByCode[value];
		},

		getNodeByLocationId: function(value){
			return this.vars.cache.nodes[value];
		},

		setValue: function(value){

			if(this.opts.provideLinkBy == 'id')
				BX.Sale.component.location.selector.search.superclass.setValue.apply(this, [value]);
			else
				this.setValueByLocationCode(value);
		},

		getValue: function(){
			if(this.opts.provideLinkBy == 'id')
				return this.vars.value === false ? '' : this.vars.value;
			else{
				return this.vars.value ? this.vars.cache.nodes[this.vars.value].CODE : '';
			}
		},

		getSelectedPath: function(){

			var sv = this.vars,
				result = [];

			if(typeof sv.value == 'undefined' || sv.value == false || sv.value == '')
				return result;

			if(typeof sv.cache.nodes[sv.value] != 'undefined'){
				var item = BX.clone(sv.cache.nodes[sv.value]);
				if(typeof item.TYPE_ID != 'undefined' && typeof this.opts.types != 'undefined')
					item.TYPE = this.opts.types[item.TYPE_ID].CODE;

				var path = item.PATH;
				delete(item.PATH);
				result.push(item);

				if(typeof path != 'undefined'){
					for(var k in path){
						var item = BX.clone(sv.cache.nodes[path[k]]);
						if(typeof item.TYPE_ID != 'undefined' && typeof this.opts.types != 'undefined')
							item.TYPE = this.opts.types[item.TYPE_ID].CODE;

						delete(item.PATH);

						result.push(item);
					}
				}
			}

			return result;
		},

		////////// PRIVATE: forbidden to use outside (for compatibility reasons)

		setInitialValue: function(){

			if(this.opts.selectedItem !== false) // there will be always a value as ID, no matter what this.opts.provideLinkBy is equal to
				this.setValueByLocationId(this.opts.selectedItem);
			else if(this.ctrls.inputs.origin.value.length > 0) // there colud be eiter ID or CODE
			{
				if(this.opts.provideLinkBy == 'id')
					this.setValueByLocationId(this.ctrls.inputs.origin.value);
				else
					this.setValueByLocationCode(this.ctrls.inputs.origin.value);
			}
		},

		addItem2Cache: function(item){
			this.vars.cache.nodes[item.VALUE] = item;
			this.vars.cache.nodesByCode[item.CODE] = item;
		},

		refineRequest: function(request){

			var filter = {};
			if(typeof request['QUERY'] != 'undefined') // search by words
				filter['=PHRASE'] = request.QUERY;

			if(typeof request['VALUE'] != 'undefined') // search by id
				filter['=ID'] = request.VALUE;

			if(typeof request['CODE'] != 'undefined') // search by code
				filter['=CODE'] = request.CODE;

			if(typeof this.opts.query.BEHAVIOUR.LANGUAGE_ID != 'undefined')
				filter['=NAME.LANGUAGE_ID'] = this.opts.query.BEHAVIOUR.LANGUAGE_ID;

			if(BX.type.isNotEmptyString(this.opts.query.FILTER.SITE_ID))
				filter['=SITE_ID'] = this.opts.query.FILTER.SITE_ID;

			var result = {
				'select': {
					'VALUE': 'ID',
					'DISPLAY': 'NAME.NAME',
					'1': 'CODE',
					'2': 'TYPE_ID'
				},
				'additionals': {
					'1': 'PATH'
				},
				'filter': filter,
				'version': '2'
			};

			if(typeof request['order'] != 'undefined')
				result['order'] = request.order;

			return result;
		},

		refineResponce: function(responce, request){

			if(typeof responce.ETC.PATH_ITEMS != 'undefined')
			{
				// deprecated begin
				for(var k in responce.ETC.PATH_ITEMS){
					if(BX.type.isNotEmptyString(responce.ETC.PATH_ITEMS[k].DISPLAY))
						this.vars.cache.path[k] = responce.ETC.PATH_ITEMS[k].DISPLAY;
				}
				// deprecated end

				for(var k in responce.ITEMS){

					var item = responce.ITEMS[k];

					if(typeof item.PATH != 'undefined')
					{
						var subPath = BX.clone(item.PATH);
						for(var p in item.PATH)
						{
							var pItemId = item.PATH[p];

							subPath.shift();
							if(typeof this.vars.cache.nodes[pItemId] == 'undefined' && typeof responce.ETC.PATH_ITEMS[pItemId] != 'undefined'){

								var pItem = BX.clone(responce.ETC.PATH_ITEMS[pItemId]);
								pItem.PATH = BX.clone(subPath);
								this.vars.cache.nodes[pItemId] = pItem;
							}
						}
					}
				}
			}

			return responce.ITEMS;
		},

		refineItems: function(items){
			return items;
		},

		refineItemDataForTemplate: function(itemData){
			return itemData;
		},

		// custom value getter (obsolete method)
		getSelectorValue: function(value){

			if(this.opts.provideLinkBy == 'id')
				return value;

			if(typeof this.vars.cache.nodes[value] != 'undefined')
				return this.vars.cache.nodes[value].CODE;
			else
				return '';
		},

		whenLoaderToggle: function(way){
			BX[way ? 'show' : 'hide'](this.ctrls.loader);
		}

	});

	// $(document).find("")
}

if(typeof BX.Sale.component.location.selector.steps == 'undefined' && typeof BX.ui != 'undefined' && typeof BX.ui.widget != 'undefined'){

    BX.Sale.component.location.selector.steps = function(opts, nf){

        this.parentConstruct(BX.Sale.component.location.selector.steps, opts);

        BX.merge(this, {
            opts: {
                bindEvents: {
                    'after-select-item': function(value){

                        if(typeof this.opts.callback == 'string' && this.opts.callback.length > 0 && this.opts.callback in window)
                            window[this.opts.callback].apply(this, [value, this]);
                    }
                },
                disableKeyboardInput: 	false,
                dontShowNextChoice: 	false,
                pseudoValues: 			[], // values that can be only displayed as selected, but not actually selected
                provideLinkBy: 			'id',
                requestParamsInject:	false
            },
            vars: {
                cache: {nodesByCode: {}}
            },
            sys: {
                code: 'slst'
            },
            flags: {
                skipAfterSelectItemEventOnce: false
            }
        });

        this.handleInitStack(nf, BX.Sale.component.location.selector.steps, opts);
        $(document).trigger("set_validators");
        BX.saleOrderAjax.setErrorForCountryField();
    };
    BX.extend(BX.Sale.component.location.selector.steps, BX.ui.chainedSelectors);
    BX.merge(BX.Sale.component.location.selector.steps.prototype, {

        // member of stack of initializers, must be defined even if does nothing
        init: function(){
            this.pushFuncStack('buildUpDOM', BX.Sale.component.location.selector.steps);
            this.pushFuncStack('bindEvents', BX.Sale.component.location.selector.steps);
        },

        // add additional controls
        buildUpDOM: function(){},

        bindEvents: function(){

            var ctx = this,
                so = this.opts;

            if(so.disableKeyboardInput){ //toggleDropDown
                this.bindEvent('after-control-placed', function(adapter){

                    var control = adapter.getControl();

                    BX.unbindAll(control.ctrls.toggle);
                    // spike, bad idea to access fields directly
                    BX.bind(control.ctrls.scope, 'click', function(e){
                        control.toggleDropDown();
                    });
                });
            }

            // quick links
            BX.bindDelegate(this.getControl('quick-locations', true), 'click', {tag: 'a'}, function(){
                ctx.setValueByLocationId(BX.data(this, 'id'));
            });
        },

        ////////// PUBLIC: free to use outside

        setValueByLocationId: function(id){
            BX.Sale.component.location.selector.steps.superclass.setValue.apply(this, [id]);
        },

        setValueByLocationIds: function(locationsData){

            if(!locationsData.PARENT_ID)
                return;

            this.flags.skipAfterSelectItemEventOnce = true;
            this.setValueByLocationId(locationsData.PARENT_ID);

            this.bindEvent('after-control-placed', function(adapter){

                var control = adapter.getControl();

                if(control.vars.value != false)
                    return;

                if(locationsData.IDS)
                    this.opts.requestParamsInject = {'filter': {'=ID': locationsData.IDS}};

                control.tryDisplayPage('toggle');
            });
        },

        setValueByLocationCode: function(code){
            var sv = this.vars;

            // clean
            if(code == null || code == false || typeof code == 'undefined' || code.toString().length == 0){ // deselect
                this.displayRoute([]);
                this.setValueVariable('');
                this.setTargetValue('');
                this.fireEvent('after-clear-selection');
                return;
            }

            // set
            this.fireEvent('before-set-value', [code]);

            var d = new BX.deferred();
            var ctx = this;

            d.done(BX.proxy(function(route){

                this.displayRoute(route);

                var value = sv.cache.nodesByCode[code].VALUE;
                sv.value = value;
                this.setTargetValue(this.checkCanSelectItem(value) ? value : this.getLastValidValue());

            }, this));

            d.fail(function(type){
                if(type == 'notfound'){

                    ctx.displayRoute([]);
                    ctx.setValueVariable('');
                    ctx.setTargetValue('');
                    ctx.showError({errors: [ctx.opts.messages.nothingFound], type: 'server-logic', options: {}});
                }
            });

            this.hideError();

            this.getRouteToNodeByCode(code, d);
        },

        setValue: function(value){
            if(this.opts.provideLinkBy == 'id')
                BX.Sale.component.location.selector.steps.superclass.setValue.apply(this, [value]);
            else
                this.setValueByLocationCode(value);
        },

        setTargetValue: function(value){
            this.setTargetInputValue(this.opts.provideLinkBy == 'code' ? (value ? this.vars.cache.nodes[value].CODE : ''): value);

            if(!this.flags.skipAfterSelectItemEventOnce)
                this.fireEvent('after-select-item', [value]);
            else
                this.flags.skipAfterSelectItemEventOnce = false;
        },

        getValue: function(){

            if(this.opts.provideLinkBy == 'id')
                return this.vars.value === false ? '' : this.vars.value;
            else{
                return this.vars.value ? this.vars.cache.nodes[this.vars.value].CODE : '';
            }
        },

        getNodeByLocationId: function(value){
            return this.vars.cache.nodes[value];
        },

        getSelectedPath: function(){

            var sv = this.vars,
                result = [];

            if(typeof sv.value == 'undefined' || sv.value == false || sv.value == '')
                return result;

            if(typeof sv.cache.nodes[sv.value] != 'undefined'){

                var node = sv.cache.nodes[sv.value];
                while(typeof node != 'undefined')
                {
                    var item = BX.clone(node);
                    var parentId = item.PARENT_VALUE;

                    delete(item.PATH);
                    delete(item.PARENT_VALUE);
                    delete(item.IS_PARENT);

                    if(typeof item.TYPE_ID != 'undefined' && typeof this.opts.types != 'undefined')
                        item.TYPE = this.opts.types[item.TYPE_ID].CODE;

                    result.push(item);

                    if(typeof parentId == 'undefined' || typeof sv.cache.nodes[parentId] == 'undefined')
                        break;
                    else
                        node = sv.cache.nodes[parentId];
                }
            }

            return result;
        },

        ////////// PRIVATE: forbidden to use outside (for compatibility reasons)

        setInitialValue: function(){

            if(this.opts.selectedItem !== false) // there will be always a value as ID, no matter what this.opts.provideLinkBy is equal to
                this.setValueByLocationId(this.opts.selectedItem);
            else if(this.ctrls.inputs.origin.value.length > 0) // there colud be eiter ID or CODE
            {
                if(this.opts.provideLinkBy == 'id')
                    this.setValueByLocationId(this.ctrls.inputs.origin.value);
                else
                    this.setValueByLocationCode(this.ctrls.inputs.origin.value);
            }
        },

        // get route for nodeId and resolve deferred with it
        getRouteToNodeByCode: function(code, d){
            var sv = this.vars,
                ctx = this;

            if(typeof code != 'undefined' && code !== false && code.toString().length > 0){

                var route = [];

                if(typeof sv.cache.nodesByCode[code] != 'undefined')
                    route = this.getRouteToNodeFromCache(sv.cache.nodesByCode[code].VALUE);

                if(route.length == 0){ // || (sv.cache.nodes[nodeId].IS_PARENT && typeof sv.cache.links[nodeId] == 'undefined')){

                    // no way existed or item is parent without children downloaded

                    // download route, then try again
                    ctx.downloadBundle({
                        request: {CODE: code}, // get only route
                        callbacks: {
                            onLoad: function(data){

                                // mark absent as incomplete, kz we do not know if there are really more items of that level or not
                                for(var k in data){
                                    if(typeof sv.cache.links[k] == 'undefined')
                                        sv.cache.incomplete[k] = true;
                                }

                                ctx.fillCache(data, true);

                                route = [];

                                // trying to re-get
                                if(typeof sv.cache.nodesByCode[code] != 'undefined')
                                    route = this.getRouteToNodeFromCache(sv.cache.nodesByCode[code].VALUE);

                                if(route.length == 0)
                                    d.reject('notfound');
                                else
                                    d.resolve(route);
                            },
                            onError: function(){
                                d.reject('internal');
                            }
                        },
                        options: {} // accessible in refineRequest\refineResponce and showError
                    });

                }else
                    d.resolve(route);
            }else
                d.resolve([]);
        },

        addItem2Cache: function(item){
            this.vars.cache.nodes[item.VALUE] = item;
            this.vars.cache.nodesByCode[item.CODE] = item;
        },

        controlChangeActions: function(stackIndex, value){

            var ctx = this,
                so = this.opts,
                sv = this.vars,
                sc = this.ctrls;

            this.hideError();

            ////////////////

            if (Number(value)<=0){
                return;
            }

            if(value.length == 0){

                ctx.truncateStack(stackIndex);
                sv.value = ctx.getLastValidValue();
                ctx.setTargetValue(sv.value);

                this.fireEvent('after-select-real-value');

            }else if(BX.util.in_array(value, so.pseudoValues)){

                ctx.truncateStack(stackIndex);
                ctx.setTargetValue(ctx.getLastValidValue());
                this.fireEvent('after-select-item', [value]);

                this.fireEvent('after-select-pseudo-value');

            }else{

                var node = sv.cache.nodes[value];

                if(typeof node == 'undefined')
                    throw new Error('Selected node not found in the cache');

                // node found

                ctx.truncateStack(stackIndex);

                if(so.dontShowNextChoice){
                    if(node.IS_UNCHOOSABLE)
                        ctx.appendControl(value);
                }else{
                    if(typeof sv.cache.links[value] != 'undefined' || node.IS_PARENT)
                        ctx.appendControl(value);
                }

                if(ctx.checkCanSelectItem(value)){
                    sv.value = value;
                    ctx.setTargetValue(value);
                    this.fireEvent('after-select-real-value');
                }
            }
        },

        // adapter to ajax page request
        refineRequest: function(request){

            var filter = {};
            var select = {
                'VALUE': 'ID',
                'DISPLAY': 'NAME.NAME',
                '1': 'TYPE_ID',
                '2': 'CODE'
            };
            var additionals = {};

            if(typeof request['PARENT_VALUE'] != 'undefined'){ // bundle request
                filter['=PARENT_ID'] = request.PARENT_VALUE;
                select['10'] = 'IS_PARENT';
            }

            if(typeof request['VALUE'] != 'undefined'){ // search by id
                filter['=ID'] = request.VALUE;
                additionals['1'] = 'PATH';
            }

            if(BX.type.isNotEmptyString(request['CODE'])){ // search by code
                filter['=CODE'] = request.CODE;
                additionals['1'] = 'PATH';
            }

            if(BX.type.isNotEmptyString(this.opts.query.BEHAVIOUR.LANGUAGE_ID))
                filter['=NAME.LANGUAGE_ID'] = this.opts.query.BEHAVIOUR.LANGUAGE_ID;

            // we are already inside linked sub-tree, no deeper check for SITE_ID needed
            if(BX.type.isNotEmptyString(this.opts.query.FILTER.SITE_ID)){

                if(typeof this.vars.cache.nodes[request.PARENT_VALUE] == 'undefined' || this.vars.cache.nodes[request.PARENT_VALUE].IS_UNCHOOSABLE)
                    filter['=SITE_ID'] = this.opts.query.FILTER.SITE_ID;
            }

            var result =  {
                'select': select,
                'filter': filter,
                'additionals': additionals,
                'version': '2'
            };

            if(this.opts.requestParamsInject)
            {
                for(var type in this.opts.requestParamsInject)
                {
                    if(this.opts.requestParamsInject.hasOwnProperty(type))
                    {
                        if(result[type] == undefined)
                            result[type] = {};

                        for(var param in this.opts.requestParamsInject[type])
                        {
                            if(this.opts.requestParamsInject[type].hasOwnProperty(param))
                            {
                                if(result[type][param] != undefined)
                                {
                                    var tmp = result[type][param];
                                    result[type][param] = [];
                                    result[type][param].push(tmp);
                                }
                                else
                                {
                                    result[type][param] = [];
                                }

                                for(var val in this.opts.requestParamsInject[type][param])
                                    if(this.opts.requestParamsInject[type][param].hasOwnProperty(val))
                                        result[type][param].push(this.opts.requestParamsInject[type][param][val]);
                            }
                        }
                    }
                }
            }

            return result;
        },

        // adapter to ajax page responce
        refineResponce: function(responce, request){

            if(responce.length == 0)
                return responce;

            if(typeof request.PARENT_VALUE != 'undefined'){ // it was a bundle request

                var r = {};
                r[request.PARENT_VALUE] = responce['ITEMS'];
                responce = r;

            }else if(typeof request.VALUE != 'undefined' || typeof request.CODE != 'undefined'){ // it was a route request

                var levels = {};

                if(typeof responce.ITEMS[0] != 'undefined' && typeof responce.ETC.PATH_ITEMS != 'undefined'){

                    var parentId = 0;

                    for(var k = responce.ITEMS[0]['PATH'].length - 1; k >= 0; k--){

                        var itemId = responce.ITEMS[0]['PATH'][k];
                        var item = responce.ETC.PATH_ITEMS[itemId];

                        item.IS_PARENT = true;

                        levels[parentId] = [item];

                        parentId = item.VALUE;
                    }

                    // add item itself
                    levels[parentId] = [responce.ITEMS[0]];
                }

                responce = levels;
            }

            return responce;
        },

        showError: function(parameters){

            if(parameters.type != 'server-logic')
                parameters.errors = [this.opts.messages.error]; // generic error on js error

            this.ctrls.errorMessage.innerHTML = '<p><font class="errortext">'+BX.util.htmlspecialchars(parameters.errors.join(', '))+'</font></p>';
            BX.show(this.ctrls.errorMessage);

            BX.debug(parameters);
        }
    });
}