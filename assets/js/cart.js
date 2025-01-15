import '../bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../styles/cart.css';

// Jquery import
import $ from 'jquery';

// for bootstrap@4
import 'bootstrap';

document.addEventListener('turbo:load', function() {
    $('.cart-add').on('click', function (e) {
        e.preventDefault();
        const productId = $(this).attr('id');
        const data = { id: productId };
        const message = 'Fail to add to shopping cart.';
        const messageDiv = $('#' + productId + '_message');

        $.ajax({
            url: '/shopping-cart-add', // The URL to send the data to
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                messageDiv.html("<span class='text-success'>" + response.message + "</span>");
            },
            error: function(xhr, status, error) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    messageDiv.html("<span class='text-danger'>" + message + " " + response.message + "</span>");
                } catch (e) {
                    messageDiv.html("<span class='text-danger'>" + message + "</span>");
                }
            }
        });
    });
});
