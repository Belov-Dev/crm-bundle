$(function () {

    let datasheetFilterOptions = $('#datasheet-filter-options');

    $('[data-datasheet-filter]').off('focus').on('focus', function () {
        datasheetFilterShowOptions($(this));
    }).on('keydown', function (event) {
        onDatasheetFilterKeyup($(this), event);
    }).on('keyup', function (event) {
        if (event.keyCode >= 37 && event.keyCode <= 40) {
            return;
        }
        datasheetFilterSearchOptions($(this));
    });

    // Hide options if clicked outside
    $('body').click(function (event) {
        if ($(event.target).parents('[data-datasheet-filter-container]').length < 1) {
            datasheetFilterHideOptions();
        }
    })

    /**
     * Handle keyboard actions
     */
    function onDatasheetFilterKeyup(filterInput, event) {
        let keyCode = event.keyCode;
        let isHidden = datasheetFilterOptions.is(':hidden')

        // This should goes first, before options show
        if (keyCode == 13) {

            // If options is hidden then - submitting form
            if (isHidden) {
                return;
            }

            // If options is hidden then - submitting form
            event.preventDefault();
            datasheetFilterApplySelectedOption(filterInput);

            // Prevent form submit on enter
            return false;
        }

        // Show options in any case
        if (isHidden) {
            datasheetFilterShowOptions(filterInput);
        } else if (keyCode == 27) {
            datasheetFilterHideOptions();
        }

        // Arrow up/down
        if (keyCode == 38 || keyCode == 40) {
            event.preventDefault();
            datasheetFilterSelectOption(filterInput, keyCode == 40);
        }
    }

    function datasheetFilterSearchOptions(filterInput) {
        let searchString = filterInput.val().toLowerCase();
        let datasheetId = filterInput.parents('table').data('datasheet-id');
        let fieldName = filterInput.data('datasheet-filter');
        let varName = 'datasheet_' + datasheetId + '_' + fieldName + '_choices';
        let options = eval(varName);
        let filteredOptions = options.filter(str => str.toLowerCase().includes(searchString));
        datasheetFilterOptions.html('');

        $.each(filteredOptions, function (kee, value) {
            datasheetFilterOptions.append('<div>' + value + '</div>');
        });

        if (filteredOptions.length == 1) {
            datasheetFilterOptions.children().addClass('active');
        } else {
            datasheetFilterOptions.find('.active').removeClass('active');
        }
    }

    function datasheetFilterApplySelectedOption(filterInput) {
        let active = datasheetFilterOptions.find('.active');

        if (active.length > 0) {
            active.removeClass('active');
            filterInput.val(active.text());
        }
        datasheetFilterHideOptions(filterInput);
    }

    function datasheetFilterSelectOption(filterInput, next = true) {
        if (datasheetFilterOptions.find('.active').length < 1) {
            if (next) {
                return datasheetFilterOptions.children().first().addClass('active');
            } else {
                return datasheetFilterOptions.children().last().addClass('active');
            }
        }

        let active = datasheetFilterOptions.find('.active');

        if (next) {
            if (active.next().length) {
                active.removeClass('active').next().addClass('active');
            }
        } else {
            if (active.prev().length) {
                active.removeClass('active').prev().addClass('active');
            }
        }
    }

    function datasheetFilterShowOptions(filterInput) {
        datasheetFilterOptions.html('').show().appendTo(filterInput.parents('[data-datasheet-filter-container]'));
        datasheetFilterSearchOptions(filterInput);
    }

    function datasheetFilterHideOptions() {
        datasheetFilterOptions.html('').hide();
    }

    datasheetFilterOptions.on('click', function (event) {
        let value = $(event.target).text();
        $(this).parent().find('[data-datasheet-filter]').val(value);
        datasheetFilterHideOptions();
    })

    $('[data-datasheet-filter-clear]').click(function () {
        $(this).hide();
        let fieldName = $(this).data('datasheet-filter-clear');
        console.log(fieldName);
        $('[data-datasheet-filter="' + fieldName + '"]').val('').parents('form').submit();
    })

    $('[data-datasheet-field-title] *').on('click', function(){
        let fieldName = $(this).parent().data('datasheet-field-title');
        let datasheet = $(this).parents('[data-datasheet-id]');
        let sortingEnable = datasheet.data('datasheet-sorting-enabled');

        if(!sortingEnable){
            return;
        }
        let sortingBy = datasheet.find('[data-datasheet-sort-by]');
        let sortingType = datasheet.find('[data-datasheet-sort-type]');

        if(sortingBy.val() == fieldName){
            sortingType.val(sortingType.val() == 'ASC' ? 'DESC' : 'ASC');
        }else{
            sortingBy.val(fieldName);
            sortingType.val('ASC');
        }
        datasheet.find('form').submit();
    })
})