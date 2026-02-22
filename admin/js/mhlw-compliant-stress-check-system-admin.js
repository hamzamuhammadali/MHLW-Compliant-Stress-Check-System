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

        /**
         * Stress Check Form Handler (for employee assessment pages)
         */
        var form = $('#mhlw-stress-check-form');
        if (form.length) {
            var saveDraftBtn = $('#mhlw-save-draft');
            var submitBtn = $('#mhlw-submit-form');
            var progressBar = $('.mhlw-progress-fill');
            var progressText = $('.mhlw-progress-text');
            var draftStatus = $('.mhlw-draft-status');
            var messagesContainer = $('#mhlw-form-messages');

            // Auto-save draft every 30 seconds
            var autoSaveInterval = setInterval(function() {
                saveDraft(true);
            }, 30000);

            // Update progress bar
            function updateProgress() {
                var totalQuestions = 57;
                var answeredQuestions = form.find('input[type="radio"]:checked').length;
                var percentage = (answeredQuestions / totalQuestions) * 100;
                
                progressBar.css('width', percentage + '%');
                progressText.text(answeredQuestions + ' / ' + totalQuestions);
                
                // Mark answered questions
                form.find('.mhlw-question-item').each(function() {
                    var questionItem = $(this);
                    var isAnswered = questionItem.find('input[type="radio"]:checked').length > 0;
                    
                    if (isAnswered) {
                        questionItem.addClass('answered');
                    } else {
                        questionItem.removeClass('answered');
                    }
                });
            }

            // Listen for radio button changes
            form.on('change', 'input[type="radio"]', function() {
                updateProgress();
            });

            // Initial progress update
            updateProgress();

            // Save draft button click
            saveDraftBtn.on('click', function(e) {
                e.preventDefault();
                saveDraft(false);
            });

            // Save draft function
            function saveDraft(isAutoSave) {
                var responses = {};
                
                form.find('input[type="radio"]:checked').each(function() {
                    var name = $(this).attr('name');
                    var questionNumber = name.replace('question_', '');
                    var value = $(this).val();
                    responses[questionNumber] = value;
                });

                if (Object.keys(responses).length === 0) {
                    if (!isAutoSave) {
                        showMessage('No responses to save.', 'error');
                    }
                    return;
                }

                $.ajax({
                    url: mhlw_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mhlw_save_draft',
                        nonce: $('#mhlw-stress-check-form input[name="mhlw_form_nonce"]').val(),
                        responses: responses
                    },
                    success: function(response) {
                        if (response && typeof response === 'object') {
                            if (response.success) {
                                console.log("Save draft response: ", response);
                                draftStatus.fadeIn().delay(2000).fadeOut();
                                if (!isAutoSave) {
                                    showMessage(response.data && response.data.message ? response.data.message : 'Draft saved successfully', 'success');
                                }
                            } else {
                                if (!isAutoSave) {
                                    showMessage(response.data && response.data.message ? response.data.message : 'Failed to save draft', 'error');
                                }
                            }
                        } else {
                            console.error('Invalid response format:', response);
                            if (!isAutoSave) {
                                showMessage('Invalid server response', 'error');
                            }
                        }
                    },
                    error: function() {
                        if (!isAutoSave) {
                            showMessage('An error occurred while saving the draft.', 'error');
                        }
                    }
                });
            }

            // Form submission
            form.on('submit', function(e) {
                e.preventDefault();
                
                var responses = {};
                
                form.find('input[type="radio"]:checked').each(function() {
                    var name = $(this).attr('name');
                    var questionNumber = name.replace('question_', '');
                    var value = $(this).val();
                    responses[questionNumber] = value;
                });

                // Validate all questions are answered
                if (Object.keys(responses).length !== 57) {
                    showMessage('Please answer all 57 questions before submitting.', 'error');
                    
                    // Scroll to first unanswered question
                    var firstUnanswered = form.find('.mhlw-question-item:not(.answered)').first();
                    if (firstUnanswered.length) {
                        $('html, body').animate({
                            scrollTop: firstUnanswered.offset().top - 100
                        }, 500);
                        firstUnanswered.css('border-color', '#f44336');
                        setTimeout(function() {
                            firstUnanswered.css('border-color', '');
                        }, 3000);
                    }
                    return;
                }

                console.log("Form submission starting...");
                console.log("mhlw_admin_ajax object:", mhlw_admin_ajax);
                console.log("Responses collected:", responses);
                console.log("Form nonce value:", $('#mhlw-stress-check-form input[name="mhlw_form_nonce"]').val());

                // Disable submit button to prevent double submission
                submitBtn.prop('disabled', true).text('Submitting...');

                $.ajax({
                    url: mhlw_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mhlw_submit_responses',
                        nonce: mhlw_admin_ajax.nonce,
                        responses: responses
                    },
                    beforeSend: function(xhr) {
                        console.log("AJAX beforeSend - URL:", mhlw_admin_ajax.ajax_url);
                        console.log("AJAX beforeSend - Data:", {
                            action: 'mhlw_submit_responses',
                            nonce: mhlw_admin_ajax.nonce,
                            responses: responses
                        });
                    },
                    success: function(response) {
                        console.log("AJAX success - Response:", response);
                        if (response && typeof response === 'object') {
                            if (response.success) {
                                showMessage(response.data && response.data.message ? response.data.message : 'Thank you for completing the stress check', 'success');
                                
                                // Clear auto-save interval
                                clearInterval(autoSaveInterval);
                                
                                // Redirect after short delay
                                if (response.data && response.data.redirect) {
                                    setTimeout(function() {
                                        window.location.href = response.data.redirect;
                                    }, 1500);
                                }
                            } else {
                                submitBtn.prop('disabled', false).text('Submit Assessment');
                                showMessage(response.data && response.data.message ? response.data.message : 'Failed to save responses', 'error');
                            }
                        } else {
                            console.error('Invalid response format:', response);
                            submitBtn.prop('disabled', false).text('Submit Assessment');
                            showMessage('Invalid server response', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error - Status:", status);
                        console.error("AJAX error - Error:", error);
                        console.error("AJAX error - Response text:", xhr.responseText);
                        console.error("AJAX error - Status code:", xhr.status);
                        submitBtn.prop('disabled', false).text('Submit Assessment');
                        showMessage('An error occurred while submitting the form. Please try again.', 'error');
                    }
                });
            });

            // Show message function
            function showMessage(message, type) {
                var messageClass = type === 'success' ? 'mhlw-message-success' : 'mhlw-message-error';
                var messageHtml = '<div class="mhlw-message ' + messageClass + '">' + message + '</div>';
                
                messagesContainer.html(messageHtml);
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    messagesContainer.find('.mhlw-message').fadeOut();
                }, 5000);
            }

            // Warn user about unsaved changes when leaving page
            var formChanged = false;
            form.on('change', 'input', function() {
                formChanged = true;
            });

            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });

            // Remove beforeunload handler on form submit
            form.on('submit', function() {
                $(window).off('beforeunload');
            });
        }

    });

})(jQuery);
