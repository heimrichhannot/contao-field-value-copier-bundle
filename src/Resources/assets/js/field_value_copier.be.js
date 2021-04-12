import utilsBundle from '@hundh/contao-utils-bundle';

class FieldValueCopierBundle {
    static init() {
        document.querySelectorAll('.field-value-copier .load').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                if (item.classList.contains('disabled')) {
                    return;
                }

                let siblingSelect = Array.prototype.filter.call(item.parentNode.parentNode.children, function(child){
                    return child !== item && child.tagName.toLowerCase() === 'select';
                }), url;

                if (utilsBundle.util.isTruthy(siblingSelect[0].value) && siblingSelect[0].value != '' && confirm(item.getAttribute('data-confirm'))) {
                    url = utilsBundle.url.addParameterToUri(item.getAttribute('href'), 'fieldName', item.closest('.field-value-copier').getAttribute('data-field'));
                    window.location.href = utilsBundle.url.addParameterToUri(url, 'fieldValue', siblingSelect[0].value);
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', FieldValueCopierBundle.init);
