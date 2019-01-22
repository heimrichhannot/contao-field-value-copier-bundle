import utilsBundle from 'contao-utils-bundle';

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
                });

                if (utilsBundle.util.isTruthy(siblingSelect[0].value) && siblingSelect[0].value != '' && confirm(item.getAttribute('data-confirm'))) {
                    window.location.href = utilsBundle.url.addParameterToUri(item.getAttribute('href'), 'fieldValue', siblingSelect[0].value);
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', FieldValueCopierBundle.init);