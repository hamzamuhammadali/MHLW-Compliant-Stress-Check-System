(function($) {
    'use strict';

    /**
     * Admin-facing JavaScript for MHLW Stress Check System
     */
    $(document).ready(function() {
        
        // Dashboard quick actions
        $('.mhlw-quick-actions .button').on('click', function() {
            $(this).addClass('loading');
        });

        // Import form validation
        $('#mhlw-import-form').on('submit', function(e) {
            var fileInput = $('#csv_file');
            if (!fileInput.val()) {
                e.preventDefault();
                alert(mhlw_admin_ajax.strings.select_file || 'Please select a CSV file.');
                return false;
            }
            
            // Show loading state
            $(this).find('input[type="submit"]').prop('disabled', true).val(mhlw_admin_ajax.strings.importing || 'Importing...');
        });

        // Group analysis filters
        $('#mhlw-filter-type').on('change', function() {
            var filterType = $(this).val();
            var $valueContainer = $('#mhlw-filter-value-container');
            
            if (filterType === 'company') {
                $valueContainer.hide();
            } else {
                $valueContainer.show();
                // Load filter values via AJAX
                loadFilterValues(filterType);
            }
        });

        // Load filter values
        function loadFilterValues(type) {
            $.ajax({
                url: mhlw_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mhlw_get_filter_values',
                    nonce: mhlw_admin_ajax.nonce,
                    filter_type: type
                },
                success: function(response) {
                    if (response.success) {
                        var $select = $('#mhlw-filter-value');
                        $select.empty().append('<option value="">-- Select --</option>');
                        
                        $.each(response.data.values, function(key, value) {
                            $select.append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                }
            });
        }

        // Individual results search
        var searchTimeout;
        $('#mhlw-employee-search').on('input', function() {
            clearTimeout(searchTimeout);
            var searchTerm = $(this).val();
            
            searchTimeout = setTimeout(function() {
                filterResults(searchTerm);
            }, 300);
        });

        function filterResults(term) {
            var $rows = $('.mhlw-results-table tbody tr');
            
            if (!term) {
                $rows.show();
                return;
            }
            
            term = term.toLowerCase();
            
            $rows.each(function() {
                var $row = $(this);
                var text = $row.text().toLowerCase();
                
                if (text.indexOf(term) > -1) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        }

        // Export CSV functionality
        $('#mhlw-export-csv').on('click', function(e) {
            e.preventDefault();
            var filterType = $('#mhlw-filter-type').val();
            var filterValue = $('#mhlw-filter-value').val();
            
            // Trigger CSV download
            window.location.href = mhlw_admin_ajax.ajax_url + '?action=mhlw_export_csv&nonce=' + mhlw_admin_ajax.nonce + '&filter_type=' + filterType + '&filter_value=' + filterValue;
        });

    });

})(jQuery);
