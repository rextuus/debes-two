/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/form.scss';

// start the Stimulus application
import './bootstrap';

// Get all the input fields
var inputs = document.querySelectorAll(".form-input-field");

// Listen for changes to all the input fields
inputs.forEach(function(inputElement) {
    let input = inputElement.querySelector('input');
    if (!input){
        return;
    }
    input.addEventListener("input", function() {
        // If the input field is not empty, show the corresponding label
        if (this.value) {
            var id = this.getAttribute("id");
            var label = document.querySelector("label[for='" + id + "']");
            console.log(id);
            console.log(label);
            if (label.innerHTML === ''){
                label.innerHTML = this.placeholder;
            }
            label.style.display = "block";
        } else {
            var id = this.getAttribute("id");
            var label = document.querySelector("label[for='" + id + "']");
            label.style.display = "none";
        }
    });
});

// curreny form field

// select all input elements with data-type attribute equals to "currency"
var currencyInputs = document.querySelectorAll("input[data-type='currency']");

// loop through each input element and add event listeners
currencyInputs.forEach(function(input) {
    input.addEventListener("keyup", function() {
        formatCurrency(input);
    });
    input.addEventListener("blur", function() {
        formatCurrency(input, "blur");
    });
});

function formatNumber(n) {
    // format number 1000000 to 1,234,567
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")
}

function formatCurrency(input, blur) {
    // appends $ to value, validates decimal side
    // and puts cursor back in right position.

    // get input value
    var input_val = input.value;
    // input_val = input_val.replace('€', '');
    // input_val = input_val.replace(" \u20AC", '');

    // don't validate empty input
    if (input_val === "") { return; }

    // // don't validate empty input
    // let numberCheck = Number(input_val);
    //
    // console.log(isNaN(numberCheck));
    // if (isNaN(numberCheck)) {input.value = ''; return; }

    // original length
    var original_len = input_val.length;

    // initial caret position
    var caret_pos = input.selectionStart;

    // check for decimal
    if (input_val.indexOf(",") >= 0) {

        // get position of first decimal
        // this prevents multiple decimals from
        // being entered
        var decimal_pos = input_val.indexOf(",");

        // split number by decimal point
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);

        // add commas to left side of number
        left_side = formatNumber(left_side);

        // validate right side
        right_side = formatNumber(right_side);

        // On blur make sure 2 numbers after decimal
        if (blur === "blur") {
            right_side += "00";
        }

        // Limit decimal to only 2 digits
        right_side = right_side.substring(0, 2);

        // join number by .
        // input_val = left_side + "," + right_side;
        input_val = left_side + "," + right_side + " \u20AC";

    } else {
        // no decimal entered
        // add commas to number
        // remove all non-digits
        input_val = formatNumber(input_val);
        input_val = input_val+" \u20AC";

        // final formatting
        if (blur === "blur") {
            input_val += ",00";
        }
    }

    // send updated string to input
    input.value = input_val;

    // put caret back in the right position
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input.setSelectionRange(caret_pos, caret_pos);
}