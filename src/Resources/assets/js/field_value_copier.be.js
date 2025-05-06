class FieldValueCopierBundle {
    static init() {
        document.querySelectorAll('.field-value-copier .load').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                if (item.classList.contains('disabled')) {
                    return;
                }

                let siblingSelect = item.closest('.field-value-copier').querySelector('select');

                if (siblingSelect.value != '' && confirm(item.getAttribute('data-confirm'))) {
                    let url = FieldValueCopierBundle.addParameterToUri(item.getAttribute('href'), 'fieldName', item.closest('.field-value-copier').getAttribute('data-field'));
                    window.location.href = FieldValueCopierBundle.addParameterToUri(url, 'fieldValue', siblingSelect.value);
                }
            });
        });
    }

    static addParameterToUri(uri, key, value)
    {
        if (!uri)
        {
            uri = window.location.href;
        }

        let re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
            hash;

        if (re.test(uri))
        {
            if (typeof value !== 'undefined' && value !== null)
            {
                return uri.replace(re, '$1' + key + "=" + value + '$2$3');
            }
            else
            {
                hash = uri.split('#');
                uri = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');

                if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                {
                    uri += '#' + hash[1];
                }

                return uri;
            }
        }
        else
        {
            if (typeof value !== 'undefined' && value !== null)
            {
                let separator = uri.indexOf('?') !== -1 ? '&' : '?';
                hash = uri.split('#');
                uri = hash[0] + separator + key + '=' + value;

                if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                {
                    uri += '#' + hash[1];
                }

                return uri;
            }
            else
            {
                return uri;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', FieldValueCopierBundle.init);
