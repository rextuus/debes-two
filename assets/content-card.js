/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// const collapseIcon = document.querySelector('.collapse-icon');
// const cardBody = document.querySelector('.card-body');
// const contentCard = document.querySelector('.content-card');
//
// contentCard.addEventListener('click', () => {
//     cardBody.style.display = cardBody.style.display === 'none' ? 'block' : 'none';
//     collapseIcon.classList.toggle('rotated');
// });

const contentCards = document.querySelectorAll('.content-card');

contentCards.forEach(contentCard => {
    // const contentCard = document.querySelector('.content-card');
    const body = contentCard.querySelector('.card-body');
    const label = contentCard.querySelector('.label');
    const collapseIcon = contentCard.querySelector('.collapse-icon');
    const header = contentCard.querySelector('.card-header');
    const description = contentCard.querySelector('.description');
    const transactionDetails = contentCard.querySelector('.account-details');

    header.addEventListener('click', () => {
        body.classList.toggle('hidden');
        collapseIcon.classList.toggle('rotate');
        label.classList.toggle('transparent-text');
    });
    if (description){
        description.addEventListener('click', () => {
            body.classList.toggle('hidden');
            collapseIcon.classList.toggle('rotate');
            label.classList.toggle('transparent-text');
        });
    }

    if (transactionDetails){
        transactionDetails.addEventListener('click', () => {
            body.classList.toggle('hidden');
            collapseIcon.classList.toggle('rotate');
            label.classList.toggle('transparent-text');
        });
    }
});

const paymentContentCards = document.querySelectorAll('.content-card-payment-option');
paymentContentCards.forEach(contentCard => {

    // const contentCard = document.querySelector('.content-card');
    const body = contentCard.querySelector('.card-body-payment-option');
    const label = contentCard.querySelector('.label-payment-option');
    const collapseIcon = contentCard.querySelector('.collapse-icon-payment-option');
    const header = contentCard.querySelector('.card-header-payment-option');
    const description = contentCard.querySelector('.description-payment-option');
    const transactionDetails = contentCard.querySelector('.account-details-payment-option');

    header.addEventListener('click', () => {
        body.classList.toggle('hidden');
        collapseIcon.classList.toggle('rotate');
        label.classList.toggle('transparent-text');
    });
    if (description){
        description.addEventListener('click', () => {
            body.classList.toggle('hidden');
            collapseIcon.classList.toggle('rotate');
            label.classList.toggle('transparent-text');
        });
    }

    if (transactionDetails){
        transactionDetails.addEventListener('click', () => {
            body.classList.toggle('hidden');
            collapseIcon.classList.toggle('rotate');
            label.classList.toggle('transparent-text');
        });
    }
});

const transactionSummaries = document.querySelectorAll('.transaction-summary');

transactionSummaries.forEach(transactionSummary => {
    // const contentCard = document.querySelector('.content-card');
    const body = transactionSummary.querySelector('.exchange-list');
    const button = transactionSummary.querySelector('.exchange-collapsable');

    if (!button.classList.contains('deactivated')){
        button.addEventListener('click', () => {
            body.classList.toggle('hidden');
            button.classList.toggle('active');
        });
    }
});


let flashMessage = document.getElementById('flash-message-container');

setTimeout(() => {
        flashMessage.style.display = 'none';
    },
    3000);