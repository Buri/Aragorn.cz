/*
---
description: CwComplete

authors:
  - Mario Fischer (http://www.chipwreck.de/blog/)

license:
  - MIT-style license

requires:
  core/1.3: '*'
  more/1.3: 'Element.Shortcuts'

provides:
  - CwComplete

...
*/
var CwAutocompleter = new Class({

	Implements: [Options,Events],

	options: {
		ajaxMethod: 'get', // use get or post for the request?
		ajaxParam: 'search', // name of parameter for the request (..url.php?search=..)
		inputMinLength: 3, // number of characters at which the auto completion starts
		pause: 0, // number of ms before autocomplete starts (set to 0 for immediately)

		targetfieldForKey: '', // if set, the user selected key will be written to this field as value (usually a hidden field)
		targetfieldForValue: '', // if set, the user selected item will be written to this field as value (usually a text field)

		suggestionBoxOuterClass: 'cwCompleteOuter', // rename css classes here if necessary
		suggestionBoxListClass: 'cwCompleteChoices', // rename css classes here if necessary
		suggestionBoxLoadingClass: 'cwCompleteLoading', // rename css classes here if necessary
		suggestionBoxHoverClass: 'cwCompleteChoicesHover', // rename css classes here if necessary

		clearChoicesOnBlur: true, // whether to clear choices when the container loses focus
		clearChoicesOnEsc: true, // whether to clear choices when the container loses focus
		clearChoicesOnChoose: true, // whether to clear choices when a value is chosen
		setValuesOnChoose: true, // whether to set values when a choice is selected

		suggestionContainer: {}, // an existing element to contain the suggestions
		choiceContainer: 'ul', // the element used to encapsulate all choices
		choiceElement: 'li', // the element used to encapsulate the actual choice text

/*      doRetrieveValues: function(input) { return [['1','example'], ['2','something else']]; }, // optional method to provide the values, the url is ignored then */
		onChoose: function() {}, // function to execute if the user chose an item
                                filter:function(t){return t;} // function to match searchterm, return term

	},

	// initialization : css class of the input field, url for ajax query, options
	initialize: function(inputfield, url, options)
	{
		// prepare options
		this.setOptions(options);
		if (!$(inputfield)) {return;}

		this.prevlength = 0;
		this.textfield = $(inputfield);
		this.url = url;
		this.clickeddoc = false;

		// build elements
		var mywidth = this.textfield.getStyle('width');
		var myleft = this.textfield.getPosition().x;
		var mytop = this.textfield.getPosition().y + this.textfield.getSize().y;

		if (this.options.suggestionContainer && $(this.options.suggestionContainer)) {
			this.container = $(this.options.suggestionContainer);
			this.container.addClass(this.options.suggestionBoxOuterClass);
		} else {
		this.container = new Element('div', {
			'class': this.options.suggestionBoxOuterClass,
			'styles': {'width': mywidth, 'left': myleft, 'top': mytop, 'height': 0}
		}).inject($(document.body));
		}

		this.choices = new Element(this.options.choiceContainer, {
			'class': this.options.suggestionBoxListClass
		}).inject($(this.container), 'inside');
		this.clearChoices();

		// attach events
		this.textfield.setProperty('autocomplete', 'off');
		this.textfield.addEvents( {'keydown': this.keypressed.bind(this), 'keyup': this.keypressed.bind(this)} );
		if (this.options.clearChoicesOnBlur) {
			if (!Browser.ie) {
				this.textfield.addEvents( {'blur': this.clearChoices.bind(this)} );
			}
			else {
				document.addEvent('click', this.docclick.bind(this));
				this.textfield.addEvents( {'blur': this.blurLater.bind(this)} );
			}
		}

		if (!Browser.ie) {
			this.choices.addEvents( {'mousedown': function(e){e.preventDefault();}} );
		}

		// prepare ajax
		if (this.url) {
			this.ajax = new Request({
				url: this.url,
				method: this.options.ajaxMethod});
			this.ajax.addEvent('onComplete', this.ajaxComplete.bind(this));
		}
	},

	// Retrieve values given the textfield input and show "loading..."
	getValues: function(input)
	{
		var t_input = input;

		if (this.options.doRetrieveValues != null) {
			this.setValues(this.options.doRetrieveValues.apply(input));
		}
		else if (this.ajax) {

			if (this.options.pause === 0) {
				this.choices.hide();
				this.container.addClass(this.options.suggestionBoxLoadingClass);
				this.container.show();
				this.ajax.send(this.options.ajaxParam+"="+t_input);
			}
			else {
				var myself = this;
				// dont spam the lookup script wait to see if typing has stopped
				window.setTimeout(
				   function() {
						if ( t_input == myself.textfield.get('value') ) {
							myself.choices.hide();
							myself.container.addClass(myself.options.suggestionBoxLoadingClass);
							myself.container.show();
						   	myself.ajax.send(myself.options.ajaxParam+"="+t_input);
					   } else {
						   myself.ajax.cancel();
					   }
				   }, myself.options.pause);
			}
		}
	},

	// Ajax oncomplete, eval response and fill dropdown, remove "loading"-classes
	ajaxComplete: function(input)
	{
		if (!input) {return;}
		var myvalue = JSON.decode(input, true);

		if (myvalue === false || !myvalue.length) {
			this.clearChoices();
		}
		else {
			this.setValues(myvalue);
		}
	},

	setValues: function(values)
	{
		this.values = values;
		this.clearChoices();
		this.values.each( function(avalue, i) {
			if (avalue) {
				this.lielems[i] = new Element(this.options.choiceElement, {'html': avalue[1]});
				this.lielems[i].addEvent('click',this.enterValue.bind(this, {id: avalue[0], value: avalue[1]})	);
				if (Browser.ie) {
					this.lielems[i].addEvent('mousedown',this.enterValue.bind(this, {id: avalue[0], value: avalue[1]}));
				}
				this.lielems[i].inject(this.choices, 'inside');
			}
		}.bind(this));

		this.container.show();
		this.container.removeClass(this.options.suggestionBoxLoadingClass);
		this.choices.show();
		this.lielems[this.selected].addClass(this.options.suggestionBoxHoverClass);
	},

	// Clear list of choices
	clearChoices: function(obj)
	{
		this.lielems = [];
		this.selected = 0;
		this.choices.set('html', '');
		this.container.hide();
	},

	// Enter value from selection into text-field and fire onChoose-event
	enterValue: function(selected)
	{
		if (this.options.setValuesOnChoose) {
			if (this.options.targetfieldForKey && $(this.options.targetfieldForKey)) {
				$(this.options.targetfieldForKey).value = selected['id'];
			}
			if (this.options.targetfieldForValue && $(this.options.targetfieldForValue)) {
				$(this.options.targetfieldForValue).value = selected['value'];
			}
			else {
				this.textfield.value = selected['value'];
			}
		}

		this.fireEvent('onChoose', {'key': selected['id'], 'value': selected['value']});

		if (this.options.clearChoicesOnChoose) {
			this.clearChoices();
		}
	},

	moveUp: function(el, event)
	{
		if (this.lielems[this.selected] && this.lielems[this.selected - 1]) {
			this.lielems[this.selected].removeClass(this.options.suggestionBoxHoverClass);
			this.selected -= 1;
			this.lielems[this.selected].addClass(this.options.suggestionBoxHoverClass);
		}
	},

	moveDown: function(el, event)
	{
		if (this.lielems[this.selected] && this.lielems[this.selected + 1]) {
			this.lielems[this.selected].removeClass(this.options.suggestionBoxHoverClass);
			this.selected += 1;
			this.lielems[this.selected].addClass(this.options.suggestionBoxHoverClass);
		}
	},

	// Text field key handler
	keypressed: function(event)
	{
		var myevent = new Event(event);
		if (myevent.target.id === this.textfield.id) {
			if (myevent.type == 'keyup') {
				switch (myevent.key) {
					case 'enter':
						if (this.lielems[this.selected]) {
							this.lielems[this.selected].fireEvent('click');
						}
						event.preventDefault();
						break;
					case 'down':
						this.moveDown();
						event.preventDefault();
						break;
					case 'up':
						this.moveUp();
						event.preventDefault();
						break;
					case 'esc':
						if (this.options.clearChoicesOnEsc) {
							this.clearChoices();
						}
						break;
					default:
						var text = myevent.target.value;
						if (text.length != this.prevlength) { // text length has changed
							if (text.length >= this.options.inputMinLength) { // ..and is long enough
								this.prevlength = text.length;
                                                                                                                                var sr = this.options.filter(text, myevent);
                                                                                                                                if(sr)
                                                                                                                                    this.getValues(sr);
							} else {
								this.clearChoices();
							}
							event.preventDefault();
						}
				}
			} else if (myevent.key == 'enter' || myevent.key == 'esc') { // keydown disabled for those
				//event.preventDefault();
			}else if((myevent.key == 'down' || myevent.key == 'up') && $$('.cwCompleteOuter')[0].getStyle('display') == 'block'){
                                                                event.preventDefault();
                                                }else {
				this.prevlength = myevent.target.value.length; // any other keydown
			}
		}
	},

	// IE6/7 workaround...
	docclick: function(event)
	{
		if (this.textfield.id !== event.target.id) {
	        this.clickeddoc = true;
		}
    },

	// IE6/7 workaround...
    blurLater: function(event)
    {
		var that = this;
		var callback = function()
		{
			if (that.clickeddoc) {
				that.clickeddoc = false;
				that.clearChoices(event);
			}
			else {
          		that.textfield.focus();
        	}
		};
		setTimeout(callback, 200);
    }
});