/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/transaction-handling.scss';
import './styles/flash_message.scss';

// Get all the buttons with class bank-transfer-row-copy-button
const copyButtons = document.querySelectorAll('.bank-transfer-row-copy-button');

// Add a click event listener to each button
copyButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Get the value of the corresponding div with class bank-transfer-row-copy-value
        const valueElement = button.parentNode.querySelector('.bank-transfer-row-copy-value');
        const value = valueElement ? valueElement.textContent.trim().replace('€', '') : '';

        // Copy the value to the clipboard
        navigator.clipboard.writeText(value).then(() => {
            // Show a flash message to the user indicating that the value has been copied
            const flashMessage = document.createElement('div');
            flashMessage.classList.add('flash-message');
            flashMessage.textContent = `In die Zwischenablage kopiert: ${value}`;
            document.body.insertBefore(flashMessage, document.querySelector('.content-body'));
            setTimeout(() => {
                flashMessage.remove();
            }, 3000);
            // Set the parent element's color to black
            const parentElement = button.parentNode.parentNode;
            if (parentElement) {
                parentElement.classList.add('copied');
            }
        }).catch((error) => {
            // Handle any errors that may occur when attempting to copy the value
            console.error('Failed to copy value: ', error);
        });
    });
});


