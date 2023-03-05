/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/tab.scss';

// start the Stimulus application
import './bootstrap';

// Get the tab buttons
var tabButtons = document.querySelectorAll('.tab-button');

// Get the tab content divs
var tabContent = document.querySelectorAll('.tab');

// Get the header content divs
var pageHeader = document.querySelector(".page-header-text");
var pageImage = document.querySelector(".page-header-image");

// Add a click event listener to each tab button
tabButtons.forEach(function(button) {
    button.innerHTML = button.getAttribute('data-name');
    if (button.classList.contains('disabled')){
        return;
    }

    button.addEventListener('click', function() {
        // Get the data-tab attribute for the clicked button
        var tab = this.getAttribute('data-tab');

        // Set the active class for the clicked button
        tabButtons.forEach(function(button) {
            button.classList.remove('active');
            button.innerHTML = button.getAttribute('data-name');
        });
        this.classList.add('active');
        pageHeader.innerHTML = this.getAttribute('data-name');
        pageImage.querySelector('img').src = this.getAttribute('data-image')

        // Show the corresponding tab content
        tabContent.forEach(function(content) {
            content.classList.remove('active');

            if (content.id === tab) {
                content.classList.add('active');
            }
        });
        if (button.classList.contains('link')){
            console.log('ddd');
            window.location.replace(button.getAttribute('data-link'))
        }
    });
});

