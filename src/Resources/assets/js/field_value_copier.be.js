class FieldValueCopierBundle {
    static init() {
        document.querySelectorAll('.field-value-copier .load').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                if (item.classList.contains('disabled')) {
                    return;
                }

                if (confirm(item.getAttribute('data-confirm'))) {
                    let siblingSelect = Array.prototype.filter.call(el.parentNode.children, function(child){
                        return child !== el;
                    });

                    window.location.href = item.getAttribute('href') + '&fieldValue=' + siblingSelect.value;
                }
            });
        });

        // the mootools part
        var $select = $$('.field-value-copier select');

        if ($select.length <= 0)
            return;

        $select = $select[0];

        function checkSelect($select) {
            if ($select.selectedIndex <= 0) {
                $select.getAllNext('a.tl_submit').addClass('disabled');
            } else {
                $select.getAllNext('a.tl_submit').removeClass('disabled');
            }
        }

        if ($select != null) {
            checkSelect($select);

            $select.addEvent('change', function(event) {
                checkSelect(this);
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', FieldValueCopierBundle.init);