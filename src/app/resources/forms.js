function validate_length(value, param) {
	return value && (value.length >= parseInt(param));
}

function validate_maxlen(value, param) {
	return (!value) || (value.length <= parseInt(param));
}

function validate_match(value, param) {
	return (value == param);
}

function validate_password(value, param) {
	return validate_length(value, 5);
}

function validate_ip(value) {
	return validate_length(value, 5);
}

function validate_date(value, param) {
	return validate_length(value, 5);
}

function validate_html(value, param) {
	return true;
}

// simple email validation
function validate_email(value) {
	var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(value);
}

// integer - param => allow empty
function validate_integer(n, param) {
	if (param == 'false' || param == '0') {
		param = false;
	}
	return (param && !n) || (+n===parseInt(n));
}

function validate_min(n, param) {
	return (parseFloat(n) >= parseFloat(param));
}

function validate_max(n, param) {
	return (parseFloat(n) <= parseFloat(param));
}

function validate_decimal(n, param) {
	if (param == 'false' || param == '0') {
		param = false;
	}
	return (param && !n) || (!isNaN(parseFloat(n)) && isFinite(n));
}

function validate_price(value) {
	return validate_decimal(value);
}

function validate_name(value) {
	var is_valid = true;
	if (value && (value.length >= 1)) {
		var arr = value.split(' ');
		is_valid = arr.length > 1 && arr[0].length > 0 && arr[1].length > 0;
	} else {
		is_valid = false;
	}
	return is_valid;
}

function validate_zip(zip) {
	var nZip;
	if (!validate_integer(zip, false)) {
		zZip = parseInt(zip.replace(' ',''));
	} else {
		nZip = zip;
	}

	return (parseInt(nZip) > 9999 && parseInt(nZip) < 100000);
}

function formValidation(form_id) {
	this.frm = document.getElementById(form_id);
	this.is_valid = true;
	this.fields = [];

	this.submit = function(noret) {
		this.is_valid = true;
		for (var i = 0, max = this.fields.length; i < max; i++) {
			this.is_valid = this.validateField(this.fields[i]) && this.is_valid;
		}		
		if (this.is_valid) {
			if (noret == true) {
				const input = z.getById('suppress_return');
				if (input) {
					input.value = 'true';
				}
			}
			this.frm.submit();
		}
		return this.is_valid;
	}

	this.add = function(field_name, validation, param) {
		var field = new formField(field_name, validation, param);
		this.fields.push(field);
		return field;
	}

	this.val = function (field_id) {
		return z.val(field_id);
	}

	this.showFieldValidation = function(field_name, validation, is_valid) {
		if (is_valid) {
			z.hide(field_name + '_validation_' + validation);
			z.removeClass(field_name + '_form_group', 'has-error');
			z.removeClass(field_name, 'is-invalid');
		} else {
			z.show(field_name + '_validation_' + validation);
			z.addClass(field_name + '_form_group', 'has-error');
			z.addClass(field_name, 'is-invalid');
		}
	}

	this.validateField = function(field) {
		var is_valid = this.isFieldValid(field);
		this.showFieldValidation(field.name, field.validation, is_valid);
		return is_valid;
	}

	this.isFieldValid = function(field) {
		var is_valid = true;
		var value = this.val(field.name);
		switch (field.validation) {
			case 'confirm':
				var value2 = this.val(field.param);
				is_valid = validate_match(value, value2);
			break;
			default:
				is_valid = window['validate_' + field.validation](value, field.param);
		}
		return is_valid;
	}

}

function formField(field_name, validation, param) {
	this.name = field_name;
	this.validation = validation || 'length';
	this.param = param;
}

function deleteItemConfirm(delete_question, delete_url) {
	if (confirm(delete_question)) {
		document.location = delete_url;
	}
}
