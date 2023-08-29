/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/account_landing.scss';

// start the Stimulus application
import './bootstrap';

let ready = 0;

function countDown(id, target, direction) {
    return new Promise((resolve, reject) => {
        let originalValue = target;
        target = Math.round(target);
        let operator = -1;

        let current = 0;
        while (current < target) {
            current = Math.ceil(Math.random() * (target + Math.ceil(target * 1.0)));
        }

        // count up
        if (direction === 0) {
            operator = 1;
            current = 0;
        }

        let speed = 1; // Initial speed

        // Display the initial value
        document.getElementById(id).innerHTML = current;
        if (current === originalValue){
            resolve();
            return;
        }
        // Run the countdown every 10 milliseconds
        const intervalId = setInterval(function () {
            // Decrement the current value
            current = current + (1 * operator);

            // Update the display
            document.getElementById(id).innerHTML = current;

            // If the current value is close to the target, start slowing down
            if (current < target + 20 && current > target - 20) {
                speed = 100;
            }

            // If the current value has reached the target, stop the countdown and resolve the Promise
            if (current === target) {
                clearInterval(intervalId);
                document.getElementById(id).innerHTML = originalValue;
                resolve();
            }
        }, speed);

    });
}


function showBalance(total) {
    document.getElementById('balance_area').classList.remove('balance-hidden');
    var div = document.getElementById('balance_area');
    div.style.opacity = 0;
    div.style.display = 'block';
    div.style.top = '-100px';

    var tick = function () {
        div.style.opacity = +div.style.opacity + 0.01;
        if (+div.style.opacity < 1) {
            (window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 100);
        }
    };
    tick();
    div.style.top = '0px';

    // enlarge img of balance
    const parent = document.querySelector('.balance-header');
    const image = document.querySelector('.balance-image img');

    // Set the height of the image to the height of the parent element
    let reduceValue = 50;
    image.style.height = (parent.offsetHeight - reduceValue) + 'px';
    image.style.width = (parent.offsetHeight - reduceValue) + 'px';
}

if (document.getElementById('total_debts')){
    let debts = parseFloat(document.getElementById('total_debts').innerHTML.replace(',', '.'));
    let loans = parseFloat(document.getElementById('total_loans').innerHTML.replace(',', '.'));
    let total = loans - debts;
    document.getElementById('total_balance').innerHTML = total.toFixed(2).toString();
    if (total < 0) {
        document.querySelector('.balance-number').classList.toggle('balance-negative');
    }

    Promise.all([
        countDown('total_loans', loans, 0),
        countDown('total_debts', debts, 1)
    ]).then(showBalance);
}


// tile reaction
const tiles = document.querySelectorAll('.tile');

tiles.forEach(tile => {
    tile.addEventListener('click', event => {
        const url = tile.getAttribute('data-url');
        window.location.href = url;
    });
});


// slide show
let slideIndex = 0;

showSlides();

function showSlides() {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    if (slides.length > 0){
        let dots = document.getElementsByClassName("dot");
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        slideIndex++;
        if (slideIndex > slides.length) {slideIndex = 1}
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex-1].style.display = "inline-block";
        // dots[slideIndex-1].className += " active";
        setTimeout(showSlides, 10000); // Change image every 2 seconds
    }

}
