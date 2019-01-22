class FieldValueCopierBundle {
    static init() {
        document.querySelectorAll('.field-value-copier .load').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                if (item.classList.contains('disabled')) {
                    return;
                }

                if (confirm(item.getAttribute('data-confirm'))) {
                    let siblingSelect = Array.prototype.filter.call(item.parentNode.children, function(child){
                        return child !== item && child.tagName.toLowerCase() === 'select';
                    });

                    window.location.href = item.getAttribute('href') + '&fieldValue=' + siblingSelect[0].value;
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', FieldValueCopierBundle.init);