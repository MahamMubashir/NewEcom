define([
    'jquery'
], function ($) {
    "use strict";
    return function customNewcom() {

        $(document).ready(function () {
            checkLimit();
        });
        function checkLimit() {
            $.ajax({
                url: "newecom/searchlimit/checklimit",
                type: "POST",
                success: function(response) {

                    if (response.response.response.status) {
                    $('.discover_widget').show();
                    $('.decide_widget').show();
                    //  JS for clickable text
                    $('.newcomClickableText').click(function() {
                        var newEcomClickedText = $(this).text().trim();
                        $('#NewEcomAi-question, #NewEcomAi-discover-question').val(newEcomClickedText);
                    });

                    //  JS condition for discover template postion
                    if ($('.newSmartShopDiscover, .newSmartShopDecide').hasClass('newcomLeftSide')) {
                        $('#newcomLeftBtn').on('click', function() {
                            $('#newcom-popup').css('display', 'block');
                        });

                    } else if ($('.newSmartShopDiscover, .newSmartShopDecide').hasClass('newcomRightSide')) {
                        $('#newcomRightBtn').on('click', function() {
                            $('#newcom-popup').css('display', 'block');
                        });
                    } else if ($('.newSmartShopDiscover, .newSmartShopDecide').hasClass('newcomProductGrid')) {
                        $('#newcom-popup').css('display', 'block');
                    } else {
                        $('#newcom-popup').css('display', 'block');
                    }

                    $('#newComclose').on('click', function() {
                        $('#newcom-popup').css('display', 'none');
                    });

                    // Function to show the scrollbar when the content is greater than popup container
                    function newcomCheckOverflow() {
                        var $container = $('.js-newcom-popup-content');
                        if ($container[0].scrollHeight > $container.innerHeight()) {
                            $container.addClass('show-scrollbar');
                        } else {
                            $container.removeClass('show-scrollbar');
                        }
                    }

                    newcomCheckOverflow(); // Initial check on document ready
                    $(window).resize(newcomCheckOverflow); // Check on window resize

                    // MutationObserver to detect changes in the container
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            newcomCheckOverflow();
                        });
                    });

                    // Configuration of the observer
                    var config = { childList: true, subtree: true, characterData: true };

                    // Pass in the target node, as well as the observer options
                    observer.observe(document.querySelector('.js-newcom-popup-content'), config);
                    }
                },
                error: function(error, status) {
                    $('.error_msg').show();
                }
            });
        }
    }
});
