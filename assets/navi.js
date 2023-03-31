/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/navi.scss';
import './styles/footer.scss';

// start the Stimulus application
import './bootstrap';

let burger = document.getElementById('burger'),
    nav    = document.getElementById('main-nav'),
    slowmo = document.getElementById('slowmo');

burger.addEventListener('click', function(e){
    this.classList.toggle('is-open');
    nav.classList.toggle('is-open');
});

if (slowmo){
    slowmo.addEventListener('click', function(e){
        this.classList.toggle('is-slowmo');
    });
}


/* Onload demo - dirty timeout */
let clickEvent = new Event('click');

window.addEventListener('load', function(e) {
    slowmo.dispatchEvent(clickEvent);
    burger.dispatchEvent(clickEvent);

    setTimeout(function(){
        burger.dispatchEvent(clickEvent);

        setTimeout(function(){
            slowmo.dispatchEvent(clickEvent);
        }, 3500);
    }, 5500);
});

var homeButton = document.getElementById('home-button');
if (homeButton){
    homeButton.querySelector('img').src = homeButton.getAttribute('data-image')
    homeButton.addEventListener('click', function() {
        if (button.classList.contains('link')){
            window.location.replace(button.getAttribute('data-link'))
        }
    });
}

