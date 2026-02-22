(function($) {
    'use strict';

    /**
     * Stress Check Form Handler
     */
    $(document).ready(function() {
        var form = $('#mhlw-stress-check-form');
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
                    showMessage('<?php _e("No responses to save.", "mhlw-compliant-stress-check-system"); ?>', 'error');
                }
                return;
            }

            $.ajax({
                url: mhlw_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mhlw_save_draft',
                    nonce: mhlw_ajax.nonce,
                    responses: responses
                },
                success: function(response) {
                    if (response.success) {
                        draftStatus.fadeIn().delay(2000).fadeOut();
                        if (!isAutoSave) {
                            showMessage(response.data.message, 'success');
                        }
                    } else {
                        if (!isAutoSave) {
                            showMessage(response.data.message, 'error');
                        }
                    }
                },
                error: function() {
                    if (!isAutoSave) {
                        showMessage('<?php _e("An error occurred while saving the draft.", "mhlw-compliant-stress-check-system"); ?>', 'error');
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
                showMessage('<?php _e("Please answer all 57 questions before submitting.", "mhlw-compliant-stress-check-system"); ?>', 'error');
                
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

            // Disable submit button to prevent double submission
            submitBtn.prop('disabled', true).text('<?php _e("Submitting...", "mhlw-compliant-stress-check-system"); ?>');

            $.ajax({
                url: mhlw_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mhlw_submit_responses',
                    nonce: mhlw_ajax.nonce,
                    responses: responses
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                        
                        // Clear auto-save interval
                        clearInterval(autoSaveInterval);
                        
                        // Redirect after short delay
                        if (response.data.redirect) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect;
                            }, 1500);
                        }
                    } else {
                        submitBtn.prop('disabled', false).text('<?php _e("Submit Assessment", "mhlw-compliant-stress-check-system"); ?>');
                        showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    submitBtn.prop('disabled', false).text('<?php _e("Submit Assessment", "mhlw-compliant-stress-check-system"); ?>');
                    showMessage('<?php _e("An error occurred while submitting the form. Please try again.", "mhlw-compliant-stress-check-system"); ?>', 'error');
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
                return '<?php _e("You have unsaved changes. Are you sure you want to leave?", "mhlw-compliant-stress-check-system"); ?>';
            }
        });

        // Remove beforeunload handler on form submit
        form.on('submit', function() {
            $(window).off('beforeunload');
        });
    });

})(jQuery);
