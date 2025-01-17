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
        const data = { id: productId, add: true };
        ajaxSetShoppingCart(data, productId);
    });

    $('.cart-minute').on('click', function (e) {
        e.preventDefault();
        const productId = $(this).attr('id');
        const data = { id: productId, add: false };
        ajaxSetShoppingCart(data, productId);
    });

    function ajaxSetShoppingCart(data, productId) {

        const message = 'Fail to set to shopping cart.';
        const messageDiv = $('#' + productId + '-message');
        const quatantityDiv = $('#' + productId + '-quantity');
        const productTotalAmountDiv = $('#' + productId + '-total-amount');
        const totalAmountDiv = $('#cart-total-amount')
        $.ajax({
            url: '/shopping-cart-set', // The URL to send the data to
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                messageDiv.html("<span class='text-success'>" + response.message + "</span>");
                if (quatantityDiv) {
                    quatantityDiv.html("<span>" +  response.quantity + "<span>")
                }
                if (totalAmountDiv) {
                    let totalAmount =  totalAmountDiv.text();
                    totalAmount = parseFloat(parseFloat(totalAmount.trim()).toFixed(2)) + response.adjustToatlAmount;
                    totalAmountDiv.text(parseFloat(totalAmount).toFixed(2));
                }
                if (productTotalAmountDiv) {
                    let productTotalAmount =  productTotalAmountDiv.text();
                    productTotalAmount = parseFloat(parseFloat(productTotalAmount.trim()).toFixed(2)) + response.adjustToatlAmount;
                    productTotalAmountDiv.text(parseFloat(productTotalAmount).toFixed(2));
                }
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
    }
});
