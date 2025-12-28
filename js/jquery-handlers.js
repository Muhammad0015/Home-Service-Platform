/**
 * ================================================
 * JQUERY HANDLERS
 * Home Service Booking Platform
 * ================================================
 */

$(document).ready(function() {
    
    // ==================== SMOOTH SCROLLING ====================
    
    $('a[href^="#"]').on('click', function(event) {
        const target = $(this.getAttribute('href'));
        
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });
    
    // ==================== HEADER SCROLL EFFECT ====================
    
    $(window).on('scroll', function() {
        if ($(this).scrollTop() > 50) {
            $('.header').css('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.15)');
        } else {
            $('.header').css('box-shadow', '0 4px 6px -1px rgba(0, 0, 0, 0.1)');
        }
    });
    
    // ==================== FORM INPUT EFFECTS ====================
    
    $('.form-group input, .form-group textarea, .form-group select').on('focus', function() {
        $(this).parent().addClass('focused');
    });
    
    $('.form-group input, .form-group textarea, .form-group select').on('blur', function() {
        if ($(this).val() === '') {
            $(this).parent().removeClass('focused');
        }
    });
    
    // Check for pre-filled values
    $('.form-group input, .form-group textarea, .form-group select').each(function() {
        if ($(this).val() !== '') {
            $(this).parent().addClass('focused');
        }
    });
    
    // ==================== LOADING HELPERS ====================
    
    window.showLoading = function(selector) {
        $(selector).html('<div class="spinner"></div>');
    };
    
    window.hideLoading = function(selector) {
        $(selector).find('.spinner').remove();
    };
    
    // ==================== TOAST NOTIFICATIONS ====================
    
    window.showToast = function(message, type = 'success', duration = 3000) {
        $('.toast-notification').remove();
        
        const toast = $(`
            <div class="toast-notification toast-${type}">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-times-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `);
        
        if ($('#toast-styles').length === 0) {
            $('head').append(`
                <style id="toast-styles">
                    .toast-notification {
                        position: fixed;
                        top: 100px;
                        right: 20px;
                        padding: 15px 25px;
                        border-radius: 8px;
                        color: white;
                        font-weight: 500;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        z-index: 9999;
                        opacity: 0;
                        transform: translateX(100px);
                        transition: all 0.3s ease;
                    }
                    .toast-notification.show {
                        opacity: 1;
                        transform: translateX(0);
                    }
                    .toast-success { background-color: #10b981; }
                    .toast-error { background-color: #ef4444; }
                    .toast-warning { background-color: #f59e0b; }
                </style>
            `);
        }
        
        $('body').append(toast);
        
        setTimeout(() => toast.addClass('show'), 10);
        
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };
    
    // ==================== MODAL HANDLERS ====================
    
    window.openModal = function(modalId) {
        $('#' + modalId).addClass('active');
        $('body').css('overflow', 'hidden');
    };
    
    window.closeModal = function(modalId) {
        $('#' + modalId).removeClass('active');
        $('body').css('overflow', 'auto');
    };
    
    // Close modal on overlay click
    $(document).on('click', '.modal-overlay', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
            $('body').css('overflow', 'auto');
        }
    });
    
    // Close modal on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.modal-overlay.active').removeClass('active');
            $('body').css('overflow', 'auto');
        }
    });
    
    // ==================== AJAX HELPERS ====================
    
    window.loadContent = function(url, container, callback) {
        showLoading(container);
        
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                hideLoading(container);
                if (typeof callback === 'function') {
                    callback(data);
                }
            },
            error: function(xhr, status, error) {
                hideLoading(container);
                $(container).html('<p style="text-align: center; color: red;">Error loading content</p>');
                console.error('AJAX Error:', error);
            }
        });
    };
    
    window.submitFormAjax = function(formSelector, url, successCallback, errorCallback) {
        const $form = $(formSelector);
        const $submitBtn = $form.find('[type="submit"]');
        const originalBtnText = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            success: function(data) {
                $submitBtn.prop('disabled', false).html(originalBtnText);
                
                if (typeof successCallback === 'function') {
                    successCallback(data);
                }
            },
            error: function(xhr, status, error) {
                $submitBtn.prop('disabled', false).html(originalBtnText);
                
                if (typeof errorCallback === 'function') {
                    errorCallback(error);
                } else {
                    showToast('An error occurred. Please try again.', 'error');
                }
            }
        });
    };
    
    // ==================== CONFIRM DIALOG ====================
    
    window.confirmDialog = function(message, onConfirm, onCancel) {
        $('.confirm-dialog-overlay').remove();
        
        const dialog = $(`
            <div class="confirm-dialog-overlay modal-overlay active">
                <div class="modal" style="max-width: 400px;">
                    <div class="modal-header">
                        <h3>Confirm</h3>
                    </div>
                    <p style="margin: 20px 0;">${message}</p>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline cancel-btn" style="flex: 1;">Cancel</button>
                        <button class="btn btn-primary confirm-btn" style="flex: 1;">Confirm</button>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(dialog);
        
        dialog.find('.confirm-btn').on('click', function() {
            dialog.remove();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });
        
        dialog.find('.cancel-btn').on('click', function() {
            dialog.remove();
            if (typeof onCancel === 'function') {
                onCancel();
            }
        });
        
        dialog.on('click', function(e) {
            if (e.target === this) {
                dialog.remove();
                if (typeof onCancel === 'function') {
                    onCancel();
                }
            }
        });
    };
    
});